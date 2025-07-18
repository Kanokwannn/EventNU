<?php
session_start();
$email = $_SESSION['email']; // รับค่า email จาก session
include "../db.php"; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $favorite_id = $_POST['favorite_id'];
    $action = $_POST['action'];
    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email is missing!']);
        exit();
    }
    if (empty($favorite_id) || empty($action)) {
        echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน!']);
        exit();
    }

    if ($action === "add") {
        // เพิ่มข้อมูลลงในตาราง notification
        $sql = "INSERT INTO notification (Audience_email, favorite_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $favorite_id);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => $stmt->error]); // เพิ่มการแสดงข้อผิดพลาดที่เกิดขึ้น
            exit();
        } else {
            echo json_encode(['success' => true]);
        }
    } elseif ($action === "remove") {
        // ลบข้อมูลออกจากตาราง notification
        $sql = "DELETE FROM notification WHERE Audience_email = ? AND favorite_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $favorite_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ไม่สามารถลบการแจ้งเตือนได้!']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'การกระทำไม่ถูกต้อง!']);
    }
    $conn->close();
}
