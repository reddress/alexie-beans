<!-- <form action="balances.php" method="get"> -->
<!-- Write the above prior to including this file -->
<!-- Also, close the form after including -->
<?php
if (isset($_GET["currency"])) {
    $currency = $_GET["currency"];
} else {
    // load base currency from settings
    $sql = "select value from settings where username = :username
    and name = 'base_currency'";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([":username" => $_SESSION["username"]]);

    $currency = $stmt->fetch()['value'];
}

if (!isset($_GET["start"])) {
    $start_date = "2000-01-01";
} else {
    $start_date = $_GET['start'];
}
$start_datetime = $start_date . " 00:00:00";

if (!isset($_GET["end"])) {
    $end_date = "2100-01-01";
} else {
    $end_date = $_GET["end"];
}
$end_datetime = $end_date . " 23:59:59";

// separate cents and whole amounts?
$stmt = $dbh->prepare("select symbol, cents from currency where id = :id");
$stmt->execute([":id" => $currency]);
$currency_row = $stmt->fetch();
$cents = (int) $currency_row['cents'];
$currency_symbol = $currency_row['symbol'];

?>

<!-- DISABLE DATE FILTER
<table>
    <tr>
        <td>
            <select name="currency" onchange="this.form.submit()">
                <?php
                // build list of currencies
                $sql = "select id, code from currency where username = :username";
                $stmt = $dbh->prepare($sql);
                $stmt->execute([":username" => $_SESSION["username"]]);
                
                foreach ($stmt as $currency_row) {
                    $selected = ($currency_row['id'] == $currency) ? " selected" : "";
                    print("<option value='{$currency_row['id']}'$selected>{$currency_row['code']}</option>");
                }
                ?>
            </select>
        </td>

        <td>
            <button type="button" onclick="reset_dates(); this.form.submit();">Reset dates</button>
            <button type="button" onclick="set_this_month(); this.form.submit();" autofocus>This month</button>
        </td>

        <td>
            From
        </td>
        <td>
            <input type="text" size="12" id="start_text" name="start" value="<?= $start_date ?>">
            To
            <input type="text" size="12" id="end_text" name="end" value="<?= $end_date ?>">
            <input type="submit">
        </td>
    </tr>
</table>
-->
