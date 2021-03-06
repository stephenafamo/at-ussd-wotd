<?php
include __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$baseUrl 	= 'https://wordsapiv1.p.mashape.com/words/';
$apiKey 	= $_ENV['API_KEY'];

$client 	= new Client([
	'base_uri' 	=> $baseUrl,
	'headers' 	=> [
		'X-Mashape-Key'	=> $apiKey,
		'Content-Type' 	=> 'application/x-www-form-urlencoded',
		'Accept' 		=> 'application/json'
	]
]);


$input_array 	= explode("*", $_POST['text']);
$word 			= end($input_array);

if (empty($word)) {
	echo base();
} elseif (is_numeric($word)) {
	echo random();
} else {
	echo definition($word);
}

function base($reply = "")
{
	if (empty($reply))
		$reply .= 'CON ';

	$reply .= 'Enter a word to find it\'s definition, or enter a number to get a random word';

	return $reply;	
}

function random()
{
	global $client;

	$response 	= $client->get('https://wordsapiv1.p.mashape.com/words/?random=true&hasDetails=definitions&letterPattern=^\S*$');

	$response_body = json_decode($response->getBody()->getContents(), true);

	$reply = 'CON ' . $response_body['word'] . " /" . $response_body['pronunciation'][key($response_body['pronunciation'])] . "/\n";

	$i = 1;
	foreach ($response_body['results'] as $result) {
		$reply .= "$i. (" . $result['partOfSpeech'] . ") " . $result['definition'] . " \n";
		$i++;
	}

	return $reply;	
}

function definition($word)
{
	global $client;

	try {
		$response 	= $client->get('https://wordsapiv1.p.mashape.com/words/'.urlencode($word));
	} catch (GuzzleException $e) {
		return base("CON We could not find that word. \n\n");
	}

	$response_body = json_decode($response->getBody()->getContents(), true);

	$reply = 'CON ' . $word . " /" . $response_body['pronunciation'][key($response_body['pronunciation'])] . "/\n";

	$i = 1;
	foreach ($response_body['results'] as $result) {
		$reply .= "$i. (" . $result['partOfSpeech'] . ") " . $result['definition'] . " \n";
		$i++;
	}

	return urldecode($reply);	
}