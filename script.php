<?php

// Week of first PictureChallenge
$referenceWeek = 2146;
// Request posts limit
$maxPosts = 25;
// Subredit
$subreddit = "PictureChallenge";
// Api URL
$api_url = "https://www.reddit.com/r/{$subreddit}.json?&limit={$maxPosts}";
// List of valid images
$validImages = [];

$currentChallengeWeek = floor(time()/(60*60*24*7));
$currentChallenge = $currentChallengeWeek - $referenceWeek;
$timeLimit = ($currentChallengeWeek-1)*60*60*24*7;

// List of accepted domains for posts
$acceptedDomain = [
	//Flickr
	'flickr.com',
	'flic.kr',
	//500px
	'500px.com',
	//min.us
	'min.us',
	'minus.com',
	//Picasa, Google+
	'googleusercontent.com',
	//Deviantart
	'deviantart.net',
	//Smugmug
	'smugmug.com',
];

// Get posts from Reddit
do {
	//Call Reddit API
	$call = isset($after) ? $api_url."&after={$after}" : $api_url;
	$posts = json_decode(file_get_contents($call));

	foreach ($posts->data->children as $post) {
		if (!$post->data->stickied) {
			// Break loops if time limit is reached
			if ($post->data->created_utc < $timeLimit) break 2;
			
			// Find images
			if (in_array($post->data->domain, $acceptedDomain)) {
				if (strpos($post->data->title, "#{$currentChallenge}") !== false) {
					$validImages[] = $post->data;
				}
			}
		}
	}

	//Start next API call from this post
	$after = $post->data->name;
} while (1!=0);

// Create Message
$message = "";
foreach ($validImages as $validImage) {
	$title = trim(str_replace("#{$currentChallenge}", "", $validImage->title), ' :');
	$message .= "[**{$title}**]({$validImage->url}) by *{$validImage->author}*"."\r\n"."\r\n";
}

// List of receivers
$recipients = [
	"email1",
	"email2",
];

// Subject and Headers
$subject = "PictureChallenge #{$currentChallenge}";
$headers = 'From: picturechallenge@picturechallenge'."\r\n"
			.'Reply-To: no-reply@picturechallenge'."\r\n"
			.'X-Mailer: PHP/'.phpversion();

// Send Emails
foreach ($recipients as $to) {
	mail($to, $subject, $message, $headers);
}
