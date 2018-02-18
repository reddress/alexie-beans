<?php
require_once("../db.php");
require_once("../util.php");

function displayForm() {
    echo '
    <form action="login.php" method="post">
Username: <input type="text" name="username">
<input type="submit">
</form>
';
}

if (isset($_POST['username'])) {
    if ($_POST['username'] === '') {
        $_POST['username'] = "guest";
    }
    
    $stmt = $dbh->prepare("select username from user where username=:username");
    $stmt->execute([":username" => $_POST['username']]);
    $row = $stmt->fetch();
    if ($row) {
        session_start();
        $_SESSION['username'] = $_POST['username'];
        println("Welcome back, " . $_POST['username']);
        println('<a href="index.php">Back to home</a>');
    } else {  // username not found
        println("Username " . $_POST['username'] . ' not found. Please try again or <a href="signup.php">sign up</a>');
        println("");
        displayForm();
    }
} else {
    displayForm();
}
?>
