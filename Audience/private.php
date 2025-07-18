<?php
session_start();
// Database connection file
include '../db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'] ?? '';

if (!empty($email)) {
    $sql = "
        SELECT sa.Audience_FirstName, sa.Audience_email, sa.StudentID, 
               sa.Audience_phone, g.genderType, sa.Audience_LastName, sa.genderID
        FROM Audience sa
        LEFT JOIN gender g ON sa.genderID = g.genderID
        WHERE sa.Audience_email = ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error); // แสดงข้อผิดพลาดของ SQL
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "No user found with email: " . htmlspecialchars($email);
    }
} else {
    echo "Email is empty!";
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- font icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="../assets/vendors/themify-icons/css/themify-icons.css">
    <!-- meyao -->
    <link rel="stylesheet" href="../assets/css/meyawo.css">
    <!-- font-awesome icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Material icons-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>EventNU</title>

</head>

<body class="navbar-body">
    <nav class="navbar-container">
        <div class="navbar-content">
            <div class="navbar-logo">
                <a href="home.php">EVENT NU</a>
            </div>
            <div class="navbar-popup-navbar" id="popupNavbar">
                <div class="navbar-search-container-popup">
                    <input type="text" class="form-control" placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน">
                </div>
                <button class="navbar-close-popup" id="closePopupNavbar"><i class="bi bi-x"></i></button>
            </div>

            <div class="navbar-popup-overlay" id="popupOverlay"></div>

            <div class="navbar-popup" id="searchPopup">

                <div class="navbar-popup-header-recommend">
                    <h3>แนะนำ</h3>
                </div>
                <div class="navbar-popup-content">
                    <div class="navbar-event-list">
                        <div class="navbar-event-card">
                            <img src="../assets/imgs/bf.png" alt="Music Festival">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 08 มี.ค. 2025</p>
                                <h4>NU Book Fair</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> THE PIRATES PARK HATYAI
                                </p>
                                <button class="navbar-event-button">ดูรายละเอียด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/bf.png" alt="Music Festival">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 08 มี.ค. 2025</p>
                                <h4>NU Book Fair</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> THE PIRATES PARK HATYAI
                                </p>
                                <button class="navbar-event-button">ดูรายละเอียด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/artyoung.jpg" alt="Northern Fest">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 01 ก.พ. 2025</p>
                                <h4>ศิลป์เสมอ</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ขนส่ง 3</p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/firststage.jpg" alt="Northern Fest">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 01 ก.พ. 2025</p>
                                <h4>First Stage</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ขนส่ง 3</p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/artyoung.jpg" alt="Northern Fest">
                            <div class="navbar-navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 01 ก.พ. 2025</p>
                                <h4>ศิลป์เสมอ</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ขนส่ง 3</p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="navbar-search-container">
                <input type="text" class="form-control navbar-search-bar"
                    placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน" id="searchInput" readonly>
            </div>


            <ul class="navbar-nav navbar-menu-items">
                <li class="navbar-nav-item">
                    <a class="nav-link" href="home.php" data-target="home">หน้าแรก</a>
                </li>
            </ul>
            <div class="nav-right">
                <button class="notification-button" id="notificationButton">
                    <i class="fi fi-rr-bell"></i>
                </button>
                <div class="notification-panel" id="notificationPanel">
                    <h5>การแจ้งเตือน</h5>
                    <hr>
                    <div class="notification-empty">
                        <i class="bi bi-envelope" style="font-size: 50px; color: #666;"></i>
                        <p>ไม่มีการแจ้งเตือน</p>
                        <small>ขณะนี้ยังไม่มีการแจ้งเตือนถึงคุณ</small>
                    </div>
                </div>
                <button class="user-button" id="userButton">
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                    <span><?php
                    if (isset($user)) {
                        echo htmlspecialchars($user['Audience_FirstName']);
                    } else {
                        echo 'Guest';
                    }
                    ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                        <div>
                            <?php
                            if (isset($user['Audience_FirstName']) && isset($user['Audience_email'])) {
                                echo '<p class="m-0">' . htmlspecialchars($user['Audience_FirstName']) . '<br><small>' . htmlspecialchars($user['Audience_email']) . '</small></p>';
                            } else {
                                echo '<p class="m-0">Guest<br><small>guest@example.com</small></p>';
                            }
                            ?>
                        </div>
                    </div>
                    <hr>
                    <a href="ticketsoon.php">บัตรของฉัน</a>
                    <a href="buy.php">คำสั่งซื้อของฉัน</a>
                    <a href="favorite.php">งานที่ติดตาม</a>
                    <a href="private.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->
    <div class="setting-setting-container">
        <div class="setting-setting-container-data">
            <div class="setting-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="setting-profile-img"></div>
                <h2><?php echo htmlspecialchars($user['Audience_FirstName']); ?></h2>
                <p><?php echo htmlspecialchars($user['Audience_email']); ?></p>
            </div>

          
            <div class="private-setting-menu">
                <a href="ticketsoon.php" class="private-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    บัตรของฉัน</a>
                <a href="buy.php" class="private-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อของฉัน</a>
                <a href="favorite.php" class="private-setting-menu-item"><i class="bi bi-star"></i> อีเว้นท์ที่ติดตาม</a>
                <a href="private.php" class="private-setting-menu-item active"><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="private-setting-menu-item private-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>

        </div>
        <div class="setting-setting-container-extra-info">
            <h4>ข้อมูลส่วนตัว</h4>
            <div class="setting-profile-header">
                <div class="setting-profile-img-container">
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="setting-profile-img-large"
                        id="profileImage">
                </div>
                <div class="setting-profile-info">
                    <a href="#" class="setting-edit-button-info" id="changeProfileButton">
                        <i class="bi bi-pencil-square"></i> เปลี่ยนภาพโปรไฟล์
                    </a>
                    <input type="file" id="profileInput" accept="image/*" style="display: none;">
                    <p><?php echo htmlspecialchars($user['Audience_email']); ?></p>
                </div>
            </div>

            <div class="setting-personal-info" id="personalInfo">
                <div class="setting-info-header">
                    <h5><i class="bi bi-person"></i> ข้อมูลส่วนตัว</h5>
                    <a href="#" class="setting-edit-button-personal-info" id="editButton"><i
                            class="bi bi-pencil-square"></i>
                        แก้ไข</a>
                </div>
                <p>ชื่อ-นามสกุล: <?php echo htmlspecialchars($user['Audience_FirstName']) ?></p>
                <p>รหัสประจำตัว: <?php echo htmlspecialchars($user['StudentID']) ?></p>
                <p>เพศ: <?php echo htmlspecialchars($user['genderType']) ?></p>
                <p>เบอร์โทรศัพท์มือถือ: <?php echo htmlspecialchars($user['Audience_phone']) ?></p>
            </div>


            <div class="setting-edit-form" id="editForm" style="display: none;">
                <h5>แก้ไขข้อมูลส่วนตัว</h5>

                <form id="editProfileForm">
                    <label>ชื่อจริง</label>
                    <input type="text" id="firstName" name="firstName"
                        value="<?php echo htmlspecialchars($user['Audience_FirstName']) ?>" required>

                    <label>นามสกุล</label>
                    <input type="text" id="lastName" name="lastName"
                        value="<?php echo htmlspecialchars($user['Audience_LastName']) ?>" required>

                    <label>รหัสนิสิต</label>
                    <input type="text" id="staffId" name="staffId" maxlength="8" oninput="validateStudentId(this)"
                        value="<?php echo htmlspecialchars($user['StudentID']) ?>" required>
                    <small class="error-message" id="staffIdError"></small>

                    <label>เพศ</label>
                    <?php
                    // Fetch genders from the database
                    $sql = "SELECT genderID, genderType FROM gender";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo '<select id="gender" name="gender">';
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($user['genderID']) && $user['genderID'] == $row['genderID']) ? 'selected' : '';
                            echo '<option value="' . $row['genderID'] . '" ' . $selected . '>' . htmlspecialchars($row['genderType']) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<p>No gender data available.</p>';
                    }
                    ?>
                    <label>เบอร์โทรศัพท์มือถือ</label>
                    <input type="text" id="phoneNumber" name="phoneNumber"
                        value="<?php echo htmlspecialchars($user['Audience_phone']) ?>" maxlength="12"
                        oninput="formatPhoneNumber(this)" placeholder="000-000-0000" required>
                    <small class="error-message" id="phoneError"></small>

                    <button type="button" id="cancelButton">ย้อนกลับ</button>
                    <button type="submit" id="saveButton" disabled>บันทึก</button>
                </form>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const form = document.getElementById("editProfileForm");
                    const saveButton = document.getElementById("saveButton");
                    const cancelButton = document.getElementById("cancelButton");

                    // ตรวจสอบว่ามีการเปลี่ยนแปลงข้อมูลหรือไม่
                    form.addEventListener("input", () => {
                        saveButton.disabled = false;
                    });

                    // เมื่อกดย้อนกลับ ให้ซ่อนฟอร์ม
                    cancelButton.addEventListener("click", function () {
                        document.getElementById("editForm").style.display = "none";
                    });

                    // ส่งฟอร์มไปที่ PHP
                    form.addEventListener("submit", function (e) {
                        e.preventDefault();
                        saveButton.disabled = true; // ป้องกันการกดหลายครั้ง

                        const formData = new FormData(form);

                        fetch("update_profile.php", {
                            method: "POST",
                            body: formData
                        })
                            .then(response => response.text())
                            .then(data => {
                                alert(data); // แสดงข้อความตอบกลับจาก PHP
                                location.reload(); // รีเฟรชหน้า
                            })
                            .catch(error => console.error("Error:", error));
                    });
                });

                // ฟังก์ชันตรวจสอบเบอร์โทร
                function formatPhoneNumber(input) {
                    let value = input.value.replace(/\D/g, ""); // ลบตัวอักษรที่ไม่ใช่ตัวเลข
                    if (value.length > 3 && value.length <= 6) {
                        input.value = value.slice(0, 3) + "-" + value.slice(3);
                    } else if (value.length > 6) {
                        input.value = value.slice(0, 3) + "-" + value.slice(3, 6) + "-" + value.slice(6, 10);
                    } else {
                        input.value = value;
                    }
                }

                // ฟังก์ชันตรวจสอบรหัสบุคลากร
                function validateStudentId(input) {
                    let error = document.getElementById("staffIdError");
                    if (!/^\d{8}$/.test(input.value)) {
                        error.textContent = "รหัสต้องมี 8 หลักและเป็นตัวเลข";
                    } else {
                        error.textContent = "";
                    }
                }
            </script>


            <div class="setting-personal-info" id="personalInfoPass">
                <div class="setting-info-header">
                    <h5><i class="bi bi-lock"></i> รหัสผ่าน</h5>
                    <a href="#" class="setting-edit-button-personal-info" id="changePasswordButton">
                        <i class="bi bi-pencil-square"></i>เปลี่ยนรหัสผ่าน
                    </a>
                </div>
            </div>

            <div class="setting-edit-form" id="passwordForm" style="display: none;">
                <h5>เปลี่ยนรหัสผ่าน</h5>
                <form id="changePasswordForm"></form>
                    <label for="OldPassword">ยืนยันรหัสผ่านเก่า:</label>
                    <input type="password" id="OldPassword" name="OldPassword" required>
                    <p id="message"></p>

                    <label for="newPassword">รหัสผ่านใหม่:</label>
                    <input type="password" id="newPassword" name="newPassword" required>

                    <label for="confirmPassword">ยืนยันรหัสผ่าน:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>

                    <p id="message"></p>
                    <button id="cancelPasswordButton" type="button">ย้อนกลับ</button>
                    <button id="savePassword" type="submit">บันทึก</button>
                </form>

                <script>
                    function validatePassword() {
                        var password = document.getElementById("OldPassword").value;
                        var message = document.getElementById("message");

                        var hasLetter = /[a-zA-Z]/.test(password);
                        var hasNumber = /\d/.test(password);

                        if (!hasLetter || !hasNumber) {
                            message.innerHTML = "รหัสผ่านต้องมีทั้งตัวอักษรและตัวเลข!";
                            message.style.color = "red";
                            return false;
                        } else {
                            message.innerHTML = "";
                            message.style.color = "green";
                            return true;
                        }
                    }

                    function samePassword() {
                        var NewPass1 = document.getElementById("newPassword").value;
                        var NewPass2 = document.getElementById("confirmPassword").value;
                        if (NewPass1 == NewPass2) {
                            message.innerHTML = "";
                            return true;
                        } else {
                            message.innerHTML = "รหัสผ่านไม่ตรงกัน"
                            return false;
                        }
                    }

                    document.getElementById("savePassword").addEventListener("click", function (e) {
                        e.preventDefault();
                        const oldPassword = document.getElementById("OldPassword").value;
                        const newPassword = document.getElementById("newPassword").value;
                        const confirmPassword = document.getElementById("confirmPassword").value;

                        if (newPassword !== confirmPassword) {
                            alert("รหัสผ่านใหม่ไม่ตรงกัน");
                            return;
                        }

                        // Add your own logic to handle password change here
                        const formData = new FormData();
                        formData.append("oldPassword", oldPassword);
                        formData.append("newPassword", newPassword);

                        fetch("update_password.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data); // แสดงข้อความตอบกลับจาก PHP
                            if (data === "Password updated successfully") {
                                location.reload(); // รีเฟรชหน้า
                            }
                        })
                        .catch(error => console.error("Error:", error));
                    });
                </script>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- Meyawo js -->

    <script>
        //ฟอร์ม
        document.addEventListener("DOMContentLoaded", function () {
            const editButton = document.getElementById("editButton");
            const personalInfo = document.getElementById("personalInfo");
            const editForm = document.getElementById("editForm");
            const cancelButton = document.getElementById("cancelButton");
            const saveButton = document.getElementById("saveButton");

            // เมื่อกด "แก้ไข" ให้ซ่อนข้อมูลเดิมและแสดงฟอร์ม
            editButton.addEventListener("click", function (e) {
                e.preventDefault();
                personalInfo.style.display = "none";
                editForm.style.display = "block";
            });

            // เมื่อกด "ย้อนกลับ" ให้ซ่อนฟอร์มและแสดงข้อมูลเดิม
            cancelButton.addEventListener("click", function () {
                editForm.style.display = "none";
                personalInfo.style.display = "block";
            });

            // เมื่อกด "บันทึก"
            saveButton.addEventListener("click", function () {
                // รับค่าที่แก้ไข
                const firstName = document.getElementById("firstName").value;
                const lastName = document.getElementById("lastName").value;
                const staffId = document.getElementById("staffId").value;
                const birthDateValue = document.getElementById("birthDate").value;
                const gender = document.getElementById("gender").value;
                const rankName = document.getElementById("rankName").value;
                const phoneNumber = document.getElementById("phoneNumber").value;

                // แปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น DD/MM/YYYY
                let formattedBirthDate = "";
                if (birthDateValue) {
                    const dateParts = birthDateValue.split("-");
                    formattedBirthDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                }

                // อัปเดตข้อมูลที่แสดง
                personalInfo.innerHTML = `
            <div class="info-header">
                <h5>ข้อมูลส่วนตัว</h5>
                <a href="#" class="edit-button-personal-info" id="editButton"><i class="bi bi-pencil-square"></i> แก้ไข</a>
            </div>
            <p>ชื่อ-นามสกุล: ${firstName} ${lastName}</p>
            <p>รหัสนิสิต: ${staffId}</p>
            <p>วันเกิด: ${formattedBirthDate}</p>
            <p>เพศ: ${gender}</p>
            <p>ตำแหน่ง: ${rankName}</p>
            <p>เบอร์โทรศัพท์มือถือ: ${phoneNumber}</p>
        `;

                // ซ่อนฟอร์มและแสดงข้อมูลที่อัปเดต
                editForm.style.display = "none";
                personalInfo.style.display = "block";

                // ต้องเพิ่ม event listener ใหม่ให้ปุ่มแก้ไขที่ถูกสร้างขึ้นใหม่
                document.getElementById("editButton").addEventListener("click", function (e) {
                    e.preventDefault();
                    personalInfo.style.display = "none";
                    editForm.style.display = "block";
                });
            });
        });

        //เช็คข้อมูลว่ากรอกถูกมั้ย
        function validateStudentId(input) {
            input.value = input.value.replace(/\D/g, '').slice(0, 8); // รับเฉพาะตัวเลข และจำกัด 8 ตัว
            const errorMessage = document.getElementById("staffIdError");

            if (input.value.length < 8) {
                errorMessage.textContent = "กรุณากรอกให้ครบ 8 ตัวเลข";
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }

            validateForm();
        }
        function formatPhoneNumber(input) {
            let phone = input.value.replace(/\D/g, '').slice(0, 10); // รับเฉพาะตัวเลข และจำกัด 10 ตัว
            input.value = phone.length > 6 ? `${phone.slice(0, 3)}-${phone.slice(3, 6)}-${phone.slice(6)}` : phone.length > 3 ? `${phone.slice(0, 3)}-${phone.slice(3)}` : phone;

            const errorMessage = document.getElementById("phoneError");

            if (phone.length < 10) {
                errorMessage.textContent = "กรุณากรอกเบอร์โทรศัพท์มือถือให้ถูกต้อง";
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }

            validateForm();
        }
        function validateForm() {
            const firstName = document.getElementById("firstName").value.trim();
            const lastName = document.getElementById("lastName").value.trim();
            const studentId = document.getElementById("staffId").value.trim();
            const birthDate = document.getElementById("birthDate").value.trim();
            const phoneNumber = document.getElementById("phoneNumber").value.replace(/\D/g, '').trim();

            const isValid = firstName && lastName && studentId.length === 8 && birthDate && phoneNumber.length === 10;
            document.getElementById("saveButton").disabled = !isValid;
        }

        //ฟอร์มแก้ไขข้อมูล
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("firstName").addEventListener("input", validateForm);
            document.getElementById("lastName").addEventListener("input", validateForm);
            document.getElementById("staffId").addEventListener("input", validateStudentId);
            document.getElementById("birthDate").addEventListener("change", validateForm);
            document.getElementById("rankName").addEventListener("input", validateForm);
            document.getElementById("phoneNumber").addEventListener("input", formatPhoneNumber);
        });

        //เปลี่ยนรูปโปรไฟล์
        document.addEventListener("DOMContentLoaded", function () {
            const changeProfileButton = document.getElementById("changeProfileButton");
            const profileInput = document.getElementById("profileInput");
            const profileImage = document.getElementById("profileImage");

            // เมื่อกดปุ่ม "เปลี่ยนภาพโปรไฟล์" ให้เปิด file picker
            changeProfileButton.addEventListener("click", function (e) {
                e.preventDefault();
                profileInput.click();
            });

            // เมื่อเลือกรูปภาพ ให้แสดงผลที่ profile-img-large
            profileInput.addEventListener("change", function () {
                const file = profileInput.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profileImage.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        //ฟอร์มเปลี่ยนรหัสผ่าน(เพิ่ม)
        document.addEventListener("DOMContentLoaded", function () {
            const editButton = document.getElementById("editButton");
            const personalInfo = document.getElementById("personalInfo");
            const personalInfoPass = document.getElementById("personalInfoPass");
            const editForm = document.getElementById("editForm");
            const cancelButton = document.getElementById("cancelButton");
            const saveButton = document.getElementById("saveButton");
            const changePasswordButton = document.getElementById("changePasswordButton");
            const passwordForm = document.getElementById("passwordForm");
            const cancelPasswordButton = document.getElementById("cancelPasswordButton");

            // แสดงฟอร์มแก้ไขข้อมูลส่วนตัว


            // กดปุ่ม "เปลี่ยนรหัสผ่าน" เพื่อแสดงฟอร์ม(เพิ่ม)
            changePasswordButton.addEventListener("click", function (e) {
                e.preventDefault();
                personalInfoPass.style.display = "none"
                personalInfo.style.display = "block";
                passwordForm.style.display = "block";
            });

            // กดปุ่ม "ยกเลิก" เพื่อซ่อนฟอร์มเปลี่ยนรหัสผ่าน(เพิ่ม)
            cancelPasswordButton.addEventListener("click", function () {
                passwordForm.style.display = "none";
                personalInfo.style.display = "block";
                personalInfoPass.style.display = "block"

            });
            //saveButtonPassword*ต้องเชื่อมdata_base*(เพิ่ม)
            savePassword.addEventListener("click", function (e) {
                if (!validatePassword() || !samePassword()) {
                    e.preventDefault(); // ป้องกันการบันทึกหากรหัสผ่านไม่ผ่านเงื่อนไข
                } else {
                    passwordForm.style.display = "none";
                    personalInfo.style.display = "block";
                    personalInfoPass.style.display = "block"
                }

            });
        });

        // ตรวจสอบรหัสผ่านต้องมีทั้งตัวอักษรและตัวเลข(เพิ่
        //ตรวจสอบรหัสผ่านใหม่ทั้งสองอันว่าตรงกันไหม(เพิ่ม)

        // ส่งข้อความแจ้งเตือน*ยังไม่ได้ใส่(เพิ่ม)
        function checkPassword() {
            var errorMessage = validatePassword(); // เรียก function ตรวจสอบรหัสผ่าน
            var samePassword = samePassword();

            if (errorMessage) {
                alert(errorMessage); // ถ้ามี error ให้แสดง alert
            } else if (samePassword) {
                alert(samePassword);
            } else {
                alert(เปลี่ยนรหัสผ่านเรียบร้อย);
            }
        }
        //Endฟอร์มเปลี่ยนรหัสผ่าน
    </script>

</body>

</html>