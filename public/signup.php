<?php
require_once("../util.php");

function displayForm() {
    echo <<<_END
<form action="signup.php" method="post">
    Choose a username: <input type="text" name="username" autofocus>
    <br>
    <input type="submit">
</form>
_END;
}

if (isset($_POST['username'])) {
    require_once("../db.php");

    $stmt = $dbh->prepare("select username from user where username=:username");
    $stmt->execute([":username" => $_POST['username']]);
    $row = $stmt->fetch();
    
    if (!$row) {  // row not found, insert user
        $stmt = $dbh->prepare("insert into user (username) values (:username)");
        $stmt->execute([":username" => $_POST['username']]);
        println("Welcome, " . $_POST['username']);
        session_start();
        $_SESSION['username'] = $_POST['username'];
?>
    <a href="index.php">Go to the main page</a>
<?php 
    } else {
        println("Username " . $_POST['username'] . " is already taken.");
        displayForm();
    }
} else {
    displayForm();
}
?>
