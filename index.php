<?php

require_once 'src/Order.php';
$pdo = require_once "db.php";

use src\Order;

$createTable = "CREATE TABLE IF NOT EXISTS `orders` (
    `order_id` VARCHAR(15) NOT NULL,
    `items` JSON NOT NULL,
    `done` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";



$statement = $pdo->prepare($createTable);
$statement->execute();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUrl = $_SERVER['REQUEST_URI'];

$order = new Order();

//создание нового заказа
if ($requestMethod === 'POST' && $requestUrl === '/orders') {

    $insertDb = 'INSERT INTO orders (order_id, items, done) VALUES (?, ?, ?)';
    $statement = $pdo->prepare($insertDb);

    $body = json_decode(file_get_contents('php://input'), true);

    if (!isset($body['items']) || !is_array($body['items']) || empty($body['items'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Items cannot be empty']);
        exit;
    }

    $order = new Order();
    $order->setItems($body['items']);

    $orderId = $order->getOrderId();
    $orderItemsJson = json_encode($body['items']);
    $orderDone = 0;

    $statement->execute([$orderId, $orderItemsJson, $orderDone]);

    $done = $orderDone ? true : false;

    http_response_code(200);
    echo json_encode([
        "order_id" => $orderId,
        "items" => $body['items'], 
        "done" => $done
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//изменение существующего
elseif($requestMethod === 'POST' && preg_match('#^/orders/([a-f0-9]{15})/items$#', $requestUrl, $matches)){

    $body = json_decode(file_get_contents('php://input'), true);
    
    if (!is_array($body) || empty($body)) {
        http_response_code(400);
        echo json_encode(['error' => 'Items must be a non-empty array']);
        exit;
    }

    $orderId = $matches[1];

    $statement = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $statement->execute([$orderId]);
    $order = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        exit;
    }

    if ($order['done'] === true || $order['done'] === 1) {
        http_response_code(400);
        exit;
    }

    $updateStmt = $pdo->prepare("UPDATE orders SET items = ? WHERE order_id = ?");
    $updateStmt->execute([json_encode($body), $orderId]);
    http_response_code(200);
    exit;
}

// Получение информации по конкретному заказу
elseif($requestMethod === 'GET' && preg_match('#^/orders/([a-f0-9]{15})$#', $requestUrl, $matches)){

    $orderId = $matches[1];
    $statement = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $statement->execute([$orderId]);
    $order = $statement->fetch(PDO::FETCH_ASSOC);

    if (!is_array($order) || empty($order)) {
        http_response_code(400);
        echo json_encode(['error' => 'Items must be a non-empty array']);
        exit;
    }

    $orderDone = $order['done'] ? true : false;
    http_response_code(200);
    echo json_encode([
        "order_id" => $order['order_id'],
        "items" => $order['items'], 
        "done" => $orderDone
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Повар помечает заказ как выполненный.
elseif($requestMethod === 'POST' && preg_match('#^/orders/([a-f0-9]{15})/done$#', $requestUrl, $matches)){
    
    if (!isset($_SERVER['HTTP_X_AUTH_KEY']) || $_SERVER['HTTP_X_AUTH_KEY'] !== 'qwerty123') {
        http_response_code(401); 
        echo json_encode(['error' => 'Missing or invalid X-Auth-Key']);
        exit;
    }

    $orderId = $matches[1];
    $statement = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $statement->execute([$orderId]);
    $order = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Items not found']);
        exit;
    }

    if ($order['done'] === true || $order['done'] === 1) {
        http_response_code(400);
        echo json_encode(['error' => 'Items have the status of done']);
        exit;
    }
    
    $updateStmt = $pdo->prepare("UPDATE orders SET done = ? WHERE order_id = ?");
    $updateStmt->execute([1, $orderId]);
    http_response_code(200);
}

// Получение списка всех заказов.
elseif ($requestMethod === 'GET' && preg_match('#^/orders$#', $requestUrl)) {
    
    if (!isset($_SERVER['HTTP_X_AUTH_KEY']) || $_SERVER['HTTP_X_AUTH_KEY'] !== 'qwerty123') {
        http_response_code(401); 
        echo json_encode(['error' => 'Missing or invalid X-Auth-Key']);
        exit;
    }

    $doneFilter = null;
    if (isset($_GET['done'])) {
        $doneFilter = match ($_GET['done']) {
            '1', 'true' => 1,
            '0', 'false' => 0,
            default => null,
        };
    }

    if ($doneFilter !== null) {
        $statement = $pdo->prepare("SELECT * FROM orders WHERE done = ?");
        $statement->execute([$doneFilter]);
    } else {
        $statement = $pdo->query("SELECT * FROM orders");
    }
    $orders = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $order['items'] = json_decode($order['items'], true);
    }

    if (!$orders) {
        echo json_encode([]);
        exit;
    }
    
    http_response_code(200);
    echo json_encode($orders, JSON_UNESCAPED_UNICODE);
    exit;
}
