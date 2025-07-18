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

// Fetch orders from the database
$eventId = $_GET['event_id']; // รับค่า eventId จาก URL
$query = "SELECT r.*, e.event_date, e.event_time, e.event_picture, b.booking_id, b.ticket_count, b.ticket_totalprice, b.booking_receipt, a.audience_FirstName AS buyer_name, a.StudentID, a.audience_email AS buyer_email 
FROM register r 
JOIN event e ON r.EventID = e.EventID 
JOIN booking b ON r.booking_id = b.booking_id 
JOIN audience a ON r.audience_email = a.audience_email 
WHERE r.EventID = ? AND r.register_status = 'verifying'";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("s", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$query = "SELECT r.*, e.event_date, e.event_time, e.event_picture, b.booking_id, b.ticket_count, b.ticket_totalprice, b.booking_receipt, a.audience_FirstName AS buyer_name, a.StudentID, a.audience_email AS buyer_email 
FROM register r 
JOIN event e ON r.EventID = e.EventID 
JOIN booking b ON r.booking_id = b.booking_id 
JOIN audience a ON r.audience_email = a.audience_email 
WHERE r.EventID = ? AND r.register_status = 'completed'";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("s", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$complete = [];

while ($row = $result->fetch_assoc()) {
    $complete[] = $row;
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
                            <p class="m-0">
                                <?php echo htmlspecialchars($user['first_name']); ?><br><small><?php echo htmlspecialchars($user['studentaffairs_email']); ?></small>
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

    <div class="allOrder2-setting-container">
        <div class="allOrder2-setting-container-data">
            <div class="allOrder2-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="allOrder2-profile-img"></div>
                <h2><?php echo htmlspecialchars($user['first_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['studentaffairs_email']); ?></p>
            </div>

            <div class="allOrder2-setting-menu">
                <a href="eventAll.php" class="allOrder2-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="allOrder2-setting-menu-item active"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="allOrder2-setting-menu-item"><i class="bi bi-clipboard2-plus"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="allOrder2-setting-menu-item"><i class="bi bi-person-plus"></i>
                    คำขอจัดอีเว้นท์</a>
                <a href="setting.php" class="allOrder2-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="allOrder2-setting-menu-item allOrder2-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="allOrder2-container">
            <div class="allOrder2-back-button-container">
                <div class="allOrder2-back-button" onclick="goBack()">
                    <i class="bi bi-chevron-left"></i> คำสั่งซื้อทั้งหมด
                </div>
            </div>
            <div class="allOrder2-name">
                <div class="allOrder2-setting-navbar">
                    <span id="waiting" class="active" onclick="changeTab('waiting')">คำสั่งที่รอการอนุมัติ</span>
                    <span id="approve" onclick="changeTab('approve')">คำสั่งซื้อที่อนุมัติเรียบร้อยแล้ว</span>
                </div>
            </div>

            <div class="allOrder2-content">
                <div id="waitingContent" class="allOrder2-tab-content active"></div>
                <div id="approveContent" class="allOrder2-tab-content">
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/navbar.js"></script>
    <script>
        //รอตรวจสอบ
        function waitingOpenPopup(eventId) {
            var popupElement = document.getElementById('waitingpopup-' + eventId);
            if (popupElement) {
                popupElement.style.display = 'block';
            }
        }

        function waitingClosePopup(eventId) {
            var popupElement = document.getElementById('waitingpopup-' + eventId);
            if (popupElement) {
                popupElement.style.display = 'none';
            }
        }

        function waitingApproveOrder(eventId) {
            alert("คำสั่งซื้อ " + eventId + " ได้รับการอนุมัติ");

            waitingClosePopup(eventId);
        }

        function createEvent(eventId, eventDate, eventTime, ticketCount, totalPrice, buyerName, studentId, buyerEmail, imgUrl, evidenceImgUrl) {
            var eventContainer = document.createElement('div');
            eventContainer.className = 'waiting-setting-container-event d-flex align-items-center p-3';
            eventContainer.setAttribute('data-id', eventId);

            var eventDateContainer = document.createElement('div');
            eventDateContainer.className = 'waiting-event-date text-center me-3';
            eventDateContainer.innerHTML = `
        <p class="mb-0">${eventDate}</p>
        <p class="mb-0">${eventTime}</p>
    `;

            var eventDetailsContainer = document.createElement('div');
            eventDetailsContainer.className = 'waiting-details-container-price d-flex justify-content-between align-items-center';
            eventDetailsContainer.innerHTML = `
        <p class="waiting-details mb-0"><i class="bi bi-ticket-perforated"></i> บัตร x ${ticketCount}</p>
        <p class="waiting-total-price mb-0">(ยอดเงิน : ${totalPrice} ฿)</p>
    `;

            var profileImgContainer = document.createElement('div');
            profileImgContainer.className = 'waiting-profile-img';
            profileImgContainer.innerHTML = `<img src="${imgUrl}" alt="Profile Image">`;

            var eventDetails = document.createElement('div');
            eventDetails.className = 'waiting-event-details';
            eventDetails.style.height = '100%';
            eventDetails.innerHTML = `
        <h2>ชื่อผู้ซื้อ : ${buyerName}</h2>
        <div class="waiting-d-flex">
            <div class="waiting-details-container">
                <p class="waiting-buyer-id mb-0">รหัสนิสิต: ${studentId}</p>
                <p class="waiting-buyer-email mb-0">Email: ${buyerEmail}</p>
            </div>
            <button class="waiting-btn-success" onclick="waitingOpenPopup('${eventId}')">ตรวจสอบหลักฐานการโอน</button>
            <div id="waitingpopup-${eventId}" class="waiting-popup-container" style="display: none;">
                <div class="waiting-popup-content">
                    <img src="../receipt/${evidenceImgUrl}" alt="หลักฐานการโอน">
                    <div class="waiting-popup-actions">
                        <button class="waiting-cancel-btn" onclick="waitingClosePopup('${eventId}')">ยกเลิก</button>
                        <button class="waiting-approve-btn" onclick="waitingApproveOrder('${eventId}')">อนุมัติ</button>
                    </div>
                </div>
            </div>
        </div>
    `;
            console.log("../receipt/" + evidenceImgUrl);
            eventContainer.appendChild(eventDateContainer);
            eventContainer.appendChild(eventDetailsContainer);
            eventContainer.appendChild(profileImgContainer);
            eventContainer.appendChild(eventDetails);

            document.getElementById('waitingContent').appendChild(eventContainer);
        }

        <?php foreach ($orders as $order) { ?>
            createEvent(
                '<?php echo htmlspecialchars($order['register_id']); ?>',
                '<?php echo htmlspecialchars($order['event_date']); ?>',
                '<?php echo htmlspecialchars($order['event_time']); ?>',
                '<?php echo htmlspecialchars($order['ticket_count']); ?>',
                '<?php echo htmlspecialchars($order['ticket_totalprice']); ?>',
                '<?php echo htmlspecialchars($order['buyer_name']); ?>',
                '<?php echo htmlspecialchars($order['StudentID']); ?>',
                '<?php echo htmlspecialchars($order['buyer_email']); ?>',
                '<?php echo htmlspecialchars($order['event_picture']); ?>',
                '<?php echo htmlspecialchars($order['booking_receipt']); ?>'
            );
        <?php } ?>



        //ตรวจสอบแล้ว
        function createApproveContent(date, profileImg, buyerName, studentId, buyerEmail, tickets, total, status) {
            const settingContainer = document.createElement('div');
            settingContainer.className = 'approve-setting-container-event d-flex align-items-center p-3';

            const eventDate = document.createElement('div');
            eventDate.className = 'approve-event-date text-center me-3';
            eventDate.innerHTML = `
                <p class="mb-0">${date.day}</p>
                <p class="mb-0">${date.month} ${date.year}</p>
                <p class="mb-0">${date.time}</p>
            `;

            const profileImgDiv = document.createElement('div');
            profileImgDiv.className = 'approve-profile-img';
            const img = document.createElement('img');
            img.src = profileImg;
            img.alt = 'Profile Image';
            profileImgDiv.appendChild(img);

            const eventDetails = document.createElement('div');
            eventDetails.className = 'approve-event-details';
            eventDetails.innerHTML = `
                <h2>ชื่อผู้ซื้อ : ${buyerName}</h2>
                <div class="approve-details-container">
                    <p class="approve-buyer-id mb-0">รหัสนิสิต: ${studentId}</p>
                    <p class="approve-buyer-email mb-0">Email: ${buyerEmail}</p>  
                </div>
                <div class="approve-event-summary">
                    <p><i class="bi bi-ticket-perforated"></i> บัตร x ${tickets}</p>
                    <p class="approve-event-total">Total: ${total}฿</p>
                </div>
            `;

            const statusDiv = document.createElement('div');
            statusDiv.className = 'approve-status';
            statusDiv.innerHTML = `<p class="approve-order-status">${status}</p>`;

            settingContainer.appendChild(eventDate);
            settingContainer.appendChild(profileImgDiv);
            settingContainer.appendChild(eventDetails);
            settingContainer.appendChild(statusDiv);

            return settingContainer;
        }

        const dateInfo = {
            day: '15',
            month: 'ธ.ค.',
            year: '2024',
            time: '12:00'
        };
        const buyers = [
            <?php foreach ($complete as $order) { ?> {
                    profileImg: '<?php echo htmlspecialchars($order['event_picture']); ?>',
                    name: '<?php echo htmlspecialchars($order['buyer_name']); ?>',
                    id: '<?php echo htmlspecialchars($order['StudentID']); ?>',
                    email: '<?php echo htmlspecialchars($order['buyer_email']); ?>',
                    tickets: '<?php echo htmlspecialchars($order['ticket_count']); ?>',
                    total: '<?php echo htmlspecialchars($order['ticket_totalprice']); ?>',
                    status: 'อนุมัติเรียบร้อยแล้ว'
                },
            <?php } ?>

        ];

        const approveContentDiv = document.getElementById('approveContent');
        buyers.forEach(buyer => {
            const buyerInfo = createApproveContent(dateInfo, buyer.profileImg, buyer.name, buyer.id, buyer.email, buyer.tickets, buyer.total, buyer.status);
            approveContentDiv.appendChild(buyerInfo);
        });

        //
        function changeTab(tabName) {
            const tabs = document.querySelectorAll('.allOrder2-setting-navbar span');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            const activeTab = document.getElementById(tabName);
            activeTab.classList.add('active');

            const tabContents = document.querySelectorAll('.allOrder2-tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            const activeContent = document.getElementById(tabName + 'Content');
            activeContent.classList.add('active');
        }

        //pop up
        function waitingOpenPopup(eventId) {
            console.log("Approving event:", eventId);
            document.getElementById(`waitingpopup-${eventId}`).style.display = "flex";
        }

        function waitingClosePopup(eventId) {
            document.getElementById(`waitingpopup-${eventId}`).style.display = "none";
        }

        function waitingApproveOrder(eventId) {
            if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการอนุมัติรายการนี้?")) {
                return;
            }

            fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `event_id=${eventId}&status=completed`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("อนุมัติสำเร็จ");
                        location.reload();
                    } else {
                        alert("เกิดข้อผิดพลาด: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์");
                });
        }



        function goBack() {
            window.history.back();
        }
    </script>
</body>

</html>