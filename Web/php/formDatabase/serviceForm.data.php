<?php

header('Content-Type: application/json');
require_once 'Web/php/connection.php';
session_start();

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $connect->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $user['acc_type'] !== 'company') {
        throw new Exception('Only company accounts can create services');
    }

    $required_fields = ['companyName', 'serviceTitle', 'address', 'contact', 'panNumber', 'serviceDescription'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Thumbnail image is required');
    }

    if (!isset($_FILES['officePhotos']) || count($_FILES['officePhotos']['name']) !== 4) {
        throw new Exception('Exactly 4 office photos are required');
    }

    if (empty($_POST['flightTypes']) || !is_array($_POST['flightTypes'])) {
        throw new Exception('Flight types must be an array');
    }

    $flight_types = $_POST['flightTypes'];

    $upload_base = 'Assets/uploads/';
    $thumbnail_dir = $upload_base . "thumbnails/";
    $photos_dir = $upload_base . 'company_images/';

    if (!file_exists($thumbnail_dir)) mkdir($thumbnail_dir, 0755, true);
    if (!file_exists($photos_dir)) mkdir($photos_dir, 0755, true);
    
    if (!file_exists($upload_base)) {
        if (!mkdir($upload_base, 0755, true)) {
            throw new Exception('Failed to create base upload directory');
        }
    }

    if (!file_exists($thumbnail_dir)) {
        if (!mkdir($thumbnail_dir, 0755, true)) {
            throw new Exception('Failed to create thumbnail directory');
        }
    }
    if (!file_exists($photos_dir)) {
        if (!mkdir($photos_dir, 0755, true)) {
            throw new Exception('Failed to create photos directory');
        }
    }
    $connect->autocommit(false);

    $thumbnail_name = uniqid('thumb_') . '.jpg';
    $thumbnail_path = $thumbnail_dir . $thumbnail_name;

    if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
        throw new Exception('Failed to move thumbnail file');
    }

    $stmt = $connect->prepare("
        INSERT INTO company_services 
        (user_id, company_name, service_title, address, contact, pan_number, service_description, thumbnail_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "isssssss",
        $user_id,
        $_POST['companyName'],
        $_POST['serviceTitle'],
        $_POST['address'],
        $_POST['contact'],
        $_POST['panNumber'],
        $_POST['serviceDescription'],
        $thumbnail_path
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to insert service: ' . $stmt->error);
    }

    $service_id = $connect->insert_id;

    $stmt = $connect->prepare("INSERT INTO service_flight_types (service_id, flight_type_name, price) VALUES (?, ?, ?)");

    foreach ($flight_types as $type) {
        $name = $type['name'] ?? null;
        $price = $type['price'] ?? null;

        if (!$name) {
            throw new Exception('Flight type name missing');
        }

        if ($price === null || $price === '') {
            throw new Exception('Flight type price missing or invalid');
        }

        $stmt->bind_param("iss", $service_id, $name, $price); // "iss" because price is now a string

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert flight type: " . $stmt->error);
        }
    }

    $stmt = $connect->prepare("INSERT INTO service_office_photos (service_id, photo_path, photo_order) VALUES (?, ?, ?)");
    for ($i = 0; $i < 4; $i++) {
        if ($_FILES['officePhotos']['error'][$i] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading office photo " . ($i + 1));
        }

        $photo_name = uniqid("photo_{$i}_") . '.jpg';
        $photo_path = $photos_dir . $photo_name;

        if (!move_uploaded_file($_FILES['officePhotos']['tmp_name'][$i], $photo_path)) {
            throw new Exception("Failed to save office photo " . ($i + 1));
        }

        $photo_order = $i + 1;
        $stmt->bind_param("ssi", $service_id, $photo_path, $photo_order);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert office photo: ' . $stmt->error);
        }
    }

    $connect->commit();
    $connect->autocommit(true);

    echo json_encode([
        'success' => true,
        'message' => 'Service created successfully!',
        'service_id' => $service_id
    ]);
} catch (Exception $e) {
    $connect->rollback();
    $connect->autocommit(true);

    if (isset($thumbnail_path) && file_exists($thumbnail_path)) {
        unlink($thumbnail_path);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>