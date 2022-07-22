<?php


ini_set('display_errors', 1);

$dir = __DIR__;
require_once $dir . "/../vendor/autoload.php";

use Commerce\Core\Bootstrap;

mb_internal_encoding("UTF-8");


Bootstrap::dispatch($dir);
exit;
