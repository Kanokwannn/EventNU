<?php
session_start(); // เริ่มต้น session
include "../db.php";

// เปิดโหมดแสดงข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบค่า register_id จาก URL
$register_id = isset($_GET['register_id']) ? intval($_GET['register_id']) : 0;
if ($register_id <= 0) {
    die("Invalid register ID!");
}

$email = $_SESSION['email']; // อีเมลของผู้ใช้ที่ล็อกอิน

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$sql = "SELECT 
            a.*, 
            f.Faculty_Name, 
            m.Major_Name 
        FROM Audience a
        LEFT JOIN Faculty f ON a.FacultyID = f.FacultyID
        LEFT JOIN Major m ON a.MajorID = m.MajorID
        WHERE a.Audience_email = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error (User Query): " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'] ?? "N/A";
    $last_name = $row['Audience_LastName'] ?? "N/A";
    $faculty = $row['Faculty_Name'] ?? "N/A";
    $major = $row['Major_Name'] ?? "N/A";
    $role = $row['Audience_Role'] ?? "N/A";
    $id = $row['StudentID'] ?? "N/A";
    $phone = $row['Audience_Phone'] ?? "N/A";
} else {
    die("User not found!");
}

// ดึงข้อมูลการลงทะเบียนและอีเวนต์
$sql = "
    SELECT 
        r.register_id, 
        r.Audience_email, 
        r.EventID,
        r.booking_id,
        DATE_FORMAT(r.register_date, '%Y-%m-%d') AS register_date, 
        r.register_status,
        e.Event_Name, 
        e.Event_Price, 
        e.Event_Date, 
        e.Event_Time, 
        e.Event_Location, 
        e.Event_Picture,
        b.ticket_count,
        b.ticket_totalprice
    FROM register r
    LEFT JOIN Event e ON r.EventID = e.EventID
    LEFT JOIN booking b ON r.booking_id = b.booking_id
    WHERE r.register_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error (Event Query): " . $conn->error);
}
$stmt->bind_param("i", $register_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // กำหนดค่าตัวแปรจากฐานข้อมูล
    $register_id = $row['register_id'];
    $booking_id = $row['booking_id'];
    $event_name = $row['Event_Name'];
    $event_price = $row['Event_Price'];
    $event_date = $row['Event_Date'];
    $event_time = $row['Event_Time'];
    $ticket_count = $row['ticket_count'];
    $ticket_totalprice = $row['ticket_totalprice'];
    $event_location = $row['Event_Location'];
    $event_picture = $row['Event_Picture'];
    $register_date = $row['register_date'];
    $status = $row['register_status'];

    // แปลงวันที่และเวลา
    $event_date_formatted = $event_date ? date("d F Y", strtotime($event_date)) : "No date available";
    $event_time_formatted = $event_time ? date("H:i", strtotime($event_time)) : "No time available";

    // กำหนดราคาตั๋ว
    $price_display = ($event_price == 0) ? "Free" : $event_price;
} else {
    die("No event found.");
}

