<?php
require_once("header.php");
require_once("formatters.php");
?>
<form action="balances.php" method="get">
    <?php
    require_once("currency_date_form.php");
    ?>
</form>
<?php 

// initialize account sums
$account_balances = [];
$account_debits = [];
$account_credits = [];
$account_names = [];

// get all accounts
$all_accounts_sql = "select id, name from account where username = :username";
$stmt = $dbh->prepare($all_accounts_sql);
$stmt->execute([":username" => $_SESSION["username"]]);

foreach ($stmt as $row) {
    $account_balances[$row['id']] = 0;
    $account_debits[$row['id']] = 0;
    $account_credits[$row['id']] = 0;
    $account_names[$row['id']] = $row['name'];
}

// tables side-by-side, one for each account type
//
// first, get account types
$account_types_sql = "select id, name from account_type where username = :username";
$stmt = $dbh->prepare($account_types_sql);
$stmt->execute([":username" => $_SESSION["username"]]);

$account_types = [];
$account_type_totals = [];
$account_ref = [];  // hold account data, as a sublevel of account type

foreach ($stmt as $type_row) {
    $account_types[$type_row['id']] = $type_row['name'];
    $account_type_totals[$type_row['id']] = 0;
    $account_ref[$type_row['id']] = [];

    // then get names of accounts matching the type
    $accounts_of_type_sql = "select id, name from account
where username = :username
and account_type = :account_type
order by name";
    $account_stmt = $dbh->prepare($accounts_of_type_sql);
    $account_stmt->execute([":username" => $_SESSION["username"],
                            ":account_type" => $type_row['id']]);
    foreach($account_stmt as $account_row) {
        $account_ref[$type_row['id']][$account_row['id']] = $account_row['name'];
    }
}

// define common transaction criteria
$transactions_criteria_sql = "inner join account_type at on a.account_type = at.id
where t.currency = :currency
and t.created >= :start_created
and t.created <= :end_created
and a.username = :username
group by a.id, at.sign"; // FIX FOR full_group_by ERROR: group by a.id, at.sign

// then get debits multiplied by sign
$debits_of_account_sql = "select a.id, sum(t.amount) * at.sign as total
from account a
inner join transaction t on a.id = t.debit " . $transactions_criteria_sql;

$stmt = $dbh->prepare($debits_of_account_sql);
$stmt->execute([":currency" => $currency,
                ":start_created" => $start_datetime,
                ":end_created" => $end_datetime,
                ":username" => $_SESSION["username"]]);
foreach ($stmt as $row) {
    $account_debits[$row['id']] = $row['total'];
}

// then get credits multiplied by sign
$credits_of_account_sql = "select a.id, sum(t.amount) * at.sign * -1 as total
from account a
inner join transaction t on a.id = t.credit " . $transactions_criteria_sql;

$stmt = $dbh->prepare($credits_of_account_sql);
$stmt->execute([":currency" => $currency,
                ":start_created" => $start_datetime,
                ":end_created" => $end_datetime,
                ":username" => $_SESSION["username"]]);
foreach ($stmt as $row) {
    $account_credits[$row['id']] = $row['total'];
}

// sum debits and credits to get total
foreach ($account_balances as $id => $value) {
    $account_balances[$id] = $account_debits[$id] + $account_credits[$id];
}

// budget total
$budget_total = 0;

// sort by account type
foreach ($account_types as $type_id => $type) {
    print("<table class='balances'>");
    print("<thead>
    <tr>
        <th colspan='2'>$type</th>
    </tr>
    </thead>");
    
    foreach ($account_ref[$type_id] as $account_id => $account_name) {
        // update type total
        $account_type_totals[$type_id] += $account_balances[$account_id];
        
        print("<tr>");
        print("<td>");
        print("<a href='account.php?id=$account_id&amp;currency=$currency&amp;start=$start_date&amp;end=$end_date'>$account_name</a></td>");
        print("<td class='right_align'>");
        if ($cents > 0) {
            print(separate_amount($account_balances[$account_id]));
        } else {
            print($account_balances[$account_id]);
        }

      // personal patch to insert budget % here
      // add only to expenses
      
      // if ($type == "Expenses") {
        // hard-coded budget, lack of cents offsets percentage x 100
        //$budget = ["groc" => 160,
        //           "junk" => 1,
        //           "jun" => 0];
        // if (isset($budget[$account_name]) && $budget[$account_name] > 0) {
        //  $acct_budget = $budget[$account_name];
        //  $budget_percentage = round($account_balances[$account_id] / $acct_budget) . "%";
        //  $budget_total += $acct_budget;
        // } else {
        //  $acct_budget = 0;
        //  $budget_percentage = "";
        // }
        // print(" / </td><td align='right'> $acct_budget = </td><td align='right'>$budget_percentage");
      // }
      
        print("</td>");
        print("</tr>");
    }
  // print("<tr><td colspan='2'><hr></td></tr>");
    print("<tr><td class='balance-total'>Total</td><td align='right'>$currency_symbol ");
    if ($cents > 0) {
        print(separate_amount($account_type_totals[$type_id]));
    } else {
        print($account_type_totals[$type_id]);
    }

  // budget total
  // if ($type == "Expenses") {
  //  print(" / </td><td align='right'> $budget_total = </td>");
  //  $total_budget_percentage = round($account_type_totals[$type_id] / $budget_total);
  //  print("<td>$total_budget_percentage%</td>");
  // }
  
    print("</table>");
}

require_once("footer.php");
?>

<script src="js/thirdparty/moment.min.js"></script>
<script src="js/date_presets.js"></script>
