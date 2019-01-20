<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 11:34
 */

defined('ROOT') || define('ROOT', dirname(__DIR__));

require dirname(ROOT) . '/vendor/autoload.php';

try {

    $app = new NashFrame\App(ROOT);

    $app->display('.env');

} catch (Throwable $e) {
    // 错误；
    var_dump($e);
}
