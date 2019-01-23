<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 11:34
 */

defined('ROOT') || define('ROOT', dirname(__DIR__));

require dirname(ROOT) . '/vendor/autoload.php';

$app = new NashFrame\App(ROOT);

echo $app->display('.env');
