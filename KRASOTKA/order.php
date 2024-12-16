<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_point = $_POST['pickup_point'];
    $user_id = $_SESSION['user']['id'];

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, pickup_point_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $pickup_point]);
    $success = "Заказ успешно оформлен!";
}

// Получение пунктов выдачи
$points = $pdo->query("SELECT * FROM pickup_points")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
</head>
<body>
    <h1>Оформление заказа</h1>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="pickup_point">Выберите пункт выдачи:</label>
        <select name="pickup_point" id="pickup_point" required>
            <?php foreach ($points as $point): ?>
                <option value="<?= $point['id'] ?>"><?= htmlspecialchars($point['address']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Оформить</button>
    </form>
</body>
</html>
