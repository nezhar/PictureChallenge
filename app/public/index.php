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
  // Api URL, limit set to 25 posts (oauth.reddit.com required for authenticated requests)
  $api_url = "https://oauth.reddit.com/r/PictureChallenge.json?&limit=25";
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

  // Fetch Reddit OAuth token (application-only client credentials flow)
  $clientId = getenv('REDDIT_CLIENT_ID');
  $clientSecret = getenv('REDDIT_CLIENT_SECRET');

  if (!$clientId || !$clientSecret) {
    Flight::halt(500, 'Reddit API credentials not configured');
    die();
  }

  $tokenOptions = [
    'http' => [
      'method' => 'POST',
      'header' => [
        'Authorization: Basic ' . base64_encode("{$clientId}:{$clientSecret}"),
        'User-Agent: PictureChallenge/1.0',
        'Content-Type: application/x-www-form-urlencoded',
      ],
      'content' => 'grant_type=client_credentials',
    ],
  ];
  $tokenContext = stream_context_create($tokenOptions);
  $tokenResponse = json_decode(file_get_contents('https://www.reddit.com/api/v1/access_token', false, $tokenContext));

  if (!isset($tokenResponse->access_token)) {
    Flight::halt(500, 'Failed to obtain Reddit access token');
    die();
  }

  $options = [
    'http' => [
      'method' => 'GET',
      'header' => [
        'Authorization: Bearer ' . $tokenResponse->access_token,
        'User-Agent: PictureChallenge/1.0',
      ],
    ],
  ];
  $context = stream_context_create($options);

  // Get posts from Reddit (oauth.reddit.com requires the token)
  do {
  	//Call Reddit API
  	$call = isset($after) ? $api_url."&after={$after}" : $api_url;
  	$posts = json_decode(file_get_contents($call, false, $context));

  	if (empty($posts->data->children)) break;

  	foreach ($posts->data->children as $post) {
  		if (!$post->data->stickied) {
        // Skip posts that are older than end date
        if ($post->data->created_utc > $endDate) continue;

  			// Break loops if time start date is reached
        if ($post->data->created_utc < $startDate) break 2;

        // Check for valid domain
        $validDomain = array_filter($acceptedDomain, function($el) use ($post) {
            return (strpos($post->data->domain, $el) !== false);
        });

  			// Find images
  			if ($validDomain) {
  				if (strpos($post->data->title, $challengeNumber) !== false) {
  					$validImages[] = $post->data;
  				}
  			}
  		}
  	}
  	//Start next API call from this post
  	if (empty($posts->data->after)) break;
  	$after = $posts->data->after;
  } while (1!=0);

  // Create Result for Reddit Comments
  $result = [];
  foreach ($validImages as $validImage) {
  	$title = trim(str_replace($challengeNumber, "", $validImage->title), ' :');
  	$result[] = "* **{$title}** [pic]({$validImage->url}) | [comment](http://www.reddit.com{$validImage->permalink}) by *{$validImage->author}*";
  }

  Flight::json($result);
});

Flight::start();
