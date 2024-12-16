<?php
include 'db.php';
session_start();

$user = $_SESSION['user'] ?? null;

if (!$user) {
    header('Location: login.php');
    exit;
}

// Получение заказов клиента
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Мои заказы</h1>
        <nav>
            <a href="index.php">Каталог</a>
            <a href="cart.php">Корзина</a>
            <a href="logout.php">Выход</a>
        </nav>
    </header>
    <main>
        <section class="order-list">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <p><strong>Заказ #<?= $order['id'] ?></strong></p>
                        <p>Статус: <?= $order['status'] ?></p>
                        <p>Пункт выдачи: <?= htmlspecialchars($order['pickup_point']) ?></p>
                        <p>Адрес: <?= htmlspecialchars($order['address']) ?></p>
                        <p>Город: <?= htmlspecialchars($order['city']) ?></p>
                        <h4>Товары в заказе:</h4>
                        <ul>
                            <?php
                            // Получаем товары из заказа
                            $order_id = $order['id'];
                            $stmt = $pdo->prepare("SELECT p.name, p.price FROM products p 
                                                   JOIN order_products op ON p.id = op.product_id
                                                   WHERE op.order_id = ?");
                            $stmt->execute([$order_id]);
                            $products_in_order = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php foreach ($products_in_order as $product): ?>
                                <li><?= htmlspecialchars($product['name']) ?> - <?= $product['price'] ?> руб.</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>У вас нет заказов.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
