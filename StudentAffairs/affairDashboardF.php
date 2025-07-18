<?php
session_start();
include '../db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Event not found!";
    exit();
}
$email = $_SESSION['email'];
$event_id = $_GET['event_id']; // รับ event_id จาก URL

// ดึงข้อมูลผู้ใช้
$sql = "SELECT first_name, last_name FROM StudentAffairs WHERE StudentAffairs_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
} else {
    echo "User not found!";
    exit();
}
$stmt->close();

// ดึงข้อมูลการลงทะเบียน
$sql = "
    SELECT 
        r.register_id, 
        r.Audience_email, 
        r.EventID,
        r.booking_id,
        DATE_FORMAT(r.register_date, '%Y-%m-%d') AS register_date, 
        r.register_status,
        e.EventID,
        e.Event_Name, 
        e.Event_Price, 
        e.Event_Date, 
        e.Event_Time, 
        e.Event_Location, 
        e.Event_Picture,
        e.Event_Detail, 
        IFNULL(b.ticket_count, 0) AS ticket_count,  
        IFNULL(b.ticket_totalprice, 0) AS ticket_totalprice,  
        b.booking_receipt,
        a.Audience_email,
        a.Audience_FirstName,
        a.Audience_LastName,
        a.FacultyID,
        f.Faculty_Name,
        a.MajorID,
        m.Major_Name,
        a.GenderID,
        g.GenderType, 
        a.Audience_Role
    FROM register r
    LEFT JOIN Event e ON r.EventID = e.EventID
    LEFT JOIN booking b ON r.booking_id = b.booking_id
    LEFT JOIN Audience a ON r.Audience_email = a.Audience_email
    LEFT JOIN Faculty f ON a.FacultyID = f.FacultyID
    LEFT JOIN Major m ON a.MajorID = m.MajorID
    LEFT JOIN Gender g ON a.GenderID = g.GenderID
    WHERE e.EventID = ?;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$registers[$event_id] = []; // ให้แน่ใจว่าเป็นอาร์เรย์ก่อน

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event_date_formatted = $row['Event_Date'] ? date("d F Y", strtotime($row['Event_Date'])) : "No date available";
        $event_time_formatted = $row['Event_Time'] ? date("H:i", strtotime($row['Event_Time'])) : "No time available";
        $price_display = ($row['Event_Price'] == 0) ? "Free" : $row['Event_Price'];

        // เก็บข้อมูลเป็น array ของหลายรายการ
        $registers[$event_id][] = [
            'event_id' => $row['EventID'],
            'register_id' => $row['register_id'],
            'event_name' => $row['Event_Name'],
            'event_date' => $row['Event_Date'],
            'event_time' => $row['Event_Time'],
            'event_date_formatted' => $event_date_formatted,
            'event_time_formatted' => $event_time_formatted,
            'event_location' => $row['Event_Location'],
            'event_detail' => $row['Event_Detail'],
            'event_picture' => $row['Event_Picture'],
            'event_price' => $row['Event_Price'],
            'price_display' => $price_display,
            'ticket_count' => $row['ticket_count'],
            'register_status' => $row['register_status'],
            'ticket_totalprice' => $row['ticket_totalprice'],
            'booking_id' => $row['booking_id'],
            'booking_receipt' => $row['booking_receipt'],
            'audience_email' => $row['Audience_email'],
            'audience_first_name' => $row['Audience_FirstName'],
            'audience_last_name' => $row['Audience_LastName'],
            'audience_role' => $row['Audience_Role'],
            'faculty_name' => $row['Faculty_Name'],
            'major_name' => $row['Major_Name'],
            'gender' => $row['GenderType'],
        ];
    }
}
// ส่งข้อมูลไปยัง JavaScript
echo "<script>";
echo "var registers = " . json_encode($registers[$event_id]) . ";";  // ส่งข้อมูลจาก PHP ไปยัง JavaScript
echo "</script>";

