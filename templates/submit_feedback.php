<?php

header('Access-Control-Allow-Origin: *');

$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$feedback = $data['feedback'] ?? '';

$error_msg = '';

if (!$username || !$email) {
    $error_msg = '名前とメールアドレスが必要です。';
}

if (($username === '' || $email === '' || $feedback === '')) {
    $error_msg = '全てのフィールドを記入してください。';
}

if ($error_msg === '') {
    $host = '127.0.0.1';
    $db   = $config['db']['database'];
    $user = $config['db']['username'];
    $pass = $config['db']['password'];
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $stmt = $pdo->prepare(
        'INSERT INTO feedback (username, email, feedback)
        VALUES (:username, :email, :feedback)'
    );
    
    $stmt->execute([
        ':username'  => $username,
        ':email'     => $email,
        ':feedback' => $feedback
    ]);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo $error_msg;
}

