<?php
session_start();
// Database connection file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../db.php';
// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}
$email = $_SESSION['email'];
$role = $_SESSION['role'] ?? '';
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT first_name, studentaffairs_email FROM studentaffairs WHERE studentaffairs_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if (!$user) {
        echo "No user found with email: " . htmlspecialchars($email);
    }
} else {
    echo $user;
}

//var_dump($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventName = htmlspecialchars($_POST['eventName']);
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $eventPrice = $_POST['eventPrice'];
    $eventLocationName = htmlspecialchars($_POST['eventLocationName']);
    //$eventLocationLink = htmlspecialchars($_POST['eventLocationLink']);
    $eventDetails = htmlspecialchars($_POST['eventDetails']);
    //$categoryName = $_POST['categories'];
    $register = $_POST['register'];
    //$checkFreeEvent = isset($_POST['CheckFreeEvent']) ? $_POST['CheckFreeEvent'] : 'no';
    $publicSaleDate = $_POST['ticketReleaseDate'];
    $eventLayout = $_POST['eventLocationLink'] ?? null; // รับค่าจากฟอร์ม

    // ถ้าราคาบัตรเป็น 0 ให้ตั้งค่า CheckFreeEvent เป็น 'free'
    if ($eventPrice == 0) {
        $checkFreeEvent = 'free';
    }

    // ดึง ID ของหมวดหมู่
    //$categoryID = null;
    //$categoryQuery = "SELECT id FROM categories WHERE Categories = ?";
    //$categoryStmt = $conn->prepare($categoryQuery);
    //$categoryStmt->bind_param("s", $categoryName);
    //$categoryStmt->execute();
    //$categoryStmt->bind_result($categoryID);
    //$categoryStmt->fetch();
    //$categoryStmt->close();

    //if ($categoryID === null) {
    //    die("Error: หมวดหมู่ไม่ถูกต้อง");
    //}
    var_dump($_POST);

    // อัปโหลดรูปภาพ
    $eventImage = null;
    if (!empty($_FILES["eventImage"]["name"]) && $_FILES["eventImage"]["error"] === 0) {
        $eventImage = "uploads/" . basename($_FILES["eventImage"]["name"]);
        move_uploaded_file($_FILES["eventImage"]["tmp_name"], $eventImage);
    }

    // SQL Insert
    $sql = "INSERT INTO Event (Event_Name, Event_Date, Event_Time, Event_Price, Event_Location, Event_Detail, Event_Picture, Event_Layout, TypeRegister, Public_Sale_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssssssssss", $eventName, $eventDate, $eventTime, $eventPrice, $eventLocationName, $eventDetails, $eventImage, $eventLayout, $register, $publicSaleDate);

    if ($stmt->execute()) {
       header("Location: eventAll.php");
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: '" . htmlspecialchars($stmt->error) . "'
            });
        </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

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
                    <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo htmlspecialchars($user['first_name']); ?><br>
                                <small><?php echo htmlspecialchars($user['studentaffairs_email']); ?></small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <a href="eventAll.php">อีเว้นท์ทั้งหมด</a>
                    <a href="allOrder.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="addEvent.php">เพิ่มอีเว้นท์</a>
                    <a href="changeRole.php">คำขอจัดอีเว้นท์</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="addEvent-setting-container">
        <div class="addEvent-setting-container-data">
            <div class="addEvent-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="addEvent-profile-img"></div>
                <h2><?php echo htmlspecialchars($user['first_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['studentaffairs_email']); ?></p>
            </div>

            <div class="addEvent-setting-menu">
                <a href="eventAll.php" class="addEvent-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="addEvent-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="addEvent-setting-menu-item active"><i
                        class="bi bi-clipboard2-plus-fill"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="addEvent-setting-menu-item"><i class="bi bi-person-plus"></i>
                    คำขอจัดอีเว้นท์</a>
                <a href="setting.php" class="addEvent-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="addEvent-setting-menu-item addEvent-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="addEvent-container">
            <div class="addEvent-name">
                <h3>เพิ่มอีเว้นท์</h3>
                <div class="addEvent-edit-form" id="editForm">
                    <form method="POST" enctype="multipart/form-data">
                        <label>ชื่ออีเว้นท์</label>
                        <input type="text" name="eventName" id="eventName" value="">

                        <label>วันที่จัดอีเว้นท์</label>
                        <input type="date" name="eventDate" id="Date" value="" onkeydown="return false"
                            onclick="this.showPicker()">

                        <label>วันวางจำหน่ายตั๋ว</label>
                        <input type="date" name="ticketReleaseDate" id="ticketReleaseDate" value=""
                            onkeydown="return false" onclick="this.showPicker()">

                        <label>เวลา</label>
                        <input type="time" name="eventTime" id="time" value="" onkeydown="return false"
                            onclick="this.showPicker()">

                        <label>ราคาบัตร</label>
                        <input type="number" name="eventPrice" id="price" min="0" step="1" placeholder="ระบุราคา">

                        <label>ชื่อสถานที่</label>
                        <input type="text" name="eventLocationName" id="eventlocationName" value="">

                        <label>ลิงก์สถานที่</label>
                        <input type="url" name="eventLocationLink" id="location"
                            placeholder="ใส่ลิงก์สถานที่ เช่น Google Maps">

                        <label>รายละเอียด</label>
                        <textarea name="eventDetails" id="details" rows="4"></textarea>

                        <label>รูปภาพอีเว้นท์</label>
                        <input type="file" name="eventImage" id="eventImage" accept="image/*">

                        <label>หมวดหมู่</label>
                        <select name="categories" id="categories">
                            <?php
                            $sql = "SELECT Categories FROM categories";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['Categories'] . "'>" . $row['Categories'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>ไม่มีหมวดหมู่</option>";
                            }
                            ?>
                        </select>

                        <label>ลงทะเบียน</label>
                        <select name="register" id="register">
                            <option value="yes">ลงทะเบียน</option>
                            <option value="no">ไม่ลงทะเบียน</option>
                        </select>
                        <button type="submit" id="saveButton">บันทึก</button>
                    </form>
                </div>


            </div>
        </div>
    </div>
    <script src="../assets/js/navbar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById("saveButton").addEventListener("click", function() {
            // ดึงค่าจากฟอร์ม
            const eventName = document.getElementById("eventName")?.value || "";
            const eventDate = document.getElementById("Date")?.value || "";
            const eventTime = document.getElementById("time")?.value || "";
            const eventPrice = document.getElementById("price")?.value || "";
            const eventLocationName = document.getElementById("eventlocationName")?.value || "";
            const eventDetails = document.getElementById("details")?.value || "";
            const eventImageInput = document.getElementById("eventImage");
            const ticketReleaseDate = document.getElementById("ticketReleaseDate")?.value || "";
            const eventLayoutInput = document.getElementById("eventLayout");

            if (!eventName || !eventDate || !eventTime || !eventLocationName) {
                alert("กรุณากรอกข้อมูลให้ครบถ้วน!");
                return;
            }

            // ตรวจสอบว่ามีการอัปโหลดรูปภาพหรือไม่
            let eventImageURL = "";
            if (eventImageInput.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    eventImageURL = e.target.result;
                    processEvent(eventImageURL);
                };
                reader.readAsDataURL(eventImageInput.files[0]);
            } else {
                processEvent("default-image.jpg");
            }

            function processEvent(imageURL) {
                let eventLayoutURL = "";
                if (eventLayoutInput.files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        eventLayoutURL = e.target.result;
                        saveEventToLocal(eventName, eventDate, eventTime, eventPrice, eventLocationName, eventDetails, imageURL, ticketReleaseDate, eventLayoutURL);
                    };
                    reader.readAsDataURL(eventLayoutInput.files[0]);
                } else {
                    saveEventToLocal(eventName, eventDate, eventTime, eventPrice, eventLocationName, eventDetails, imageURL, ticketReleaseDate, "default-layout.jpg");
                }
            }
        });

        function saveEventToLocal(name, date, time, price, locationName, details, imageURL, ticketReleaseDate, eventLayoutURL) {
            const eventData = {
                name,
                date,
                time,
                price,
                locationName,
                details,
                imageURL,
                ticketReleaseDate,
                eventLayoutURL
            };

            // บันทึกข้อมูลลงใน localStorage
            localStorage.setItem('eventData', JSON.stringify(eventData));

            // เปลี่ยนหน้าไป eventAll.php
        }
    </script>

</body>

</html>