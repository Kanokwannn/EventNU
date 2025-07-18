<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่มี session, เปลี่ยนเส้นทางไปหน้า login
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลตาม email
$email = $_SESSION['email'];
$sql = "SELECT * FROM Audience WHERE Audience_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email); // ส่ง email เป็น parameter ไปใน query
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบผู้ใช้หรือไม่
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'];
    $last_name = $row['Audience_LastName'];
    $faculty = $row['FacultyID'];  // หรือเพิ่มข้อมูลอื่นๆ ตามต้องการ
    $role = $row['Audience_Role'];
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}

// ตรวจสอบว่ามี event_id ใน URL หรือไม่
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Event not found!";
    exit();
}

$event_id = $_GET['event_id']; // ดึง EventID จาก URL

// ดึงข้อมูลของ Event ตาม EventID
$sql = "SELECT * FROM Event WHERE EventID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ดึงข้อมูลจากฐานข้อมูล
    $row = $result->fetch_assoc();
    $event_picture = $row['Event_Picture'];
    $event_name = $row['Event_Name'];
    $event_date = $row['Event_Date'];
    $event_time = $row['Event_Time'];
    $event_location = $row['Event_Location'];
    $event_price = $row['Event_Price'];
    $event_detail = $row['Event_Detail'];
    $event_id = $row['EventID'];
    $eventTypeRe = $row['TypeRegister'];
} else {
    echo "No event found.";
}
// ตรวจสอบว่า event_price เป็น 0 หรือไม่
if ($event_price == 0) {
    $price_display = "Free";
} else {
    $price_display = $event_price;
}

// แปลงวันที่เป็นรูปแบบที่ต้องการ
$event_date_formatted = date("d F Y", strtotime($event_date));  // วันที่ในรูปแบบ "01 January 2025"

// ตรวจสอบให้แน่ใจว่าเวลาในฐานข้อมูลมีรูปแบบที่ถูกต้อง ถ้าไม่ ให้แสดงเวลาเป็น "00:00"
$event_time_formatted = date("H:i", strtotime($event_time));  // เวลาในรูปแบบ "18:00"

$days_th = [
    "Sunday" => "อาทิตย์",
    "Monday" => "จันทร์",
    "Tuesday" => "อังคาร",
    "Wednesday" => "พุธ",
    "Thursday" => "พฤหัสบดี",
    "Friday" => "ศุกร์",
    "Saturday" => "เสาร์"
];

$months_th = [
    "01" => "มกราคม",
    "02" => "กุมภาพันธ์",
    "03" => "มีนาคม",
    "04" => "เมษายน",
    "05" => "พฤษภาคม",
    "06" => "มิถุนายน",
    "07" => "กรกฎาคม",
    "08" => "สิงหาคม",
    "09" => "กันยายน",
    "10" => "ตุลาคม",
    "11" => "พฤศจิกายน",
    "12" => "ธันวาคม"
];

$day_of_week = $days_th[date("l", strtotime($event_date))];
$day = date("d", strtotime($event_date));
$month = $months_th[date("m", strtotime($event_date))];
$year = date("Y", strtotime($event_date)) + 543; // แปลงเป็น พ.ศ.

$sql = "SELECT f.feedback_comment, f.feedback_point, f.feedback_option, a.Audience_FirstName, a.Audience_LastName 
        FROM feedback f
        JOIN Audience a ON f.Audience_email = a.Audience_email
        WHERE f.EventID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$feedback_result = $stmt->get_result();

$feedbacks = [];
if ($feedback_result->num_rows > 0) {
    while ($feedback_row = $feedback_result->fetch_assoc()) {
        $feedbacks[] = [
            "name" => $feedback_row['Audience_FirstName'] . " " . $feedback_row['Audience_LastName'],
            "comment" => $feedback_row['feedback_comment'],
            "point" => $feedback_row['feedback_point'],
            "option" => json_decode($feedback_row['feedback_option'], true),
        ];
    }
}

// ดึงข้อมูลอีเวนต์ทั้งหมดจากตาราง Event
$sql_event = "SELECT * FROM Event";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->execute();
$event_result = $stmt_event->get_result();

