<?php
require_once("header.php");

$acct_group_member_sql = "select acct.name from account acct 
inner join account_group_assoc aassoc on aassoc.account_id = acct.id
where aassoc.account_group_id = :acct_group_id";

$acct_groups_sql = "select id, name from account_group where
username = :username";

$acct_groups_stmt = $dbh->prepare($acct_groups_sql);
$acct_groups_stmt->execute([":username" => $_SESSION['username']]);

foreach ($acct_groups_stmt as $acct_group_row) {
    print($acct_group_row['name'] . "<br>");
    $acct_group_member_stmt = $dbh->prepare($acct_group_member_sql);
    $acct_group_member_stmt->execute([":acct_group_id" => $acct_group_row['id']]);
    foreach ($acct_group_member_stmt as $member_row) {
        print($member_row['name'] . " ");
    }
    print("<hr>");    
}

?>
