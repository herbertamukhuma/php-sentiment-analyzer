<?php

namespace SentimentClassifier\Base;

class Base{

    protected $verbose = false;

    protected function output($text){

        if($this->verbose === true){
            print_r($text);
        }
    }
}