// ตรวจสอบว่ามีข้อมูล event_id หรือไม่
if (!isset($registers[$event_id]) || !is_array($registers[$event_id])) {
    $total_participants = $students = $lecturers = $guests = 0;
} else {
    // คำนวณจำนวนคนทั้งหมด
    $total_participants = count($registers[$event_id]);

    // คำนวณตาม audience_role
    $students = count(array_filter($registers[$event_id], function ($r) {
        return $r['audience_role'] === 'Student';
    }));
    $lecturers = count(array_filter($registers[$event_id], function ($r) {
        return $r['audience_role'] === 'Lecturer';
    }));
    $guests = count(array_filter($registers[$event_id], function ($r) {
        return $r['audience_role'] === 'Guest User';
    }));
}

$sql = "SELECT * 
        FROM favorite f
        INNER JOIN event e ON f.EventId = e.EventID
        WHERE f.EventID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id); // ส่ง event_id ไปใน query
$stmt->execute();
$result = $stmt->get_result();
$events = [];

if ($result->num_rows > 0) {
    // ใช้ while loop เพื่อดึงข้อมูลทุกแถวจากผลลัพธ์
    while ($row = $result->fetch_assoc()) {
        // ดึงข้อมูลของ event
        $event_pictures = $row['Event_Picture'];
        $event_names = $row['Event_Name'];
        $event_dates = $row['Event_Date'];
        $event_times = $row['Event_Time'];
        $event_locations = $row['Event_Location'];
        $event_prices = $row['Event_Price'];
        $event_details = $row['Event_Detail'];
        $event_ids = $row['EventID'];
        $favorite_id = $row['favorite_id']; // ดึง Favorite_id จากตาราง favorite

        // ตรวจสอบว่า event_price เป็น 0 หรือไม่
        if ($event_prices == 0) {
            $price_displays = "Free";
        } else {
            $price_displays = $event_prices;
        }

        // แปลงวันที่เป็นรูปแบบที่ต้องการ
        $event_dates_formatted = date("d F Y", strtotime($event_dates));  // วันที่ในรูปแบบ "01 January 2025"
        $event_times_formatted = date("H:i", strtotime($event_times));  // เวลาในรูปแบบ "18:00"

        // แปลงวันที่เป็น พ.ศ.
        $eventss[] = [
            'pictures' => $event_pictures,
            'names' => $event_names,
            'dates_formatted' => $event_dates_formatted,
            'times_formatted' => $event_times_formatted,
            'locations' => $event_locations,
            'price_displays' => $price_displays,
            'ids' => $event_ids,
            'favorite_id' => $favorite_id,
        ];
    }
}

// คำสั่ง SQL เพื่อดึงจำนวนผู้ที่ถูกใจแยกตามประเภท
$sql_student = "SELECT COUNT(*) AS student_count FROM favorite f
                LEFT JOIN Audience a ON f.Audience_email = a.Audience_email
                WHERE f.EventID = ? AND a.Audience_role = 'Student'";

$sql_lecturer = "SELECT COUNT(*) AS lecturer_count FROM favorite f
                 LEFT JOIN Audience a ON f.Audience_email = a.Audience_email
                 WHERE f.EventID = ? AND a.Audience_role = 'Lecturer'";

$sql_guest = "SELECT COUNT(*) AS guest_count FROM favorite f
              LEFT JOIN Audience a ON f.Audience_email = a.Audience_email
              WHERE f.EventID = ? AND a.Audience_role = 'Guest User'";

// คำนวณจำนวนผู้ที่ถูกใจทั้งหมด
$sql_total = "SELECT COUNT(*) AS total_count FROM favorite f
              WHERE f.EventID = ?";

// Prepare และ execute แต่ละคำสั่ง
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $event_id);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_count = $total_result->fetch_assoc()['total_count'];

$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $event_id);
$stmt_student->execute();
$student_result = $stmt_student->get_result();
$student_count = $student_result->fetch_assoc()['student_count'];

$stmt_lecturer = $conn->prepare($sql_lecturer);
$stmt_lecturer->bind_param("i", $event_id);
$stmt_lecturer->execute();
$lecturer_result = $stmt_lecturer->get_result();
$lecturer_count = $lecturer_result->fetch_assoc()['lecturer_count'];

$stmt_guest = $conn->prepare($sql_guest);
$stmt_guest->bind_param("i", $event_id);
$stmt_guest->execute();
$guest_result = $stmt_guest->get_result();
$guest_count = $guest_result->fetch_assoc()['guest_count'];

