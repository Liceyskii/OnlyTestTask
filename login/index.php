<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../profile");
    exit;
}

require_once '../funcs.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $token = $_POST['smart-token'];

    if (authUser($_POST) && check_captcha($token)) {
        header("Location: ../profile");
        exit;
    } else if (!check_captcha($token)) {
        $error = "Вы не прошли проверку \"Я не робот\".";
    } else {
        $error = 'Неверный email, номер телефона или пароль.';
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Авторизация</title>
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
</head>
<body>
    <h1>Авторизация</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="emailOrPhone">Email/Телефон:</label><br>
        <input type="text" id="emailOrPhone" name="emailOrPhone" required><br><br>

        <label for="password">Пароль:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <!-- Капча -->
        <div 
            style="height: 100px"
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="ysc1_vzIjnFvSuNYWJ38EQD6Qg5eyXflXYBlOdgXx1PwA29749f84"
        ></div><br>

        <input type="submit" value="Войти">
    </form>
    <br>
    <a href="/">На главную</a>
</body>
</html>