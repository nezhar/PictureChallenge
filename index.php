<?php

// Week of first PictureChallenge
$referenceWeek = 2147;
// Request posts limit
$maxPosts = 25;
// Subredit
$subreddit = "PictureChallenge";
// Api URL
$api_url = "https://www.reddit.com/r/{$subreddit}.json?&limit={$maxPosts}";

$after = "";

$validImages = [];

$currentChallengeWeek = floor(time()/(60*60*24*7));
$currentChallenge = $currentChallengeWeek - $referenceWeek;
$timeLimit = ($currentChallengeWeek-1)*60*60*24*7;

//List of accepted domains for posts
$acceptedDomain = [
	'flickr.com',
	'flic.kr',
	'500px.com',
];


do {
	//Call Reddit API
	$call = $after ? $api_url."&after={$after}" : $api_url;
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

foreach ($validImages as $validImage) {
	$title = trim(str_replace("#{$currentChallenge}", "", $validImage->title), ' :');
	echo "[**{$title}**]({$validImage->url}) by *{$validImage->author}*";
    echo "\n";
}
