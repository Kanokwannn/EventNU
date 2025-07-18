<?php
session_start();
include '../db.php';

if (!isset($_SESSION['email'])) {
    die("Unauthorized access");
}
$Audience_FirstName = $_POST['firstName'] ?? null;
$Audience_LastName = $_POST['lastName'] ?? null;
$StudentID = $_POST['staffId'] ?? null;
$gender = $_POST['gender'] ?? null;
$Audience_phone = $_POST['phoneNumber'] ?? null;

$update_fields = [];
$params = [];
$types = '';

if ($Audience_FirstName !== null) {
    $update_fields[] = 'Audience_FirstName = ?';
    $params[] = $Audience_FirstName;
    $types .= 's';
}
if ($Audience_LastName !== null) {
    $update_fields[] = 'Audience_LastName = ?';
    $params[] = $Audience_LastName;
    $types .= 's';
}
if ($StudentID !== null) {
    $update_fields[] = 'StudentID= ?';
    $params[] = $StudentID;
    $types .= 's';
}
if ($gender !== null) {
    $update_fields[] = 'genderID = ?';
    $params[] = $gender;
    $types .= 'i';
}
if ($Audience_phone !== null) {
    $update_fields[] = 'Audience_phone = ?';
    $params[] = $Audience_phone;
    $types .= 's';
}

if (empty($update_fields)) {
    die("No fields to update.");
}

$params[] = $_SESSION['email'];
$types .= 's';

$sql = "UPDATE audience SET " . implode(', ', $update_fields) . " WHERE Audience_email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL Error: {$conn->error}");
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo "Data saved successfully.";
} else {
    echo "Error: {$stmt->error}";
}

$stmt->close();
$conn->close();
