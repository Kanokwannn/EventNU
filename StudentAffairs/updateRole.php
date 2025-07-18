<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        echo "อีเมลไม่ถูกต้อง";
        exit();
    }

    $conn->begin_transaction(); // เริ่ม Transaction

    try {
        // ตรวจสอบว่าผู้ใช้อยู่ใน audience หรือไม่
        $stmt = $conn->prepare("SELECT * FROM audience WHERE Audience_email = ?");
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            throw new Exception("ไม่พบข้อมูลผู้ใช้");
        }

        // ตรวจสอบว่าอีเมลมีอยู่แล้วใน EventOrganizer หรือไม่
        $stmt = $conn->prepare("SELECT 1 FROM EventOrganizer WHERE EventOrganizer_email = ?");
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("อีเมลนี้มีอยู่แล้วในระบบ EventOrganizer");
        }
        $stmt->close();

        // ตรวจสอบรหัสผ่าน
        $password = $user['Audience_password'] ? $user['Audience_password'] : 'default_password'; // ใส่รหัสผ่านเริ่มต้นถ้าเป็น NULL

        // ย้ายข้อมูลไปยัง EventOrganizer
        $stmt = $conn->prepare("INSERT INTO EventOrganizer (
            EventOrganizer_email, 
            EventOrganizer_FirstName, 
            EventOrganizer_LastName, 
            FacultyID, 
            MajorID, 
            EventOrganizer_Password, 
            EventOrganizer_Phone, 
            GenderID, 
            StudentID, 
            EventOrganizer_Role
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'EventOrganizer')");

        $stmt->bind_param(
            "sssiiisii",
            $user['Audience_email'],
            $user['Audience_FirstName'],
            $user['Audience_LastName'],
            $user['FacultyID'],
            $user['MajorID'],
            $password, // ใช้รหัสผ่านที่ตรวจสอบแล้ว
            $user['Audience_Phone'],
            $user['GenderID'],
            $user['StudentID']
        );

        if (!$stmt->execute()) {
            throw new Exception("เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit(); // บันทึกการเปลี่ยนแปลง
        echo "เปลี่ยนโรลสำเร็จ!";
    } catch (Exception $e) {
        $conn->rollback(); // ยกเลิกการเปลี่ยนแปลงหากมีข้อผิดพลาด
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }

    $conn->close();
}
?>
