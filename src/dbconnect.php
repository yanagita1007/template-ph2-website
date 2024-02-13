<?php

$host = 'db'; // Docker ComposeでのMySQLサービス名
$dbname = 'posse'; // データベース名
$user = 'root'; // データベースユーザー
$password = 'root'; // データベースパスワード
$port = 3306; // MySQLポート（Docker Composeでポートマッピングされたポート）

try {
    $dbh = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    // echo ("Connection to DB");
    // データベースに対するクエリや操作をここに追加

    // $stmt = $pdo->query('SELECT * FROM questions');
    // while ($row = $stmt->fetch()) {
    //     print_r($row);
    // }
    // $stmt = $pdo->query('SELECT * FROM choices');
    // while ($row = $stmt->fetch()) {
    //     print_r($row);
    // }
} catch (PDOException $e) {
    exit($e->getMessage());
}


// <?php
// $dsn = 'mysql:host=db;dbname=posse;charset=utf8';
// $user = 'root';
// $password = 'root';

// try {
//     $dbh = new PDO($dsn, $user, $password);
//     //echo 'Connection success!';
// } catch (PDOException $e) {
//     //echo 'Connection failed: ' . $e->getMessage();
// }
