<?php
require_once("config_secret.php");

$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8";

try {
    $dbh = new PDO($dsn, $dbuser, $dbpassword);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbh->exec("set character set 'utf8'");
} catch (PDOException $e) {
    die("DB host connection error " . $e->getMessage());
}


?>
