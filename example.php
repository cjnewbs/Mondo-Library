<?php
function __autoload($class)
{
    if (file_exists("$class.php")) {
        require_once("$class.php");
    } else {
        die("Autoloader failed to load $class.php");
    }
}
session_start();
$mondo = new mondo;
require_once 'config.php';
// everything above this line should be present in all pages

//this needs to be present on pages after the OAuth stage has completed
$mondo->loadSession();
?><html>
<head>
    <meta charset="UTF-8">
    <title>Connect To Mondo</title>
</head>
<body>
<div class="container">
    <p><a href="logout.php">Log Out</a></p>
    <p>Account Name: <?php echo $mondo->getAccountName(); ?></p>
    <p>Account ID: <?php echo $mondo->getAccountID(); ?></p>
    <p>Balance: <?php echo $mondo->getBalance(); ?></p>
    <p>Today you spent: <?php echo $mondo->getTodaySpend(); ?></p>
    <p>Your account was created on: <?php echo $mondo->getAccountCreated() ?></p>
    <p>Your recent transactions:</p>
</div>
<table>
    <thead>
        <tr><th>Transaction ID</th><th>Description</th><th>Amount</th></tr>
    </thead>
        <?php
        $transactions = $mondo->getTransactionList();
        foreach ($transactions['transactions'] as $item) {
            echo '<tr>';
            echo '<td>' . $item['id'] . '</td>';
            echo '<td>' . $item['description'] . '</td>';
            echo '<td>' . number_format(($item['amount']/100), 2, '.', ',') . '</td>';
            echo "</tr>\n";
        }
        ?></table>
</body>
</html>