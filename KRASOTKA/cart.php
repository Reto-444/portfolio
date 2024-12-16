<?php
include 'db.php';
session_start();

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header('Location: index.php');
    exit;
}

// Получаем информацию о пользователе
$user = $_SESSION['user'] ?? null;

// Получаем список пунктов выдачи с адресами и городами из базы данных
$pickup_points = [];
$stmt = $pdo->query("SELECT id, name, address, city FROM pickup_points");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pickup_points[] = $row;
}

// Оформление заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pickup_point_id'])) {
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    // Получаем данные из формы
    $pickup_point_id = $_POST['pickup_point_id'];

    // Получаем информацию о выбранном пункте выдачи
    $stmt = $pdo->prepare("SELECT address, city FROM pickup_points WHERE id = ?");
    $stmt->execute([$pickup_point_id]);
    $pickup_point = $stmt->fetch(PDO::FETCH_ASSOC);

    // Создаем заказ в базе данных
    $order_query = $pdo->prepare("INSERT INTO orders (user_id, status, pickup_point_id, address, city) VALUES (?, ?, ?, ?, ?)");
    $order_query->execute([$user['id'], 'Pending', $pickup_point_id, $pickup_point['address'], $pickup_point['city']]);
    $order_id = $pdo->lastInsertId();

    // Добавляем товары в заказ, проверяя наличие записи
    foreach ($_SESSION['cart'] as $product) {
        // Проверяем, существует ли уже такая запись в таблице order_products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_products WHERE order_id = ? AND product_id = ?");
        $stmt->execute([$order_id, $product['id']]);
        $exists = $stmt->fetchColumn();

        // Если записи нет, добавляем товар в таблицу
        if ($exists == 0) {
            $order_product_query = $pdo->prepare("INSERT INTO order_products (order_id, product_id) VALUES (?, ?)");
            $order_product_query->execute([$order_id, $product['id']]);
        }
    }

    // Очищаем корзину
    $_SESSION['cart'] = [];
    header('Location: my_orders.php'); // Перенаправляем на страницу с заказами
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Корзина</h1>
        <nav>
            <a href="index.php">Каталог</a>
            <a href="my_orders.php">Мои заказы</a>
            <a href="logout.php">Выход</a>
        </nav>
    </header>
    <main>
        <h2>Товары в корзине</h2>
        <section class="cart-list">
            <?php foreach ($_SESSION['cart'] as $product): ?>
                <div class="cart-item">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>Цена: <?= $product['price'] ?> руб.</p>
                </div>
            <?php endforeach; ?>
        </section>

        <h3>Выберите пункт выдачи</h3>
        <form method="post" action="cart.php">
            <select name="pickup_point_id" required>
                <?php foreach ($pickup_points as $point): ?>
                    <option value="<?= $point['id'] ?>"><?= htmlspecialchars($point['name']) ?> (<?= htmlspecialchars($point['address']) ?>, <?= htmlspecialchars($point['city']) ?>)</option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Оформить заказ</button>
        </form>
    </main>
</body>
</html>
