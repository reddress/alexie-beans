<script src="js/parsers.js"></script>

<?php
require_once("header.php");
require_once("formatters.php");

// set default timezone
date_default_timezone_set('America/Sao_Paulo');

// retrieve account ids
$sql = "select a.id, a.name
from account a
inner join user u on a.username = u.username
where a.username = :username
order by name";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username']]);

$account_ids = $stmt->fetchAll();

// retrieve currencies
$sql = "select c.id, c.code
from currency c
inner join user u on c.username = u.username
where c.username = :username
order by id";

$stmt = $dbh->prepare($sql);
$stmt->execute(["username" => $_SESSION['username']]);

$currency_ids = $stmt->fetchAll();


function displayAccountSelect($name, $accounts) {
    $select = "<select name='$name'>";
    $select .= "<option value='0'>$name</option>";
    foreach ($accounts as $account) {
        $select .= "<option value='{$account['id']}'>{$account["name"]}</option>";
    }
    $select .= "</select>";
    print($select);
}

function displayCurrencySelect($currencies) {
    $select = "<select name='currency'>";
    foreach ($currencies as $currency) {
        $select .= "<option value='{$currency['id']}'>{$currency['code']}</option>";
    }
    $select .= "</select>";
    print($select);
}

?>
<form action="transactions.php" method="post">
    <table>
        <tr>
            <td>Description</td>
            <td><input type="text" name="description" size="28" autofocus></td>
        </tr>
        <tr>
            <td>Amount</td>
            <td><?php displayCurrencySelect($currency_ids) ?>
                <!-- <input type="hidden" name="currency" value="1">BRL -->
                <input type="text" name="amount" size="9">
            </td>
        </tr>
        <tr>
            <td>Debit/Credit</td>
            <td><?php displayAccountSelect("debit", $account_ids) ?>
                <?php displayAccountSelect("credit", $account_ids) ?>
            </td>
        </tr>
        <tr>
            <td>Date created</td>
            <td><input type="text" name="date" id="dateCreated" value="<?= date('Y-m-d') ?>" size="12">
                <input type="text" name="time" value="<?= date('H:i') ?>" size="8">
            </td>
        </tr>
    </table>
    <input type="submit">
</form>

<?php

// insert a transaction if form was submitted
if (isset($_POST['description'])) {
    $datetime = $_POST['date'] . " " . $_POST['time'];

    // determine whether amount in cents should be parsed
    $stmt = $dbh->prepare("select cents from currency where id = :id");
    $stmt->execute([":id" => $_POST["currency"]]);
    $cents_row = $stmt->fetch();

    $amount = $_POST['amount'];
    
    if ((int) $cents_row['cents'] !== 0) {
        $amount = parse_amount($amount);
    }

    $sql = "insert into transaction (username, description, currency, amount, debit, credit, created)
values (:username, :description, :currency, :amount, :debit, :credit, :created)";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([":username" => $_SESSION['username'],
                    ":description" => $_POST['description'],
                    ":currency" => $_POST['currency'],
                    ":amount" => $amount,
                    ":debit" => $_POST['debit'],
                    ":credit" => $_POST['credit'],
                    ":created" => $datetime]);
    println("Inserted transaction {$_POST['description']} {$_POST['amount']}");
    println("");
}

// display 30 newest transactions
$sql = "select date_format(t.created, '%Y-%m-%d %H:%i %a') as created, t.description, c.symbol, c.cents, t.amount, debit.name as debit, credit.name as credit
from transaction t
inner join currency c on t.currency = c.id
inner join account debit on t.debit = debit.id
inner join account credit on t.credit = credit.id
inner join user u on t.username = u.username
where t.username = :username
order by created desc, code, amount desc limit 30";

$stmt = $dbh->prepare($sql);
$stmt->execute([":username" => $_SESSION['username']]);

?>
30 newest transactions

<?php 
// count number of transactions
$count_sql = "select count(id) as count_id from transaction
where username = :username";

$count_stmt = $dbh->prepare($count_sql);
$count_stmt->execute(["username" => $_SESSION['username']]);

print("(" . $count_stmt->fetch()['count_id'] . " total)");
?>

<table id="latest_transactions">
    <thead>
        <tr>
            <th>Created</th>
            <th>Description</th>
            <th class="right_align">Amount</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
    </thead>
<?php 
foreach ($stmt as $row) {
    $amount_value = $row['amount'];
    if ((int) $row['cents'] > 0) {
        $amount_value = separate_amount($amount_value);
    }
    print("<tr>
        <td>{$row['created']}</td>
        <td>{$row['description']}</td>
        <td class='right_align'>{$row['symbol']} $amount_value</td>
        <td>{$row['debit']}</td>
        <td>{$row['credit']}</td>
        </tr>");
}
?>
    
</table>

<?php 
require_once("footer.php");
?>

