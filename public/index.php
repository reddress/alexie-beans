<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ALEXIE Beans</title>
        <link href="css/alexie_beans.css" rel="stylesheet" type="text/css">
    </head>
    <body>

        <?php
        require_once("../util.php");

        session_start();

        // Check if user is already logged in
        if (isset($_SESSION['username'])) {  
            print("Welcome, " . $_SESSION['username']);
        ?>
            | <a href="logout.php">Logout</a>

            <hr>

            <a href="balances.php">Balances</a><br>
            <br>
            
            <a href="transactions.php">Transactions</a><br>
            <!-- <a href="accounts.php">Accounts</a><br> -->
            <a href="acctgroups.php">Account groups</a><br>
            <br>
            <!-- <a href="currencies.php">Currencies</a><br> -->
            <!--  <a href="account_types.php">Account types</a><br> -->
            
        <?php
        } else {
        ?>
            <form action="login.php" method="post">
                Please login or <a href="signup.php">sign up</a><br>
                <br>
                Username: <input type="text" name="username" autofocus><br>
                <input type="submit">
            </form>
        <?php
        }
        ?>

        <hr>
        <a href="https://github.com/heitorchang/alexie-beans">Source on Github</a>
    </body>
</html>
