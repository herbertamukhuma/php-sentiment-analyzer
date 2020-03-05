<?php

ini_set("memory_limit","5000M");

use SentimentClassifier\Exception\DataHandlerException;
use SentimentClassifier\SentimentClassifier;

require_once "./../sentiment-classifier/autoload.php";

try {

    $classifier = new SentimentClassifier("tweets", 1000,true);
    $samples = [
        "I guess this is the end for me"
        ];
    print_r($classifier->predict($samples));


} catch (\Phpml\Exception\InvalidArgumentException $e) {
    echo "Error 1: " . $e->getMessage();
} catch (DataHandlerException $e) {
    echo "Error 2: " . $e->getMessage();
} catch (\SentimentClassifier\Exception\SentimentClassifierException $e) {
    echo "Error 3: " . $e->getMessage();
}