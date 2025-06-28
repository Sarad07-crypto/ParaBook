<?php

header('Content-Type: application/json');
require_once 'Web/php/connection.php';
session_start();

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $user_id = $_SESSION['user_id'];

    // Only companies can post
    $stmt = $connect->prepare("SELECT acc_type FROM users_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user['acc_type'] !== 'company') {
        throw new Exception('Only company accounts can create or update services');
    }

    // Validate fields
    $required_fields = ['companyName', 'serviceTitle', 'address', 'contact', 'panNumber', 'serviceDescription'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    if (empty($_POST['flightTypes']) || !is_array($_POST['flightTypes'])) {
        throw new Exception('Flight types must be an array');
    }

    $flight_types = $_POST['flightTypes'];
    $service_id = isset($_POST['service_id']) && !empty($_POST['service_id']) ? intval($_POST['service_id']) : null;
    $is_update = $service_id !== null;

    // Start transaction
    $connect->autocommit(false);

    // Handle thumbnail
    $thumbnail_path = null;
    $new_thumbnail_uploaded = false;

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        // New thumbnail uploaded
        $thumbnail_dir = 'Assets/uploads/thumbnails/';
        if (!file_exists($thumbnail_dir)) mkdir($thumbnail_dir, 0755, true);

        $thumbnail_name = uniqid('thumb_') . '.jpg';
        $thumbnail_path = $thumbnail_dir . $thumbnail_name;

        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path)) {
            throw new Exception('Failed to save thumbnail');
        }
        $new_thumbnail_uploaded = true;
    } elseif ($is_update) {
        // For updates, get existing thumbnail if no new one uploaded
        $stmt = $connect->prepare("SELECT thumbnail_path FROM company_services WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $service_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            $thumbnail_path = $result['thumbnail_path'];
        }
    }

    if (!$thumbnail_path) {
        throw new Exception("Thumbnail is required");
    }

    // Insert or Update service
    if ($is_update) {
        // Verify ownership
        $stmt = $connect->prepare("SELECT id, thumbnail_path FROM company_services WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $service_id, $user_id);
        $stmt->execute();
        $existing_service = $stmt->get_result()->fetch_assoc();
        
        if (!$existing_service) {
            throw new Exception('Service not found or access denied');
        }

        // Update existing service
        $stmt = $connect->prepare("UPDATE company_services SET 
            company_name = ?, service_title = ?, address = ?, contact = ?, 
            pan_number = ?, service_description = ?, thumbnail_path = ?
            WHERE id = ? AND user_id = ?");
        $stmt->bind_param(
            "sssssssii",
            $_POST['companyName'],
            $_POST['serviceTitle'],
            $_POST['address'],
            $_POST['contact'],
            $_POST['panNumber'],
            $_POST['serviceDescription'],
            $thumbnail_path,
            $service_id,
            $user_id
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to update service: ' . $stmt->error);
        }

        // Delete old thumbnail if new one was uploaded
        if ($new_thumbnail_uploaded && $existing_service['thumbnail_path'] && 
            $existing_service['thumbnail_path'] !== $thumbnail_path && 
            file_exists($existing_service['thumbnail_path'])) {
            unlink($existing_service['thumbnail_path']);
        }

    } else {
        // Insert new service
        $stmt = $connect->prepare("INSERT INTO company_services 
            (user_id, company_name, service_title, address, contact, pan_number, service_description, thumbnail_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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
            throw new Exception('Failed to insert new service: ' . $stmt->error);
        }

        $service_id = $connect->insert_id;
    }

    // Handle flight types
    if ($is_update) {
        // Delete existing flight types for update
        $stmt = $connect->prepare("DELETE FROM service_flight_types WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete existing flight types: ' . $stmt->error);
        }
    }

    // Insert flight types
    $stmt = $connect->prepare("INSERT INTO service_flight_types (service_id, flight_type_name, price) VALUES (?, ?, ?)");
    foreach ($flight_types as $type) {
        $name = $type['name'] ?? null;
        $price = $type['price'] ?? null;

        if (!$name || !$price) {
            throw new Exception('Invalid flight type data');
        }

        $stmt->bind_param("iss", $service_id, $name, $price);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert flight type: ' . $stmt->error);
        }
    }

    // Handle office photos
    $new_photos_uploaded = isset($_FILES['officePhotos']) && 
                          is_array($_FILES['officePhotos']['name']) && 
                          count($_FILES['officePhotos']['name']) > 0 &&
                          $_FILES['officePhotos']['name'][0] !== '';

    if ($new_photos_uploaded) {
        // Get existing photos for deletion
        $existing_photos = [];
        if ($is_update) {
            $stmt = $connect->prepare("SELECT photo_path FROM service_office_photos WHERE service_id = ?");
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $existing_photos[] = $row['photo_path'];
            }
        }

        // Delete existing photo records
        $stmt = $connect->prepare("DELETE FROM service_office_photos WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete existing office photos: ' . $stmt->error);
        }

        // Create upload directory
        $photo_dir = 'Assets/uploads/company_images/';
        if (!file_exists($photo_dir)) mkdir($photo_dir, 0755, true);

        // Insert new photos
        $stmt = $connect->prepare("INSERT INTO service_office_photos (service_id, photo_path, photo_order) VALUES (?, ?, ?)");

        for ($i = 0; $i < count($_FILES['officePhotos']['name']); $i++) {
            if ($_FILES['officePhotos']['error'][$i] !== UPLOAD_ERR_OK) {
                throw new Exception("Error uploading office photo " . ($i + 1));
            }

            $photo_name = uniqid("photo_{$service_id}_{$i}_") . '.jpg';
            $photo_path = $photo_dir . $photo_name;

            if (!move_uploaded_file($_FILES['officePhotos']['tmp_name'][$i], $photo_path)) {
                throw new Exception("Failed to save office photo " . ($i + 1));
            }

            $photo_order = $i + 1;
            $stmt->bind_param("isi", $service_id, $photo_path, $photo_order);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert office photo: " . $stmt->error);
            }
        }

        // Delete old photo files after successful upload
        foreach ($existing_photos as $old_photo) {
            if (file_exists($old_photo)) {
                unlink($old_photo);
            }
        }
    } elseif (!$is_update) {
        // New service must have office photos
        throw new Exception("Office photos are required for new services");
    }

    // Handle existing office photos to keep (for updates when no new photos uploaded)
    if ($is_update && !$new_photos_uploaded && isset($_POST['existingOfficePhotos']) && is_array($_POST['existingOfficePhotos'])) {
        // Get current photos in database
        $stmt = $connect->prepare("SELECT photo_path FROM service_office_photos WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $current_photos = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $current_photos[] = $row['photo_path'];
        }

        $existing_photos_to_keep = $_POST['existingOfficePhotos'];
        
        // Find photos to delete (in DB but not in keep list)
        $photos_to_delete = array_diff($current_photos, $existing_photos_to_keep);
        
        if (!empty($photos_to_delete)) {
            // Delete unwanted photos from database
            $placeholders = str_repeat('?,', count($photos_to_delete) - 1) . '?';
            $stmt = $connect->prepare("DELETE FROM service_office_photos WHERE service_id = ? AND photo_path IN ($placeholders)");
            $params = array_merge([$service_id], $photos_to_delete);
            $types = 'i' . str_repeat('s', count($photos_to_delete));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            // Delete photo files
            foreach ($photos_to_delete as $photo_path) {
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }
        }
    }

    // Commit transaction
    $connect->commit();
    $connect->autocommit(true);

    echo json_encode([
        'success' => true,
        'message' => $is_update ? 'Service updated successfully' : 'Service created successfully',
        'service_id' => $service_id
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $connect->rollback();
    $connect->autocommit(true);

    // Clean up uploaded files on error
    if (isset($thumbnail_path) && $new_thumbnail_uploaded && file_exists($thumbnail_path)) {
        unlink($thumbnail_path);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}