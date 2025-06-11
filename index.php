<?php

require_once 'src/Order.php';
$pdo = require_once "db.php";

use src\Order;

$order = new Order();
$order->setItems([1,2,3,4,5]);

$createTable = "CREATE TABLE IF NOT EXISTS `order` (
    `order_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `items` TEXT NOT NULL,
    `done` BOOLEAN NOT NULL,
    PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";



$statement = $pdo->prepare($createTable);
$statement->execute();

$insertDb = 'INSERT INTO order(items, done) VALUES (?, ?)';
$statement = $pdo->prepare($insertDb);
$statement->execute([1, false]);