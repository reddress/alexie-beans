<?php
require_once("header.php");

println("Manage currencies");
println("");
println("add");
?>

<?php 
// retrieve existing currencies for this user

$sql = "select c.id, c.code, c.symbol, c.name, c.cents
from currency c
inner join user u on c.username = u.username
where c.username = :username";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username']]);

foreach ($stmt as $row) {
    print_r($row);
}

require_once("footer.php");
?>
