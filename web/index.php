<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 16:30
 */

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/anu/BaseAnu.php';
require __DIR__ . '/../src/anu/di/Container.php';
DEFINE('templatePath', __DIR__ .'/../templates/');
DEFINE('ANU_DEBUG', true);
DEFINE('BASE_PATH', __DIR__ . '/../');
DEFINE('ASSET_PATH', __DIR__ . '/assets/');
error_reporting( E_ALL );

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$server = $protocol . $host . '/';
DEFINE("BASE_URL", $server);

class Anu extends \anu\BaseAnu
{
}


spl_autoload_register(['Anu', 'autoload'], true, true);
Anu::$classMap = require __DIR__ . '/../src/anu/classes.php';

Anu::init();

Anu::$app->handleRequest();
