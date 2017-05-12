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
  	//Deviantart
  	"deviantart.net",
    "deviantart.com",
  	//Smugmug
  	"smugmug.com",
    //OneDrive
    "1drv.ms",
    "onedrive.live.com",
  ];
  // Api URL, limit set to 25 posts
  $api_url = "https://www.reddit.com/r/PictureChallenge.json?&limit=25";
  // List of valid images
  $validImages = [];

  $request = Flight::request();

  if (!isset($request->data['challenge_number'])) {
    Flight::halt(400, 'Please set challenge_number');
  }
  if (!isset($request->data['weeks'])) {
    Flight::halt(400, 'Please set weeks');
  }

  $challengeNumber = $request->data['challenge_number'];
  $timeLimit = time()-60*60*24*7*$request->data['weeks'];

  // Get posts from Reddit
  do {
  	//Call Reddit API
  	$call = isset($after) ? $api_url."&after={$after}" : $api_url;
  	$posts = json_decode(file_get_contents($call));

  	foreach ($posts->data->children as $post) {
  		if (!$post->data->stickied) {
  			// Break loops if time limit is reached
  			if ($post->data->created_utc < $timeLimit) break 2;

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
  	$after = $post->data->name;
  } while (1!=0);

  // Create Result for Reddit Comments
  $results = [];
  foreach ($validImages as $validImage) {
  	$title = trim(str_replace($challengeNumber, "", $validImage->title), ' :');
  	$result[] = "* **{$title}** [pic]({$validImage->url}) | [comment](http://www.reddit.com{$validImage->permalink}) by *{$validImage->author}*";
  }

  Flight::json($result);
});

Flight::start();
