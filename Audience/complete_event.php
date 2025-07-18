<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

ini_set('display_errors', 1);
error_reporting(E_ALL);


// ตรวจสอบว่า user เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่ได้ล็อกอินให้ไปหน้า login
    exit();
}
$event_name = $_POST['eventName'];
$event_date = $_POST['dateevent'];
$event_time = $_POST['timeevent'];
$event_location = $_POST['locationevent'];
$event_price = $_POST['prictticketevent'];
$event_map = $_POST['mapevent'];
$event_budget = $_POST['budgetTotal'];
$event_saledate = !empty($_POST['datesale']) ? $_POST['datesale'] : NULL;
$event_saletime = !empty($_POST['timesale']) ? $_POST['timesale'] : NULL;
$event_type = ($_POST['registerType'] == "yes") ? "yes" : "no";
$event_detail = $_POST['eventDetails'];
$email = $_SESSION['email']; // ใช้ email ของผู้ใช้งานจาก session

// ตรวจสอบว่ามีการอัปโหลดไฟล์หรือไม่
if (isset($_FILES["eventImage"]) && $_FILES["eventImage"]["error"] == UPLOAD_ERR_OK) {
    $file_tmp = $_FILES["eventImage"]["tmp_name"];
    $file_name = $_FILES["eventImage"]["name"];
    $file_type = $_FILES["eventImage"]["type"];

    // ตรวจสอบว่าเป็นไฟล์รูปภาพจริง ๆ
    $check = getimagesize($file_tmp);
    if ($check === false) {
        die("ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ");
    }

    // จำกัดประเภทไฟล์เฉพาะ JPG, PNG, JPEG
    $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $allowed_types)) {
        die("อัปโหลดได้เฉพาะไฟล์ JPG, JPEG, PNG เท่านั้น");
    }

    // ย้ายไฟล์ไปยังโฟลเดอร์
    $target_dir = "../EventPicture/";
    $target_file = $target_dir . basename($file_name);

    // ตรวจสอบชื่อไฟล์ซ้ำ
    if (file_exists($target_file)) {
        $target_file = $target_dir . uniqid() . '.' . $imageFileType;
    }

    // ย้ายไฟล์
    if (!move_uploaded_file($file_tmp, $target_file)) {
        die("อัปโหลดไฟล์ล้มเหลว");
    }

    // บันทึก path ของไฟล์รูปในฐานข้อมูล
    $image_path = $target_file;
    echo "ไฟล์ถูกอัปโหลดเรียบร้อยแล้ว: " . $image_path;
} else {
    die("ไม่พบไฟล์ที่อัปโหลด หรือเกิดข้อผิดพลาดในการอัปโหลด");
}

// สร้างคำสั่ง SQL
// สร้างคำสั่ง SQL
$sql = "INSERT INTO request_event (Audience_email, request_event_status, request_event_name, category_id, request_event_detail, request_event_date, request_event_saletime, request_event_saledate, request_event_time, request_event_location, request_event_map, request_event_price, request_event_type, request_event_budget, request_event_picture) 
        VALUES (?, 'committee', ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);

// ตรวจสอบว่าเตรียมคำสั่งสำเร็จหรือไม่
if (!$stmt) {
    die("Error in preparing statement: " . $conn->error);  // แสดงข้อความผิดพลาด
}

// ใช้ประเภทข้อมูลที่ตรงกัน
// s = string, d = double, i = integer
$stmt->bind_param("sssssssssdsds", $email, $event_name, $event_detail, $event_date, $event_saletime, $event_saledate, $event_time, $event_location, $event_map, $event_price, $event_type, $event_budget, $image_path);

// ตรวจสอบการ execute statement
if ($stmt->execute()) {
    echo "<script>alert('Request successful!'); window.location.href='followrequest.php';</script>";
} else {
    echo "บันทึกข้อมูลล้มเหลว: " . $stmt->error;
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
