<?php
require_once("../dbhost.php");

echo "Uncomment the source to drop DB";

try {
//    $sql = "drop database $dbname";
//    $dbh->exec($sql);

//    echo "Dropped DB $dbname";
    
} catch (PDOException $e) {
    die($e->getMessage());
}

?>
