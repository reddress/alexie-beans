<?php
require_once("header.php");

// retrieve account types
$sql = "select a.id, a.name
from account_type a
inner join user u on a.username = u.username
where a.username = :username";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username']]);

$account_types = $stmt->fetchAll();
?>
New Account
<form action="accounts.php" method="post">
    <table>
        <tr>
            <td>Name</td>
            <td>Account type</td>
        </tr>
        <tr>
            <td>
                <input type="text" name="name" autofocus>
            </td>
            <td>
                <select name="account_type">
                    <?php
                    foreach ($account_types as $account_type) {
                        println("<option value=\"{$account_type['id']}\">{$account_type['name']}</option>");
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <input type="submit">
</form>
<?php

// check if form was posted, if so, add the account
if (isset($_POST['name'])) {
    $sql = "insert into account (name, account_type, username)
    values (:name, :account_type, :username)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([":name" => $_POST["name"],
                    ":account_type" => $_POST["account_type"],
                    ":username" => $_SESSION["username"]]);
    println("Added account " . $_POST["name"]);
}

println("");
println("Current accounts");
println("");

// retrieve existing accounts
$sql = "select a.name, at.name as at_name from account a
inner join account_type at on a.account_type = at.id
where a.username = :username order by name";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username']]);

foreach ($stmt as $row) {
    println($row['name'] . " " . $row['at_name']);
}

require_once("footer.php");
?>