// ตรวจสอบว่ามีอีเวนต์หรือไม่
$events = [];
if ($event_result->num_rows > 0) {
    while ($event_row = $event_result->fetch_assoc()) {
        $event_ids = $event_row['EventID'];
        $event_names = $event_row['Event_Name'];
        $event_dates = $event_row['Event_Date'];  // รับค่า event_date จากผลลัพธ์
        $event_times = $event_row['Event_Time'];
        if ($event_dates) {
            $event_dates_formatted = date("d F Y", strtotime($event_date));  // วันที่ในรูปแบบ "01 January 2025"
        } else {
            $event_dates_formatted = "No date available";  // ถ้าไม่มีวันที่ในข้อมูล
        }

        if ($event_times) {
            $event_times_formatted = date("H:i", strtotime($event_time));  // เวลาในรูปแบบ "18:00"
        } else {
            $event_times_formatted = "No time available";  // ถ้าไม่มีเวลาในข้อมูล
        }
        $event_locations = $event_row['Event_Location'];
        $event_details = $event_row['Event_Detail'];
        $event_pictures = $event_row['Event_Picture'];
        $event_prices = $event_row['Event_Price'];
        $price_displays = ($event_price == 0) ? "Free" : $event_price;
        $event_ids = $event_row['EventID'];
        // สร้างอาร์เรย์อีเวนต์
        $events[] = [
            'event_ids' => $event_row['EventID'],
            'event_names' => $event_row['Event_Name'],
            'event_dates' => $event_row['Event_Date'],
            'event_times' => $event_row['Event_Time'],
            'event_dates_formatted' => $event_date_formatted,
            'event_times_formatted' => $event_time_formatted,
            'event_locations' => $event_row['Event_Location'],
            'event_details' => $event_row['Event_Detail'],
            'event_pictures' => $event_row['Event_Picture'],
            'event_prices' => $event_row['Event_Price'],
            'price_displays' => $price_display,
        ];

        // ตรวจสอบราคาอีเวนต์

    }
} else {
    echo "No events found.";
}

// แปลง array ของ feedbacks เป็น JSON และส่งไปยัง JavaScript
$feedbacks_json = json_encode($feedbacks, JSON_UNESCAPED_UNICODE);

$sql = "SELECT COUNT(*) FROM favorite WHERE Audience_email = ? AND EventId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email, $event_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$is_favorite = $count > 0; // ถ้ามีแถวแปลว่าเป็น Favorite
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

