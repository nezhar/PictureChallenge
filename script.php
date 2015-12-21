<?php

include "vendor/autoload.php";
include "config.php";

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
	$message .= "* **{$title}** [pic]({$validImage->url}) | [comment](http://www.reddit.com{$validImage->permalink}) by *{$validImage->author}* <br><br>";
}

$mail = new PHPMailer;
$mail->isSMTP();
$mail->isHTML(true); 
$mail->Host = $smtp["host"];
$mail->SMTPAuth = $smtp["smtp_auth"];
$mail->Username = $smtp["username"];
$mail->Password = $smtp["password"];
$mail->SMTPSecure = $smtp["smtp_secure"];
$mail->Port = $smtp["port"];

$mail->setFrom($fromEmail, 'Picture Challenge Script');
foreach ($recipients as $recipient) {
	$mail->addAddress($recipient);   
}

$mail->Subject = "PictureChallenge #{$currentChallenge}";
$mail->Body = $message;

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}
