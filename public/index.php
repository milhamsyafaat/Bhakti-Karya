<?php
/**
 * Front controller — satu-satunya file yang diakses langsung.
 */

require dirname(__DIR__) . '/bootstrap/app.php';

$app = new \App\Core\App();
$app->run();
