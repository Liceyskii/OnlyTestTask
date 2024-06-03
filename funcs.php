<?php

// Константа для капчи
define('SMARTCAPTCHA_SERVER_KEY', '');

// Подключение к базе данных
function dbConnect() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "testovoe";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
        return false;
    }
}

// Проверка данных на уникальность
function checkUserExists($request, $userId = null) {
    $conn = dbConnect();
    $email = $request['email'];
    $phone = $request['phone'];

    if ($userId) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE (email = :email OR phone = :phone) AND id != :userId");
        $stmt->bindParam(':userId', $userId);
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email OR phone = :phone");
    }

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

// Регистрация нового пользователя
function registerUser($request) {
    $name = $request['name'];
    $email = $request['email'];
    $phone = $request['phone'];
    $password = password_hash($request['password'], PASSWORD_DEFAULT); 

    $conn = dbConnect();
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, date_created) VALUES (:name, :email, :phone, :password, NOW())");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $password);
    $result = $stmt->execute();

    if ($result) {
        return true;
    } else {
        return false;
    }

}

// Авторизация
function authUser($request) {
    $conn = dbConnect();
    $emailOrPhone = $request['emailOrPhone'];
    $password = $request['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :emailOrPhone OR phone = :emailOrPhone");
    $stmt->bindParam(':emailOrPhone', $emailOrPhone);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Получение всех данных пользователя по id
function getUserById($id) {
    $conn = dbConnect();

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user;
}

// Обновление информации о пользователе
function updateUser($id, $name, $email, $phone, $password) {
    $conn = dbConnect();

    if ($password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, password = :password WHERE id = :userId");
        $stmt->bindParam(':password', $hashedPassword);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :userId");
    }
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':userId', $id);
    return $stmt->execute();
}

// Yandex SmartCaptcha
function check_captcha($token) {
    $ch = curl_init();
    $args = http_build_query([
        "secret" => SMARTCAPTCHA_SERVER_KEY,
        "token" => $token,
        "ip" => $_SERVER['REMOTE_ADDR'], // Нужно передать IP пользователя.
                                         // Как правильно получить IP зависит от вашего прокси.
    ]);
    curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);

    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 200) {
        echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
        return true;
    }
    $resp = json_decode($server_output);
    return $resp->status === "ok";
}

?>