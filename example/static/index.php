<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 14:10
 */

defined('ROOT') || define('ROOT', dirname(__DIR__));

require dirname(ROOT) . '/vendor/autoload.php';

$app = \NashFrame\App::createFPM(ROOT . '/.env');

echo $app->display();
