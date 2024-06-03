<?php
session_start();

require_once '../funcs.php';

// Проверка авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: ../");
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Валидация имени
    if (empty($name) || strlen($name) < 3 || strlen($name) > 255) {
        $errors['name'] = "Имя должно быть от 3 до 255 символов.";
    }

    // Валидация email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Введите корректный email.";
    }

    // Валидация телефона
    if (empty($phone) || !preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors['phone'] = "Введите корректный номер телефона.";
    }

    // Валидация пароля
    if ($password && strlen($password) < 6) {
        $errors['password'] = "Пароль должен быть не менее 6 символов.";
    }

    // Валидация подтверждения пароля
    if ($password && $password !== $confirm_password) {
        $errors['confirm_password'] = "Пароли не совпадают.";
    }

    if (checkUserExists($_POST, $userId)) {
        $errors['dublicate'] = "Пользователь с таким email или телефоном уже существует.";
    }

    // Обновление данных пользователя
    if (empty($errors)) {
        updateUser($userId, $name, $email, $phone, $password);
        echo 'Данные успешно обновлены!!';
        // Обновление значений в форме
        $user = getUserById($userId);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
</head>
<body>
    <h1>Профиль</h1>

    <?php if (isset($errors)): ?>
        <?php foreach ($errors as $error) { ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php } ?>
    <?php endif; ?>

    <p>Привет, <?php echo $user['name']; ?></p>

    <form method="POST">
        <label for="name">Имя:</label><br>
        <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required><br><br>

        <label for="phone">Телефон:</label><br>
        <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required><br><br>

        <label for="password">Новый пароль:</label><br>
        <input type="password" id="password" name="password"><br><br>

        <label for="confirm_password">Повторите пароль:</label><br>
        <input type="password" id="confirm_password" name="confirm_password"><br><br>

        <input type="submit" value="Сохранить изменения">
    </form>

    <br>

    <a href="logout.php">Выйти</a>
</body>
</html>