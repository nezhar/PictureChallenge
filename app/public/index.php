<?php

include "../vendor/autoload.php";

Flight::set('flight.views.path', '../views');

// Load app layout
Flight::route('/', function() {
    Flight::render('layout', array('title' => 'Home Page'));
});

// Call reddit API to grab results
Flight::route('POST /call', function() {
  // List of accepted domains for posts
  $acceptedDomain = [
    //Flickr
    "flickr.com",
    "flic.kr",
    //500px
    "500px.com",
    //min.us
    "min.us",
    "minus.com",
    //Picasa, Google+
    "googleusercontent.com",
    //Google Photos
    "photos.google.com",
    "photos.app.goo.gl",
    //Deviantart
    "deviantart.net",
    "deviantart.com",
    //Smugmug
    "smugmug.com",
    //OneDrive
    "1drv.ms",
    "onedrive.live.com",
  ];
  // Public Atom feed (JSON API blocks unauthenticated requests), newest first so the
  // date range check can stop paging early.
  $api_url = "https://www.reddit.com/r/PictureChallenge/new/.rss?limit=100";
  // List of valid images
  $validImages = [];

  $request = Flight::request();

  if (!isset($request->data['challenge_number'])) {
    Flight::halt(400, 'Please set challenge_number');
    die();
  }
  if (!isset($request->data['start_date'])) {
    Flight::halt(400, 'Please set start_date');
    die();
  }
  if (!isset($request->data['end_date'])) {
    Flight::halt(400, 'Please set end_date');
    die();
  }

  $challengeNumber = $request->data['challenge_number'];
  $startDate = strtotime($request->data['start_date']);
  $endDate = strtotime($request->data['end_date']);

  // Reddit requires a unique, descriptive User-Agent for unauthenticated requests
  $options = [
    'http' => [
      'method' => 'GET',
      'header' => [
        'User-Agent: web:PictureChallenge:1.1 (+https://github.com/nezhar/PictureChallenge)',
      ],
      'ignore_errors' => true,
    ],
  ];
  $context = stream_context_create($options);

  // Get posts from Reddit
  do {
    //Call Reddit feed, pause between pages and retry when rate limited
    if (isset($after)) sleep(2);
    $call = isset($after) ? $api_url."&after={$after}" : $api_url;
    for ($attempt = 1; $attempt <= 3; $attempt++) {
      $response = @file_get_contents($call, false, $context);
      // Last status line wins when redirects were followed
      $statusLines = isset($http_response_header) ? preg_grep('#^HTTP/#', $http_response_header) : [];
      $status = $statusLines ? end($statusLines) : 'no response';
      if (strpos($status, ' 429') === false) break;
      sleep(5 * $attempt);
    }

    if ($response === false || strpos($status, ' 200') === false) {
      Flight::halt(502, "Reddit request failed: {$status}");
      die();
    }

    $feed = @simplexml_load_string($response);
    if ($feed === false) {
      Flight::halt(502, 'Reddit returned an invalid feed');
      die();
    }

    if (empty($feed->entry)) break;

    foreach ($feed->entry as $entry) {
      $post = feedEntryToPost($entry);

      // Skip posts that are newer than end date
      if ($post->created_utc > $endDate) continue;

      // Break loops if time start date is reached
      if ($post->created_utc < $startDate) break 2;

      // Check for valid domain
      $validDomain = array_filter($acceptedDomain, function($el) use ($post) {
        return (strpos($post->domain, $el) !== false);
      });

      // Find images
      if ($validDomain) {
        if (strpos($post->title, $challengeNumber) !== false) {
          $validImages[] = $post;
        }
      }
    }
    //Start next feed call from the last post
    if (count($feed->entry) < 100) break;
    $after = (string) $entry->id;
  } while (1!=0);

  // Create Result for Reddit Comments
  $result = [];
  foreach ($validImages as $validImage) {
    $title = trim(str_replace($challengeNumber, "", $validImage->title), ' :');
    $result[] = "* **{$title}** [pic]({$validImage->url}) | [comment](http://www.reddit.com{$validImage->permalink}) by *{$validImage->author}*";
  }

  Flight::json($result);
});

// Map an Atom feed entry to the post fields used above
function feedEntryToPost($entry) {
  // The submitted link is only available inside the HTML content as "[link]"
  $url = '';
  if (preg_match('#<a href="([^"]+)">\[link\]</a>#', (string) $entry->content, $match)) {
    $url = html_entity_decode($match[1], ENT_QUOTES);
  }

  return (object) [
    'title' => (string) $entry->title,
    'created_utc' => strtotime((string) $entry->published),
    'author' => preg_replace('#^/u/#', '', (string) $entry->author->name),
    'url' => $url,
    'domain' => (string) parse_url($url, PHP_URL_HOST),
    'permalink' => (string) parse_url((string) $entry->link['href'], PHP_URL_PATH),
  ];
}

Flight::start();
