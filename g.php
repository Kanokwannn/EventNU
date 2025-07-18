<?php
$password = 'eiei11';  // รหัสผ่านที่คุณต้องการแปลง
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);  // แปลงเป็น hash
echo $hashedPassword;  // แสดง hash ที่ได้
?>
