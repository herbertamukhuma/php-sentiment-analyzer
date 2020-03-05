<?php
$dir_path =  __DIR__;
$dir_path = str_replace("\\","/",$dir_path);

//PHP-ML autoload file
require_once $dir_path."/../php-ml/vendor/autoload.php";

//TextClassifier files
require_once $dir_path."/src/SentimentClassifier/Exception/DataHandlerException.php";

require_once $dir_path."/src/SentimentClassifier/Base/Base.php";

require_once $dir_path."/src/SentimentClassifier/Helper/DataHandler.php";

require_once $dir_path."/src/SentimentClassifier/SentimentClassifier.php";