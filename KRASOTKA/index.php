<?php
include 'db.php';
session_start();

// Инициализация корзины, если она еще не существует
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Получение товаров
$query = $pdo->query("SELECT * FROM products");
$products = $query->fetchAll(PDO::FETCH_ASSOC);

// Обработка добавления товара в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $product_query = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $product_query->execute([$product_id]);
    $product = $product_query->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Добавляем товар в корзину
        $_SESSION['cart'][] = $product;
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Красотка - Главная</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Магазин Красотка</h1>
        <nav>
            <a href="index.php">Каталог</a>
            <a href="cart.php">Корзина (<?= count($_SESSION['cart']) ?>)</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="my_orders.php">Мои заказы</a>
                <a href="logout.php">Выход (<?= htmlspecialchars($_SESSION['user']['username']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Войти</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Каталог товаров</h2>
        <section class="product-list">
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>Цена: <?= $product['price'] ?> руб.</p>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                    <form method="post" action="index.php">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit">Добавить в корзину</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