<body>

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
                        <?php
                        // เช็คว่าเรามีอีเวนต์หรือไม่
                        if (count($events) > 0) {
                            // วนลูปแสดงอีเวนต์
                            foreach ($events as $event) {
                                // เก็บข้อมูลจากอีเวนต์
                                $event_dates = $event['event_dates_formatted'];
                                $event_names = $event['event_names'];
                                $event_locations = $event['event_locations'];
                                $event_pictures = $event['event_pictures'];
                                $event_prices = $event['price_displays'];
                                $event_ids = $event['event_ids'];

                                // สร้างปุ่มที่แสดงราคา
                                $button_class = "navbar-event-button";  // ไม่ต้องสนใจราคาบัตร
                                $button_text = "ดูรายละเอียด";  // แสดงข้อความ "ดูรายละเอียด" ตลอด
                        ?>
                                <div class="navbar-event-card">
                                    <img src="<?php echo $event_pictures; ?>" alt="<?php echo $event_names; ?>">
                                    <div class="navbar-event-info">
                                        <p class="navbar-event-date"><i class="bi bi-calendar"></i> <?php echo $event_dates; ?></p>
                                        <h4><?php echo $event_names; ?></h4>
                                        <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> <?php echo $event_locations; ?></p>
                                        <a href="details.php?event_id=<?php echo $event['event_ids']; ?>">
                                            <button class="<?php echo $button_class; ?>"><?php echo $button_text; ?></button>
                                        </a>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<p>No events available.</p>";
                        }
                        ?>
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
                    <a class="nav-link" href="followrequest.php" data-target="home">ประชาสัมพันธ์</a>
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
                    <a href="favorite.php">งานที่ติดตาม</a>
                    <a href="private.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <!-- event -->
    <div class="adetails">
        <div class="adetails-top-container">
            <div class="adetails-top-box">
                <div class="adetails-top">
                    <div class="adetails-top-left">
                        <img src="<?php echo $event_picture; ?>" alt="Event Left Image">
                    </div>
                    <div class="adetails-top-right">
                        <h3>Event in NU Presents "<?php echo $event_name; ?>"</h3>
                        <div class="adetails-top-icons">
                            <i class="fas fa-share-alt" onclick="shareEvent()"></i>
                            <i class="fas fa-link" onclick="copyLink()"></i>
                            <i id="star-icon" class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-star"
                                onclick="toggleFavorite(<?php echo $event_id; ?>, '<?php echo $email; ?>')"></i>
                        </div>
                        <div class="adetails-top-info">
                            <div class="adetails-info-item">
                                <div class="adetails-icon-circle">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <span><strong>Public Sale Date : </strong> <?php echo $event_date_formatted; ?> | <?php echo $event_time_formatted; ?></span>
                            </div>
                            <div class="adetails-info-item">
                                <div class="adetails-icon-circle">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <span><strong>Event Date : </strong> <?php echo $event_date_formatted; ?></span>
                            </div>
                            <div class="adetails-info-item">
                                <div class="adetails-icon-circle">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span><strong>Event Time : </strong> เริ่มงาน <?php echo $event_time_formatted; ?> น. เป็นต้นไป</span>
                            </div>
                            <div class="adetails-info-item">
                                <div class="adetails-icon-circle">
                                    <i class="fas fa-ticket"></i>
                                </div>
                                <span><strong>Prices : </strong> <span id="eventPrice"> <?php echo $price_display; ?></span></span>
                            </div>
                            <div class="adetails-info-item">
                                <div class="adetails-icon-circle">
                                    <i class="fas fa-location-dot"></i>
                                </div>
                                <span><strong style="color: gray;"><?php echo $event_location; ?></strong></span>
                            </div>
                            <div class="adetails-mapping">
                                <p style="color: gray;">Province: Phitsanulok District: Muang</p>
                                <a href="https://www.google.com/maps/place/..." target="_blank"
                                    class="adetails-map-link">
                                    Open Map
                                </a>
                            </div>
                            <div class="adetails-top-right-btn-comingsoon">
                                <h3>Coming Soon</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="adetails-bottom-container">
            <div class="adetails-bottom-left">
                <div class="adetails-bottom-left-about">
                    <div class="adetails-name-event">
                        <p>About the Event</p>
                        <div class="adetails-moodeng">
                            <p>Categories: <span class="highlight">Education</span></p>
                        </div>
                        <h3>Event in Naresuan University Presents "<?php echo $event_name ?>"</h3>
                        <p>Open House Faculty of Science</p>
                        <h6>รับชมได้ <span class="highlight">วันที่ <?php echo "$day $month $year"; ?></span> นี้</h6>
                        <div class="adetails-textevent">
                            <p><?php echo $event_detail; ?></p>
                        </div>
                        <div class="adetails-selecteventdata">
                            <p>Select Event Date:</p>
                        </div>
                        <div class="adetails-bottom-mapping">
                            <i class="fas fa-location-dot"></i>
                            <p>Province: Phitsanulok District: Muang</p>
                            <a href="https://www.google.com/maps/place/..." target="_blank"
                                class="adetails-bottommap-link">
                                <!-- Google Maps Link -->
                            </a>
                        </div>
                        <div class="adetails-bottom-box">
                            <div class="adetails-bottom-box-left">
                                <span class="datehighlight"><?php echo $day; ?></span>
                                <br> <?php echo "$month $year"; ?> <br>
                                <?php echo $day_of_week; ?> <?php echo $event_time_formatted; ?> น.
                            </div>
                            <div class="adetails-bottom-box-right">
                                <?php echo $event_name; ?> 
                            </div>
                        </div>
                        <button id="registerevent" class="adetails-bottom-register-btn">Register</button>
                        <div id="ticketPopup" class="adetails-ticket-popup" style="display: none;">
                            <div class="adetails-ticket-popup-content">
                                <span class="adetails-close" onclick="closePopup()">&times;</span>
                                <h2>เลือกบัตร</h2>
                                <div class="adetails-ticket-option">
                                    <p>ราคา: <span id="Pricetic"><?php echo $event_price; ?></span></p>
                                </div>
                                <div class="adetails-ticket-controls">
                                    <button onclick="updateTicketCount('earlyBird', true)">-</button>
                                    <span id="earlyBirdCount">1</span>
                                    <button onclick="updateTicketCount('earlyBird')">+</button>
                                </div>
                                <p>รวมราคา : <span id="totalPrice"><?php echo $event_price; ?></span></p>
                                <button class="adetails-ticket-confirm" onclick="confirmBooking()">จอง</button>
                            </div>
                        </div>
                        <script>
                            document.getElementById("registerevent").onclick = function() {
                                var price = document.getElementById("eventPrice").innerText.trim(); // ดึงราคาจาก element ที่มี id = eventPrice
                                if (price.toLowerCase() == "free" && "<?php echo $eventTypeRe; ?>".toLowerCase() == "yes") {
                                    // ถ้าราคาเป็น "free" ให้ redirect ไปยัง registerForFree.php
                                    window.location.href = "registerForFree.php?event_id=" + <?php echo $event_id; ?>;
                                } else if (price.toLowerCase() == "free" && "<?php echo $eventTypeRe; ?>".toLowerCase() == "no") {
                                    // ถ้า eventTypeRe เป็น "No" ให้ปุ่มกดไม่ได้
                                    var registerButton = document.getElementById("registerevent");
                                    registerButton.disabled = true;
                                    registerButton.style.opacity = "0.5"; // ทำให้ปุ่มดูจางลง
                                    registerButton.style.cursor = "not-allowed"; // เปลี่ยนเมาส์เป็นห้ามคลิก
                                } else {
                                    openPopup()
                                }
                            }
                            document.addEventListener("DOMContentLoaded", function() {
                                var eventTypeRe = "<?php echo isset($eventTypeRe) ? trim($eventTypeRe) : ''; ?>";
                                if (eventTypeRe.toLowerCase() == "No") {
                                    var registerButton = document.getElementById("registerevent");
                                    registerButton.disabled = true;
                                    registerButton.style.opacity = "0.5";
                                    registerButton.style.cursor = "not-allowed";
                                }
                            });

                            let earlyBirdCount = 1;

                            function openPopup() {
                                document.getElementById("ticketPopup").style.display = "flex";
                            }

                            function closePopup() {
                                document.getElementById("ticketPopup").style.display = "none";
                            }

                            function updateTicketCount(type, isSubtract = false) {
                                if (type === 'earlyBird') {
                                    earlyBirdCount = isSubtract ? Math.max(1, earlyBirdCount - 1) : earlyBirdCount + 1;
                                    document.getElementById("earlyBirdCount").innerText = earlyBirdCount;
                                }
                                updateTotalPrice();
                            }

                            function updateTotalPrice() {
                                const earlyBirdPrice = <?php echo $event_price; ?>;
                                const total = (earlyBirdCount * earlyBirdPrice);
                                document.getElementById("totalPrice").innerText = `${total.toFixed(2)}`;
                            }

                            function confirmBooking() {
                                const eventID = <?php echo $event_id; ?>;
                                const Audience_email = "<?php echo $email; ?>";
                                const earlyBirdCount = parseInt(document.getElementById("earlyBirdCount").innerText);
                                const totalPrice = parseFloat(document.getElementById("totalPrice").innerText);

                                console.log("🔹 Event ID:", eventID);
                                console.log("🔹 Tickets:", earlyBirdCount);
                                console.log("🔹 Total Price:", totalPrice);
                                console.log("🔹 Email:", Audience_email);

                                fetch("save_booking.php", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: `event_id=${eventID}&email=${Audience_email}&ticket_count=${earlyBirdCount}&ticket_totalprice=${totalPrice}`
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        console.log("🔹 Server Response:", data);
                                        if (data.success) {
                                            alert("✅ การจองสำเร็จ กรุณาจ่ายเงินภายในเวลา 10 นาที");
                                            window.location.href = "buy.php";
                                        } else {
                                            alert("❌ เกิดข้อผิดพลาด: " + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error:", error);
                                        alert("❌ ไม่สามารถจองได้ กรุณาลองใหม่อีกครั้ง");
                                    });
                            }

                            function shareEvent() {
                                alert("แชร์อีเว้นท์!");
                            }

                            function copyLink() {
                                alert("คัดลอกลิงค์เรียบร้อยแล้ว!");
                            }

                            function toggleFavorite(eventID, email) {
                                const star = document.getElementById("star-icon");
                                const action = star.classList.contains("far") ? "add" : "remove"; // ตรวจสอบสถานะดาว

                                console.log("action:", action); // เพิ่มบรรทัดนี้เพื่อตรวจสอบ
                                console.log("eventID:", eventID, "email:", email); // เพิ่มบรรทัดนี้เพื่อตรวจสอบ

                                fetch("favorite_action.php", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: `action=${action}&event_id=${eventID}&email=${email}`
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            if (action === "add") {
                                                star.classList.remove("far");
                                                star.classList.add("fas");
                                                alert("Favorite แล้ว!");
                                            } else {
                                                star.classList.remove("fas");
                                                star.classList.add("far");
                                                alert("ยกเลิก Favorite แล้ว!");
                                            }
                                        } else {
                                            alert("เกิดข้อผิดพลาด: " + data.message);
                                        }
                                    })
                                    .catch(error => console.error("Error:", error));
                            }
                        </script>
                        <button id="registerevent" class="adetails-bottom-register-btn" style="margin-top: 20px;"
                            onclick="window.location.href='openlayout.html';">Layout</button>
                        <div class="adetails-back-link"><a href="javascript:history.back()">
                                < Back</a>
                        </div>
                    </div>
                    <?php
                    $today = date('Y-m-d');
                    // ถ้า Event_Date <= วันปัจจุบัน ให้แสดงรีวิว
                    if ($event_date <= $today) :
                    ?>
                        <div class="adetails-review-container">
                            <div class="adetails-result-star-detail">
                                <div class="adetails-star-box">
                                    <i class="fas fa-star"></i>
                                    <span class="adetails-rating-value">Comment(<?php echo count($feedbacks); ?>)</span>
                                    <span class="adetails-rating-text"></span>
                                    <div class="adetails-rating-actions">
                                        <a href="feedback.php?event_id=<?php echo $event_id; ?>" class="adetails-rating-comment">></a>
                                    </div>
                                </div>
                            </div>
                            <div class="adetails-box-audience-review">
                                <div class="adetails-user-info">
                                    <img src="../assets/imgs/jusmine.png" alt="User Profile" class="adetails-user-profile">
                                    <span class="adetails-user-name"><?php echo $first_name; ?></span>
                                </div>
                                <div class="adetails-rating-stars">
                                    <span class="aFeedback-star" onclick="rateComment(1)"><i class="fas fa-star"></i></span>
                                    <span class="aFeedback-star" onclick="rateComment(2)"><i class="fas fa-star"></i></span>
                                    <span class="aFeedback-star" onclick="rateComment(3)"><i class="fas fa-star"></i></span>
                                    <span class="aFeedback-star" onclick="rateComment(4)"><i class="fas fa-star"></i></span>
                                    <span class="aFeedback-star" onclick="rateComment(5)"><i class="fas fa-star"></i></span>
                                </div>
                                <div class="aDetails-user-comment-section">
                                    <textarea id="mainComment" placeholder="แสดงความคิดเห็น..." readonly></textarea>
                                    <a href="feedback.php?event_id=<?php echo $event_id; ?>" id="addCommentLink" class="comment-button">เพิ่มคอมเมนต์</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            document.getElementById("popupNavbar").addEventListener("keyup", function(e) {
                let searchQuery = e.target.value;
                fetch("search_events.php", {
                        method: "POST",
                        body: JSON.stringify({
                            searchQuery: searchQuery
                        }),
                        headers: {
                            "Content-Type": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        displayEvents(data.events);
                    })
                    .catch(error => console.error("Error fetching events:", error));
            });

            function displayEvents(events) {
                let eventList = document.querySelector(".navbar-event-list");
                eventList.innerHTML = ""; // Clear previous results

                if (events.length > 0) {
                    events.forEach(event => {
                        let eventCard = `
                    <div class="navbar-event-card">
                        <img src="${event.event_pictures}" alt="${event.event_names}">
                        <div class="navbar-event-info">
                            <p class="navbar-event-date"><i class="bi bi-calendar"></i> ${event.event_dates}</p>
                            <h4>${event.event_names}</h4>
                            <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ${event.event_locations}</p>
                            <a href="details.php?event_id=${event.event_ids}">
                                <button class="navbar-event-button ${event.button_class}">${event.button_text}</button>
                            </a>
                        </div>
                    </div>
                `;
                        eventList.innerHTML += eventCard;
                    });
                } else {
                    eventList.innerHTML = "<p>No events found.</p>";
                }
            }
        </script>

        <!-- core  -->
        <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
        <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
        <script src="../assets/js/navbar.js"></script>

        <!-- bootstrap 3 affix -->
        <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

        <!-- Meyawo js -->
        <script src="../assets/js/meyawo.js"></script>
        <script src="../assets/js/comment.js"></script>

</body>

</html>