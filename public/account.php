<?php
require_once("header.php");
require_once("formatters.php");

// set transaction row limit to 30 by default
if (isset($_GET["row_limit"])) {
    $row_limit = $_GET["row_limit"];
} else {
    $row_limit = 30;
}

?>

<form action="account.php" method="get">
    <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
    <?php 
    require_once("currency_date_form.php");
    ?>
    Show how many transactions? <input type="text" name="row_limit" value="<?= $row_limit ?>">
</form>

<!-- balances.php?currency=1&start=2000-01-01&end=2100-01-02 -->
<a href="balances.php?currency=<?= $currency ?>&amp;start=<?= $start_date ?>&amp;end=<?= $end_date ?>">Balances for this time period</a>
<br><br>

<?php

$lazy_sql = "select created, description, amount, debit, credit from transaction
where username = :username
and (debit = :debit or credit = :credit)
and currency = :currency
and created >= :start_created
and created <= :end_created
order by created desc
limit :row_limit";

$full_sql = "select date_format(t.created, '%Y-%m-%d %H:%i %a') as created,
t.description as description,
t.amount as amount, ad.name as debit, ac.name as credit from transaction t
inner join account ad on ad.id = t.debit
inner join account ac on ac.id = t.credit
where t.username = :username
and (t.debit = :debit or t.credit = :credit)
and t.currency = :currency
and t.created >= :start_created
and t.created <= :end_created
order by t.created desc
limit :row_limit";

$stmt = $dbh->prepare($full_sql);
$stmt->execute([":username" => $_SESSION["username"],
                ":debit" => $_GET['id'],
                ":credit" => $_GET['id'],
                ":currency" => $currency,
                ":start_created" => $start_datetime,
                ":end_created" => $end_datetime,
                ":row_limit" => $row_limit]);
?>
<table id="latest_transactions">
    <?php 
    foreach ($stmt as $row) {
        print("<tr>");
        print("<td>{$row['created']}</td>");
        print("<td>{$row['description']}</td>");
        print("<td class='right_align'>");
        if ($cents > 0) {
            print(separate_amount($row['amount']));
        } else {
            print($row['amount']);
        }
        print("</td>");
        print("<td>{$row['debit']}</td>");
        print("<td>{$row['credit']}</td>");        
        print("</tr>");
    }
    ?>

</table>


<?php 
require_once("footer.php");
?>

<script src="js/thirdparty/moment.min.js"></script>
<script src="js/date_presets.js"></script>
