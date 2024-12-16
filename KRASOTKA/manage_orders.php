<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $order_id = $_POST['order_id'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
}

$orders = $pdo->query("
    SELECT orders.id, users.username, orders.status, pickup_points.address 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    JOIN pickup_points ON orders.pickup_point_id = pickup_points.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами</title>
</head>
<body>
    <h1>Управление заказами</h1>
    <?php foreach ($orders as $order): ?>
        <form method="post">
            <p>Заказ #<?= $order['id'] ?> - Клиент: <?= htmlspecialchars($order['username']) ?> - Пункт выдачи: <?= htmlspecialchars($order['address']) ?></p>
            <select name="status">
                <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>В ожидании</option>
                <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Отправлен</option>
                <option value="Received" <?= $order['status'] === 'Received' ? 'selected' : '' ?>>Получен</option>
            </select>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <button type="submit">Обновить</button>
        </form>
    <?php endforeach; ?>
</body>
</html>
