<?php
require_once("header.php");
require_once("formatters.php");
?>

<?php

$currency_settings_sql = "select value from settings where username = :username
and name = 'base_currency'";

$stmt = $dbh->prepare($currency_settings_sql);
$stmt->execute([":username" => $_SESSION["username"]]);

$currency = $stmt->fetch()['value'];

$acctgroups_sql = "select id, name from account_group where username = :username";
$stmt = $dbh->prepare($acctgroups_sql);
$stmt->execute([":username" => $_SESSION["username"]]);
?>

<a href="acctgroup_members.php">View all account group members</a><br><br>
<table>
    <tr>
        <td>Group</td><td>&nbsp;</td><td align="right">All time</td><td>&nbsp;</td><td align="right">This month</td>
    </tr>
    <?php 
foreach ($stmt as $acct_row) {
    print("<tr><td>");
    print($acct_row['name'] . "</td><td>");

    // override form dates
    $start_datetime = (new DateTime("first day of this month"))->format("Y-m-d");
    $end_datetime = "2100-01-01";
    
    // get debits for this month
    // TODO: this simple query does not consider account type signs
    $acct_debits_sql = "select sum(amount) as acct_debits from transaction
where debit in
(select account_id from account_group_assoc where
account_group_id = :account_group_id)
and currency = :currency
and created >= :start_created
and created <= :end_created";


    $acct_debits_stmt = $dbh->prepare($acct_debits_sql);
    $acct_debits_stmt->execute([":account_group_id" => $acct_row['id'],
                                ":currency" => $currency,
                                ":start_created" => $start_datetime,
                                ":end_created" => $end_datetime]);

    foreach ($acct_debits_stmt as $acct_debits_row) {
        //print("acct debits row: " . separate_amount($acct_debits_row['acct_debits']));
        //print("<br>");
        $acct_debits_amount = $acct_debits_row['acct_debits'];
    }

    // get credits for this month
    // TODO: this simple query does not consider account type signs
    $acct_credits_sql = "select -sum(amount) as acct_credits from transaction
where credit in
(select account_id from account_group_assoc where
account_group_id = :account_group_id)
and currency = :currency
and created >= :start_created
and created <= :end_created";


    $acct_credits_stmt = $dbh->prepare($acct_credits_sql);
    $acct_credits_stmt->execute([":account_group_id" => $acct_row['id'],
                                 ":currency" => $currency,
                                 ":start_created" => $start_datetime,
                                 ":end_created" => $end_datetime]);

    foreach ($acct_credits_stmt as $acct_credits_row) {
        //print("acct credits row: " . separate_amount($acct_credits_row['acct_credits']));
        //print("<br>");
        $acct_credits_amount = $acct_credits_row['acct_credits'];
    }

    $acct_total_this_month = $acct_debits_amount + $acct_credits_amount;

    // get values for all time

    $acct_debits_sql = "select sum(amount) as acct_debits from transaction
where debit in
(select account_id from account_group_assoc where
account_group_id = :account_group_id)
and currency = :currency";

    $acct_debits_stmt = $dbh->prepare($acct_debits_sql);
    $acct_debits_stmt->execute([":account_group_id" => $acct_row['id'],
                                ":currency" => $currency]);

    foreach ($acct_debits_stmt as $acct_debits_row) {
        //print("acct debits row: " . separate_amount($acct_debits_row['acct_debits']));
        //print("<br>");
        $acct_debits_amount = $acct_debits_row['acct_debits'];
    }

    // get credits 
    // TODO: this simple query does not consider account type signs
    $acct_credits_sql = "select -sum(amount) as acct_credits from transaction
where credit in
(select account_id from account_group_assoc where
account_group_id = :account_group_id)
and currency = :currency";
    
    $acct_credits_stmt = $dbh->prepare($acct_credits_sql);
    $acct_credits_stmt->execute([":account_group_id" => $acct_row['id'],
                                 ":currency" => $currency,]);

    foreach ($acct_credits_stmt as $acct_credits_row) {
        //print("acct credits row: " . separate_amount($acct_credits_row['acct_credits']));
        //print("<br>");
        $acct_credits_amount = $acct_credits_row['acct_credits'];
    }

    $acct_total_all_time = $acct_debits_amount + $acct_credits_amount;

    
    //    print("Total: ");
    print("</td><td align='right'>" . separate_amount($acct_total_all_time) . "</td><td>&nbsp;</td><td align='right'>" . separate_amount($acct_total_this_month) . "</td></tr>");
}

// local ubuntu: account_group
// id 1 = bank
// id 2 = wal

// ids 4 and 7 are itcor and itpou = bank
// id 3 is wal
?>
</table>

<?php
require_once("footer.php");
?>

