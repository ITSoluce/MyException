<?php 
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

new MyException\MyException();

global $MyExceptionScreenError;
$MyExceptionScreenError = 0;


echo MyException\MyException::ScreenError;

echo 100/0;