// ส่งค่าจำนวนที่ได้ไปยัง JavaScript
echo "<script>
        document.getElementById('totalInterested').innerText = $total_count;
        document.getElementById('studentInterested').innerText = $student_count;
        document.getElementById('lecturerInterested').innerText = $lecturer_count;
        document.getElementById('guestInterested').innerText = $guest_count;
      </script>";

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
        // คำนวณคะแนนดาวจาก feedback_point
        $rating = 0;
        if ($feedback_row['feedback_point'] >= 15) {
            $rating = 5;
        } elseif ($feedback_row['feedback_point'] >= 12) {
            $rating = 4;
        } elseif ($feedback_row['feedback_point'] >= 9) {
            $rating = 3;
        } elseif ($feedback_row['feedback_point'] >= 6) {
            $rating = 2;
        } else {
            $rating = 1;
        }

        // เก็บข้อมูล feedback และคะแนนดาว
        $feedbacks[] = [
            "username" => $feedback_row['Audience_FirstName'] . " " . $feedback_row['Audience_LastName'],
            "profilePic" => "../assets/imgs/jusmine.png", // ปรับตามเส้นทางของรูปภาพโปรไฟล์
            "rating" => $rating,
            "options" => json_decode($feedback_row['feedback_option'], true),
            "comment" => $feedback_row['feedback_comment'],
        ];
    }
}
// แปลง array ของ feedbacks เป็น JSON และส่งไปยัง JavaScript
$feedbacks_json = json_encode($feedbacks, JSON_UNESCAPED_UNICODE);


// ดึงข้อมูลคณะที่มีอยู่ในระบบ
$sql = "SELECT * FROM Faculty";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$faculties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $faculties[] = [
            'faculty_id' => $row['FacultyID'],
            'faculty_name' => $row['Faculty_Name'],
        ];
    }
} else {
    echo "No faculties found.";
    exit();
}

// คำนวณจำนวนผู้เข้าร่วมในแต่ละคณะ
$faculty_counts = [];
foreach ($faculties as $faculty) {
    $faculty_counts[$faculty['faculty_name']] = 0; // เริ่มต้นที่ 0
}

// คำนวณจำนวนผู้เข้าร่วมในแต่ละคณะจากข้อมูลการลงทะเบียน
foreach ($registers[$event_id] as $register) {
    $faculty_name = $register['faculty_name']; // ค้นหาคณะจากข้อมูลผู้ลงทะเบียน
    if (isset($faculty_counts[$faculty_name])) {
        $faculty_counts[$faculty_name]++;
    }
}

// เตรียมข้อมูลที่ส่งไปให้ JavaScript (กรองเฉพาะคณะที่มีผู้เข้าร่วม)
$faculties_js = array_map(function ($faculty) use ($faculty_counts) {
    $value = $faculty_counts[$faculty['faculty_name']] ?? 0;
    if ($value > 0) { // เฉพาะคณะที่มีคน
        return [
            'name' => $faculty['faculty_name'],
            'value' => $value,
            'color' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.6)', // สุ่มสี
            'border' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 1)' // สุ่มสี
        ];
    }
    return null; // คณะที่ไม่มีผู้เข้าร่วมจะถูกข้าม
}, $faculties);

// กรองค่า null ออกจากอาร์เรย์
$faculties_js = array_filter($faculties_js, function ($faculty) {
    return $faculty !== null;  // กรองออกค่าที่เป็น null
});

// รีเซ็ตดัชนีของอาร์เรย์
$faculties_js = array_values($faculties_js);

// แปลงเป็น JSON
$json_faculties_js = json_encode($faculties_js);

// ตรวจสอบข้อผิดพลาดจาก json_encode()
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON encode error: " . json_last_error_msg();
} else {
    // ใช้ $json_faculties_js ที่แปลงสำเร็จ
    //echo $json_faculties_js;
}

// ดึงข้อมูลจากฐานข้อมูลเกี่ยวกับเพศ (Gender)
$sql = "SELECT * FROM Gender";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$genders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $genders[] = [
            'gender_id' => $row['GenderID'],
            'gender_type' => $row['GenderType'],
        ];
    }
} else {
    echo "No genders found.";
    exit();
}

