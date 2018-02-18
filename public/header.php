<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ALEXIE Beans</title>
        <link href="css/alexie_beans.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        
        <?php
        require_once("../db.php");
        require_once("../util.php");

        // set default timezone
        date_default_timezone_set('America/Sao_Paulo');
        
        session_start();

        if (!isset($_SESSION['username'])) {
            println('Please <a href="login.php">log in</a>');
            exit(0);
        } else {
            print("<a href='index.php'>ALEXIE Beans</a> ");
            print("(" . $_SESSION['username'] . ")");
        ?>
            <a href="balances.php">Balances (all time)</a>

            <a href="balances.php?start=<?= (new DateTime("first day of this month"))->format("Y-m-d") ?>">(this month)</a> |

            <a href="transactions.php">Add transaction</a> |
            <a href="search.php">Search</a> |
            <!--  <a href="accounts.php">Accounts</a> | -->
            <!--  <a href="acctgroups.php">Acct groups (all time)</a> -->
            <!--  <a href="acctgroups.php?start=<?= (new DateTime("first day of this month"))->format("Y-m-d") ?>">(this month)</a> -->
            
            <a href="acctgroups_all_and_this_month.php">Groups summary</a>
            <!-- <a href='logout.php'>Logout</a> -->
            <hr>
        <?php 
        }
        ?>
