<?php

use GuzzleRetry\GuzzleHandler;

require 'vendor/autoload.php';

$client = (new GuzzleHandler((new \GuzzleRetry\Monolog())->getLogger()))->getHttpClient();

$result = $client->get('http://google.com');

var_dump($result);
