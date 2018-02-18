<?php
require_once("../dbhost.php");
require_once("../util.php");

try {
    $sql = "create database if not exists $dbname
    character set utf8 collate utf8_general_ci";

    $dbh->exec($sql);
    $sql = "grant all on $dbname.* to $dbuser@$dbhost identified by '$dbpassword'";
    $dbh->exec($sql);

    println("DB $dbname is active");

    // Create tables
    $dbh->exec("use $dbname");

    // Determine if user table exists, if not, create all tables
    $stmt = $dbh->prepare("show tables like 'account_group_assoc'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        // User
        // username (varchar, PK)
        $dbh->exec("create table if not exists user
        (username varchar(32) not null,
            constraint pk_user primary key (username)) engine=InnoDB");
        println("Table user created");

        // Settings
        // username (FK), name, value
        $dbh->exec("create table if not exists settings
        (username varchar(32) not null,
         name varchar(32) not null,
         value int not null,
         constraint fk_setting_user foreign key (username)
         references user (username)) engine=InnoDB");
        println("Table settings created");
        
        // Currency
        // Code (PK), Symbol, username (PK, FK), name, cents
        // cents is usually 2 (having cents, like USD), or 0 (no cents, like TWD)
        $dbh->exec("create table if not exists currency
        (id int not null auto_increment,
         code varchar(8) not null,
         symbol char(3) not null,
         username varchar(32) not null,
         name varchar(32) not null,
         cents tinyint not null,
         constraint pk_currency primary key (id, code),
         constraint fk_currency_username foreign key (username)
         references user (username)) engine=InnoDB");
        println("Table currency created");
        
        // Account type
        // id (PK), username (FK), name (PK), sign
        // sign indicates whether balances should flip signs, such as incomes,
        // where an income should appear positive, though it is a credit.
        $dbh->exec("create table if not exists account_type
        (id int not null auto_increment,
         username varchar(32) not null,
         name varchar(32) not null,
         sign tinyint not null,
         constraint pk_account_type primary key (id, name),
         constraint fk_account_type_username foreign key (username)
         references user (username)) engine=InnoDB");
        println("Table account_type created");
        
        // Account
        // id (PK), name (PK), account_type (FK), username (FK)
        $dbh->exec("create table if not exists account
        (id int not null auto_increment,
         name varchar(128) not null,
         account_type int not null,
         username varchar(32) not null,
         constraint pk_account_id primary key (id, name, username),
         constraint fk_account_type foreign key (account_type)
         references account_type (id),
         constraint fk_account_username foreign key (username)
         references user (username)) engine=InnoDB");
        println("Table account created");            
        
        // Transaction
        // id (PK), username (FK), created, description, amount (int),
        // debit (FK), credit (FK)
        $dbh->exec("create table if not exists transaction
        (id int not null auto_increment,
         username varchar(32) not null,
         description varchar(128) not null,
         currency int not null,
         amount int not null,
         debit int not null,
         credit int not null,
         created datetime not null,
         constraint pk_transaction primary key (id),
         constraint fk_transaction_username foreign key (username)
         references user (username),
         constraint fk_transaction_currency foreign key (currency)
         references currency (id),
         constraint fk_transaction_debit foreign key (debit)
         references account (id),
         constraint fk_transaction_credit foreign key (credit)
         references account (id)) engine=InnoDB");
        println("Table transaction created");

        // account_group
        // id (PK), username (FK), name (PK)
        $dbh->exec("create table if not exists account_group
        (id int not null auto_increment,
         name varchar(64) not null,
         username varchar(32) not null,
         constraint pk_account_group primary key (id, username, name),
         constraint fk_account_group_username foreign key (username)
         references user (username)) engine=InnoDB");
        println("Table account_group created");
        
        // account_group_assoc
        // group_id (FK), account_id (FK)
        // TODO: Both columns should be Primary Key
        $dbh->exec("create table if not exists account_group_assoc
        (account_group_id int not null,
         account_id int not null,
         constraint fk_account_group_assoc_acct_group foreign key (account_group_id)
         references account_group (id),
         constraint fk_account_group_assoc_account foreign key (account_id)
         references account (id)) engine=InnoDB");
        
        println("Table account_group_assoc created");

        // create guest account
        $dbh->exec("insert into user (username) values ('')");
        $dbh->exec("insert into user (username) values ('guest')");
        println("Inserted guest user");

        // create base data
        $dbh->exec("insert into user (username) values ('heitor')");


        /*
        $dbh->exec("insert into currency (code, symbol, username, name, cents)
        values ('BRL', 'R$', 'heitor', 'Real', 2),
        ('USD', '$', 'heitor', 'Dólar', 2),
        ('TWD', 'NT$', 'heitor', 'New Taiwan Dollar', 0)");

        $dbh->exec("insert into account_type (username, name, sign)
        values ('heitor', 'Assets', 1),
        ('heitor', 'Expenses', 1),
        ('heitor', 'Liabilities', -1),
        ('heitor', 'Income', -1),
        ('heitor', 'Equity', -1)");

        $dbh->exec("insert into settings (username, name, value)
        values ('heitor', 'base_currency', 1)");

        $dbh->exec("insert into account (username, name, account_type)
        values ('heitor', 'open', 5),
        ('heitor', 'ptlsal', 4), 
        ('heitor', 'wal', 1),    
        ('heitor', 'itcor', 1),  
        ('heitor', 'groc', 2)"); 

        $dbh->exec("insert into transaction (username, description, currency, amount, debit, credit, created)
        values ('heitor', 'Opening balance', 1, 410000, 4, 1, '2015-09-30 12:00:00'),
        ('heitor', 'Sept salary', 1, 449500, 3, 2, '2015-09-28 15:30:00'),
        ('heitor', 'Opening balance', 2, 99000, 3, 1, '2015-08-15 12:00:00'),
        ('heitor', 'Opening balance', 3, 7200, 3, 1, '2015-08-15 12:00:00'),
        ('heitor', 'xfer', 1, 300000, 4, 3, '2015-10-02 09:00:00'),
        ('heitor', 'Agua de coco', 1, 550, 5, 3, '2015-10-03 17:00:00'),
        ('heitor', 'Hot dog', 2, 320, 5, 3, '2015-10-03 17:00:00'),
        ('heitor', 'Pineapple cake', 3, 90, 5, 3, '2015-05-22 19:00:00')");
        */
    } else {
        println("Tables already exist");
    }
    
} catch (PDOException $e) {
    die($e->getMessage());
}
?>
