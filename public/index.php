<?php
require(__DIR__ . '/../vendor/autoload.php');

defined('PROJECT_ROOT')
|| define('PROJECT_ROOT', realpath(dirname(__FILE__) . '/../'));

$GLOBALS['__start__'] = microtime(true);