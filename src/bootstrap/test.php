<?php
require_once __DIR__.'/../../vendor/autoload.php';

use App\Database\PDODatabaseConnection;
use App\Helpers\Config;
use App\Database\PDOQueryBuilder;

$configs = Config::get("database","pdo_testing");
$pdoDatabaseConnection = new PDODatabaseConnection($configs);
$pdoQueryBuilder = new PDOQueryBuilder($pdoDatabaseConnection->connect());
$pdoQueryBuilder->truncateAllTable();