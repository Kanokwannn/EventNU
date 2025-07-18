<?php
session_start();
include '../db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['email'])) {
    die("Unauthorized access");
}

$email = $_SESSION['email'];  // อีเมลของผู้ใช้ที่ล็อกอิน
$oldPassword = $_POST['oldPassword'] ?? '';  // รหัสผ่านเก่าจากฟอร์ม
$newPassword = $_POST['newPassword'] ?? '';  // รหัสผ่านใหม่จากฟอร์ม

// ตรวจสอบว่ามีค่าครบหรือไม่
if (empty($oldPassword) || empty($newPassword)) {
    die("กรุณากรอกข้อมูลให้ครบ");
}

// ดึงรหัสผ่านเก่าจากฐานข้อมูล
$stmt = $conn->prepare("SELECT Audience_password FROM audience WHERE Audience_email = ?");
if (!$stmt) {
    die("เกิดข้อผิดพลาดในการเตรียมคำสั่ง: {$conn->error}");
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
if (!$row) {
    die("ไม่พบผู้ใช้ในระบบ");
}
// ตรวจสอบว่ารหัสผ่านเก่าถูกต้องหรือไม่
if (!password_verify($oldPassword, $row['Audience_password'])) {
    die("รหัสผ่านเก่าไม่ถูกต้อง");
}

// เข้ารหัสรหัสผ่านใหม่
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// อัปเดตรหัสผ่านใหม่ในฐานข้อมูล
$stmt = $conn->prepare("UPDATE Audience SET Audience_password = ? WHERE Audience_email = ?");
if ($stmt) {
    $stmt->bind_param("ss", $hashedPassword, $email);
    if ($stmt->execute()) {
        echo "Password updated successfully";
    } else {
        echo "เกิดข้อผิดพลาด: {$stmt->error}";
    }
    $stmt->close();
} else {
    echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง: {$conn->error}";
}
$conn->close();