// คำนวณจำนวนผู้เข้าร่วมตามเพศ
$gender_counts = ['male' => 0, 'female' => 0, 'other' => 0];

// ใช้ข้อมูลจาก register เพื่อคำนวณจำนวนเพศ
foreach ($registers[$event_id] as $register) {
    $gender = strtolower($register['gender']);  // แปลงเป็นตัวพิมพ์เล็ก

    // ตรวจสอบว่าเพศที่ได้เป็นหนึ่งในค่า 'male', 'female', หรือ 'other'
    if (isset($gender_counts[$gender])) {
        $gender_counts[$gender]++;
    } else {
        // ถ้าไม่ใช่ 'male' หรือ 'female' ให้ถือว่าเป็น 'other'
        $gender_counts['other']++;
    }
}

// ส่งข้อมูลไปยัง JavaScript
echo "<script>";
echo "var genderData = " . json_encode($gender_counts) . ";";  // ส่งข้อมูลจาก PHP ไปยัง JavaScript
echo "</script>";
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- font icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css" />
    <link rel="stylesheet" href="../assets/vendors/themify-icons/css/themify-icons.css" />
    <!-- meyao -->
    <link rel="stylesheet" href="../assets/css/meyawo.css" />
    <!-- font-awesome icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Bootstrap icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />
    <!-- Material icons-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <input type="text" class="form-control" placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน" />
                </div>
                <button class="navbar-close-popup" id="closePopupNavbar">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="navbar-popup-overlay" id="popupOverlay"></div>

            <div class="navbar-popup" id="searchPopup">
                <div class="navbar-popup-header-recommend">
                    <h3>แนะนำ</h3>
                </div>
                <div class="navbar-popup-content">
                    <div class="navbar-event-list">
                        <div class="navbar-event-card">
                            <img src="../assets/imgs/bf.png" alt="Music Festival" />
                            <div class="navbar-event-info">
                                <p class="navbar-event-date">
                                    <i class="bi bi-calendar"></i> 08 มี.ค. 2025
                                </p>
                                <h4>NU Book Fair</h4>
                                <p class="navbar-event-location">
                                    <i class="bi bi-geo-alt"></i> THE PIRATES PARK HATYAI
                                </p>
                                <button class="navbar-event-button">ดูรายละเอียด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/bf.png" alt="Music Festival" />
                            <div class="navbar-event-info">
                                <p class="navbar-event-date">
                                    <i class="bi bi-calendar"></i> 08 มี.ค. 2025
                                </p>
                                <h4>NU Book Fair</h4>
                                <p class="navbar-event-location">
                                    <i class="bi bi-geo-alt"></i> THE PIRATES PARK HATYAI
                                </p>
                                <button class="navbar-event-button">ดูรายละเอียด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/artyoung.jpg" alt="Northern Fest" />
                            <div class="navbar-event-info">
                                <p class="navbar-event-date">
                                    <i class="bi bi-calendar"></i> 01 ก.พ. 2025
                                </p>
                                <h4>ศิลป์เสมอ</h4>
                                <p class="navbar-event-location">
                                    <i class="bi bi-geo-alt"></i> ขนส่ง 3
                                </p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/firststage.jpg" alt="Northern Fest" />
                            <div class="navbar-event-info">
                                <p class="navbar-event-date">
                                    <i class="bi bi-calendar"></i> 01 ก.พ. 2025
                                </p>
                                <h4>First Stage</h4>
                                <p class="navbar-event-location">
                                    <i class="bi bi-geo-alt"></i> ขนส่ง 3
                                </p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/artyoung.jpg" alt="Northern Fest" />
                            <div class="navbar-navbar-event-info">
                                <p class="navbar-event-date">
                                    <i class="bi bi-calendar"></i> 01 ก.พ. 2025
                                </p>
                                <h4>ศิลป์เสมอ</h4>
                                <p class="navbar-event-location">
                                    <i class="bi bi-geo-alt"></i> ขนส่ง 3
                                </p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="navbar-search-container">
                <input type="text" class="form-control navbar-search-bar"
                    placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน" id="searchInput" readonly />
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
                    <hr />
                    <div class="notification-empty">
                        <i class="bi bi-envelope" style="font-size: 50px; color: #666"></i>
                        <p>ไม่มีการแจ้งเตือน</p>
                        <small>ขณะนี้ยังไม่มีการแจ้งเตือนถึงคุณ</small>
                    </div>
                </div>
                <button class="user-button" id="userButton">
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img" />
                    <span><?php echo $first_name; ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img" />
                        <div>
                            <p class="m-0">
                                <?php echo $first_name; ?><br /><small><?php echo $email; ?></small>
                            </p>
                        </div>
                    </div>
                    <hr />
                    <a href="eventAll.php">อีเว้นท์ทั้งหมด</a>
                    <a href="allOrder.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="addEvent.php">เพิ่มอีเว้นท์</a>
                    <a href="changeRole.php">เปลี่ยนโรล</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr />
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="eventDash-container" style="margin-top: 5px">
        <h1 style="margin-top: 10px">ชื่ออีเว้นท์ : <span id="eventTitle"><?php echo $eventss[0]['names'] ?></span></h1>
        <div class="eventDash-section">
            <h2 style="font-size: 20px;">สถิติผู้ที่ถูกใจ</h2>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="totalInterested"><?php echo $total_count; ?></h3>
                    <p>ทั้งหมด</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="studentInterested"><?php echo $student_count; ?></h3>
                    <p>นิสิต</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="lecturerInterested"><?php echo $lecturer_count; ?></h3>
                    <p>อาจารย์</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="guestInterested"><?php echo $guest_count; ?></h3>
                    <p>บุคคลทั่วไป</p>
                </div>
            </div>
        </div>
        <div class="eventDash-affairsdashboard-section3">
            <h3>ความคิดเห็นจากผู้เข้าร่วม</h3>
            <label for="filterStars">กรองตามดาว:</label>
            <select id="filterStars">
                <option value="all">แสดงทั้งหมด</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="2">⭐⭐</option>
                <option value="1">⭐</option>
            </select>
            <div id="eventDash-commentsContainer"></div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const feedbacks = <?php echo $feedbacks_json; ?>; // รับค่าจาก PHP

            function generateStars(starCount) {
                let starsHTML = "";
                for (let i = 1; i <= 5; i++) {
                    if (i <= starCount) {
                        starsHTML += '<i class="fas fa-star"></i>';
                    } else {
                        starsHTML += '<i class="far fa-star"></i>';
                    }
                }
                return starsHTML;
            }

            function displayComments(filter = "all") {
                const container = document.getElementById("eventDash-commentsContainer");
                container.innerHTML = "";

                const filteredComments =
                    filter === "all" ?
                    feedbacks :
                    feedbacks.filter((comment) => comment.rating === parseInt(filter));

                if (filteredComments.length === 0) {
                    container.innerHTML =
                        '<p style="text-align: center; color: #ffa726">ไม่มีคอมเมนต์ที่ตรงกับจำนวนดาวนี้</p>';
                    return;
                }

                // วนลูปแสดงคอมเมนต์
                filteredComments.forEach((comment) => {
                    const commentBox = document.createElement("div");
                    commentBox.classList.add("aFeedback-comment-box");

                    // ตรวจสอบว่ามี options หรือไม่ แล้วแปลงเป็นข้อความ HTML
                    const optionsText = comment.options ?
                        Object.entries(comment.options)
                        .map(([key, value]) => `${key}: ${value}`)
                        .join("<br>") :
                        "";

                    commentBox.innerHTML = `
            <img src="${comment.profilePic}" alt="Profile Picture" class="aFeedback-profile-pic">
            <div class="aFeedback-comment-content">
                <span class="aFeedback-username">${comment.username}</span>
                <div class="aFeedback-rating">
                    ${generateStars(comment.rating)}
                </div>
                <div class="aFeedback-comment-text-user">
                    ${optionsText}
                    <div class="aFeedback-comment-bubble">${comment.comment}</div>
                </div>
            </div>
            `;

                    container.appendChild(commentBox);
                });
            }

            // เรียกใช้ฟังก์ชันแสดงคอมเมนต์ตามค่าเริ่มต้น
            displayComments();

            // กรองตามดาว
            document.getElementById("filterStars").addEventListener("change", function() {
                displayComments(this.value);
            });
        });
    </script>
    <script src="../assets/js/navbar.js"></script>
</body>

</html>