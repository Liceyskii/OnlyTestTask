<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../profile");
    exit;
}

require_once '../funcs.php';

// Проверка запроса
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
    if (empty($password) || strlen($password) < 6) {
        $errors['password'] = "Пароль должен быть не менее 6 символов.";
    }

    // Валидация подтверждения пароля
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Пароли не совпадают.";
    }

    // Проверка данных на уникальность
    if (checkUserExists($_POST)) {
        $errors['dublicate'] = "Пользователь с таким email/номером телефона уже зарегистрирован.";
    }

    // Регистрация пользователя
    if (empty($errors) && registerUser($_POST)) {
        header("Location: ../login");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
    <title>Регистрация</title>
</head>
<body>
    <h1>Регистрация</h1>

    <?php if (isset($errors)): ?>
        <?php foreach ($errors as $error) { ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php } ?>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Имя:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="phone">Телефон:</label><br>
        <input type="tel" id="phone" name="phone" required><br><br>

        <label for="password">Пароль:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Повторите пароль:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <input type="submit" value="Зарегистрироваться">
        
    </form>

    <br>
    <a href="/">На главную</a>
</body>
</html>