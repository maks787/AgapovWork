<?php
// index.php
require 'config.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Получение конкретного объявления
            $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $ad = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($ad ?: ["message" => "Объявление не найдено"]);
        } else {
            // Получение списка объявлений
            $stmt = $pdo->query("SELECT title, price, SUBSTRING_INDEX(photos, ',', 1) AS main_photo FROM ads LIMIT 10");
            $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($ads);
        }
        break;

    case 'POST':
        // Создание нового объявления
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("INSERT INTO ads (title, description, photos, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['description'], implode(",", $data['photos']), $data['price']]);
        echo json_encode(["id" => $pdo->lastInsertId(), "message" => "Объявление создано"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Метод не поддерживается"]);
        break;
}

