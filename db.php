<?php
$host = 'mysql-fabio-hkj.alwaysdata.net';
$dbname = 'fabio-hkj_auto-ecole';
$username = 'fabio-hkj';
$password = 'henikaja2005-F';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}