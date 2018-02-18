<?php
require_once("header.php");
require_once("formatters.php");
?>

<form action="search.php" method="get">
    <select name="currency">
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

    Search <input name="keywords" autofocus> From <input name="start_date" id="start_date_input" value="2000-01-01" size="12"> to <input name="end_date" id="end_date_input" value="2100-01-01" size="12">
    <button type="submit">Search</button>
</form>
<br>
<?php 
if (isset($_GET["keywords"]) &&strlen($_GET["keywords"]) > 0) {
    $start_datetime = $_GET["start_date"] . " 00:00:00";
    $end_datetime = $_GET["end_date"] . " 23:59:59";
?>

    <script>
     // fill in given dates
     document.getElementById("start_date_input").value = "<?= $_GET["start_date"] ?>";
     document.getElementById("end_date_input").value = "<?= $_GET["end_date"] ?>";
    </script>

    <?php 

    $sql = "select date_format(t.created, '%Y-%m-%d %H:%i %a') as created, t.description, c.symbol, c.cents, t.amount, debit.name as debit, credit.name as credit
    from transaction t
    inner join currency c on t.currency = c.id
    inner join account debit on t.debit = debit.id
    inner join account credit on t.credit = credit.id
    inner join user u on t.username = u.username
    where t.username = :username
    and t.description like :keywords
    and t.created >= :start_created
    and t.created <= :end_created
    and t.currency = :currency
    order by created desc, code, amount desc";

    $stmt = $dbh->prepare($sql);
    $stmt->execute([":username" => $_SESSION['username'],
                    ":keywords" => "%" . $_GET['keywords'] . "%",
                    ":start_created" => $start_datetime,
                    ":end_created" => $end_datetime,
                    ":currency" => $_GET['currency']]);

    $total_sql = "select sum(t.amount) as total, c.cents, c.symbol
    from transaction t
    inner join currency c on t.currency = c.id
    inner join account debit on t.debit = debit.id
    inner join account credit on t.credit = credit.id
    inner join user u on t.username = u.username
    where t.username = :username
    and t.description like :keywords
    and t.created >= :start_created
    and t.created <= :end_created
    and t.currency = :currency
group by c.cents, c.symbol";  // satisfy ubuntu sql mode

    $total_stmt = $dbh->prepare($total_sql);
    $total_stmt->execute([":username" => $_SESSION['username'],
                          ":keywords" => "%" . $_GET['keywords'] . "%",
                          ":start_created" => $start_datetime,
                          ":end_created" => $end_datetime,
                          ":currency" => $_GET['currency']]);
    foreach ($total_stmt as $total_row) {
        if ($total_row['cents'] > 0) {
            $total_row['total'] = separate_amount($total_row['total']);
        }
        
        print("Total: {$total_row['symbol']} {$total_row['total']}");
    }

    
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

<?php } ?>
