<?php namespace pineapple;

header('Content-Type: application/json');

require_once('API.php');
$api = new API();
echo $api->magic();