// ปิด statement และ connection
$stmt->close();
$conn->close();
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
                <li class="navbar-nav-item">
                    <a class="nav-link" href="followrequest.html" data-target="home">ประชาสัมพันธ์</a>
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
                    <span><?php echo $first_name; ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo $first_name; ?><br><small><?php echo $email; ?></small></p>
                        </div>
                    </div>
                    <hr>
                    <a href="ticketsoon.php">บัตรของฉัน</a>
                    <a href="buy.php">คำสั่งซื้อของฉัน</a>
                    <a href="favorite.php">อีเว้นท์ที่ติดตาม</a>
                    <a href="private.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->
    <div class="entry-content clear" itemprop="text">

        <div class="woocommerce">
            <div class="woocommerce-notices-wrapper"></div>
            <form class="woocommerce-cart-form" method="post">
                <p id="countdown-timer">หน้านี้จะหมดอายุใน <span id="timer">30</span> นาที</p>

                <table class="shop_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="product-remove"><span class="screen-reader-text">จำนวนบัตร</span></th>
                            <th class="product-thumbnail"><span class="screen-reader-text">อีเว้นท์</span>
                            </th>
                            <th class="product-name">ชื่ออีเว้นท์</th>
                            <th class="product-price">ราคา</th>
                            <th class="product-subtotal">ยอดรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="woocommerce-cart-form__cart-item cart_item">
                            <td class="ticket-quantity">
                                <span id=""><?php echo $ticket_count; ?></span>
                            </td>

                            <td class="cart-thumbnail"><img src="../assets/imgs/openhouse.jpg" alt="Postcard"></td>
                            <td class="product-name" data-title="EventName">
                                <span id=""><?php echo $event_name; ?></span>
                            </td>
                            <td class="product-price" data-title="Price">
                                <span class="woocommerce-Price-amount amount"><bdi><span
                                            class="woocommerce-Price-currencySymbol"></span><?php echo $event_price; ?></bdi></span>
                            </td>
                            <td class="product-subtotal" data-title="Subtotal">
                                <span class="woocommerce-Price-amount amount"><bdi><span
                                            class="woocommerce-Price-currencySymbol"></span><?php echo $ticket_totalprice; ?></bdi></span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class="actions">
                                <div class="upload-qr">
                                    <img src="../assets/imgs/scan.jpg" alt="Upload QR" class="icon">
                                </div>
                                <div class="qr-upload">
                                    <label for="qr_code" class="screen-reader-text">Upload QR Code:</label>
                                    <input type="file" name="qr_code" id="qr_code" accept="image/*">
                                </div>

                                <!-- Popup Modal -->
                                <div id="qr_modal" class="modal">
                                    <div class="modal-content">
                                        <span class="modal-close">&times;</span>
                                        <img id="qr_preview_img" src="" alt="QR Preview">
                                    </div>
                                </div>

                                <input type="hidden" id="woocommerce-cart-nonce" name="woocommerce-cart-nonce"
                                    value="ba17e27740">
                                <input type="hidden" name="_wp_http_referer"
                                    value="/earth-store-02/cart/?customize=template">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
            <div class="cart-collaterals">
                <div class="cart_totals ">
                    <h2>ที่ต้องชำระ</h2>
                    <table cellspacing="0" class="shop_table shop_table_responsive">
                        <tbody>
                            <tr class="cart-subtotal">
                                <th>ราคาบัตร</th>
                                <td data-title="Subtotal"><span class="woocommerce-Price-amount amount"><bdi><span
                                                class="woocommerce-Price-currencySymbol"></span><?php echo $event_price; ?></bdi></span>
                                </td>
                            </tr>
                            <tr class="order-total">
                                <th>ยอดรวม</th>
                                <td data-title="Total"><strong><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol"></span><?php echo $ticket_totalprice; ?></bdi></span></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="wc-proceed-to-checkout">
                        <button class="checkout-button button alt wc-forward disabled-link" id="checkout-button">
                            Proceed to checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        //นับถอยหลัง
        let timeLeft = 600;
        let timerElement = document.getElementById("timer");

        function updateTimerDisplay() {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`;
        }

        let countdown = setInterval(function() {
            timeLeft--;
            updateTimerDisplay();

            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = "buy.html";
            }
        }, 1000);

        // แสดงค่าเริ่มต้นก่อนเริ่มนับถอยหลัง
        updateTimerDisplay();

        //ส่ง
        document.getElementById("qr_code").addEventListener("change", function() {
            let checkoutButton = document.querySelector(".checkout-button");
            if (this.files.length > 0) {
                checkoutButton.classList.remove("disabled-link"); // เปิดใช้งานปุ่ม
            } else {
                checkoutButton.classList.add("disabled-link"); // ปิดใช้งานปุ่มถ้าไม่มีไฟล์
            }
        });

        document.getElementById("checkout-button").addEventListener("click", function(event) {
            event.preventDefault(); // ป้องกันการเปลี่ยนหน้าโดยอัตโนมัติ

            let fileInput = document.getElementById("qr_code");
            let file = fileInput.files[0];

            if (!file) {
                alert("กรุณาอัปโหลด QR Code ก่อนทำการชำระเงิน!");
                return;
            }

            let formData = new FormData();
            formData.append("qr_code", file);
            formData.append("register_id", "<?php echo $register_id; ?>");

            fetch("upload.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        alert("ชำระเงินสำเร็จ! ระบบกำลังตรวจสอบ...");
                        window.location.href = "buy.php"; // เปลี่ยนหน้าไปยัง buy.php
                    } else {
                        alert("เกิดข้อผิดพลาด: " + data);
                    }
                })
                .catch(error => console.error("Error:", error));
        });


        document.getElementById('qr_code').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('qr_preview_img');
                    img.src = e.target.result;
                    document.getElementById('qr_modal').style.display = "flex";
                };
                reader.readAsDataURL(file);
            }
        });

        document.querySelector('.modal-close').addEventListener('click', function() {
            document.getElementById('qr_modal').style.display = "none";
        });

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('qr_modal');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    </script>
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>
</body>

</html>