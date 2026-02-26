<?php

header('Access-Control-Allow-Origin: *');

$username = $data['username'] ?? null;
$email = $data['email'] ?? null;
$bld_id = $data['bld_id'] ?? '';
$bld_name = $data['bld_name'] ?? '';
$alertStopCheck = $data['alertStopCheck'] ?? false;

$error_msg = '';

if (!$username || !$email) {
    $error_msg = '名前とメールアドレスが必要です。';
}

if (!$alertStopCheck && ($bld_id === '' || $bld_name === '')) {
    $error_msg = '建物IDと建物の名前が必要です。';
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

    if ($alertStopCheck) {
        $stmt = $pdo->prepare(
            'DELETE FROM alerts
            WHERE email = :email'
        );
        
        $stmt->execute([
            ':email' => $email
        ]);
    }

    if ($bld_id !== '' && $bld_name !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO alerts (name, email, bld_id, bld_name)
            VALUES (:name, :email, :bld_id, :bld_name)'
        );
        
        $stmt->execute([
            ':name'  => $username,
            ':email'     => $email,
            ':bld_id' => $bld_id,
            ':bld_name' => $bld_name
        ]);
    }
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo $error_msg;
}

