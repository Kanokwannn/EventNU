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
$stmt = $conn->prepare("SELECT request_event.*, audience.audience_email, audience.Audience_Role, audience.Audience_FirstName FROM request_event LEFT JOIN audience ON request_event.Audience_email = audience.Audience_email WHERE audience.audience_email IS NOT NULL");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    echo "Error preparing statement: " . htmlspecialchars($conn->error);
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
                    <a href="changeRole.php">ขอจัดอีเว้นท์</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="changeRole-setting-container">
        <div class="changeRole-setting-container-data">
            <div class="changeRole-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="changeRole-profile-img"></div>
                <h2><?php echo htmlspecialchars($user['first_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['studentaffairs_email']); ?></p>
            </div>

            <div class="changeRole-setting-menu">
                <a href="eventAll.php" class="changeRole-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="changeRole-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="changeRole-setting-menu-item"><i class="bi bi-clipboard2-plus"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="changeRole-setting-menu-item active"><i
                        class="bi bi-person-plus-fill"></i>
                    ขอจัดอีเว้นท์</a>
                <a href="setting.php" class="changeRole-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="changeRole-setting-menu-item changeRole-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="changeRole-container">
            <div class="changeRole-name">
                <h3>ขอจัดอีเว้นท์</h3>
                <div class="changeRole-setting-navbar">
                    <span id="pending" class="active" onclick="changeTab('pending')">คำขอจัดอีเว้นท์</span>
                </div>
            </div>

            <div class="changeRole-content">
                <div id="pendingContent" class="changeRole-tab-content active">
                    <div id="changerole-pending-list">
                        <div class="followevent-content">
                        </div>
                    </div>
                    <div id="changerole-changed-list" class="changerole-hidden"></div>
                    <div id="changerole-reverted-list" class="changerole-hidden"></div>
                </div>

                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/meyawo.js"></script>
    <script>
        const users = [
            <?php
            foreach ($data as $event) {
                echo "{
                    id: \"" . htmlspecialchars($event['audience_id']) . "\",
                    name: \"" . htmlspecialchars($event['Audience_FirstName']) . "\",
                    role: \"" . htmlspecialchars($event['Audience_Role']) . "\",
                    email: \"" . htmlspecialchars($event['audience_email']) . "\",
                    eventDate: \"" . htmlspecialchars(date('d M Y', strtotime($event['request_event_date']))) . "\",
                    eventTime: \"" . htmlspecialchars(date('H:i', strtotime($event['request_event_time']))) . "\",
                    eventLocation: \"" . htmlspecialchars($event['request_event_location']) . "\",
                    eventImage: \"". htmlspecialchars($event['request_event_picture']). "\",
                    eventname: \"" . htmlspecialchars($event['request_event_name']) . "\",
                    eventid: \"" . htmlspecialchars($event['request_event_id']) . "\",
                },";
            }
            ?>

        ];

        function populatePendingList() {
            const pendingList = document.getElementById("changerole-pending-list");

            users.forEach(user => {
                const userDiv = document.createElement("div");
                userDiv.className = "followevent-content";

                userDiv.innerHTML = `
        <div class="followevent-tab-content followevent">
            <div class="followevent-setting-container-event d-flex align-items-center p-3">
                <div class="followevent-event-date text-center me-3">
                    <h3 class="mb-0 fw-bold">${user.eventDate.split(' ')[0]}</h3>
                    <p class="mb-0">${user.eventDate.split(' ')[1]}</p>
                    <p class="mb-0">${user.eventTime}</p>
                </div>
                <img src="${user.eventImage}" alt="Event Image" class="me-3 rounded" style="height: 150px; width: auto;">
                <div class="followevent-event-details">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h2>${user.eventname}</h2>
                    </div>
                    <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${user.eventLocation}</p>
                    <div class="changerole-role-card" id="${user.id}">
                        <img src="../assets/imgs/jusmine.png" alt="profile">
                        <div class="changerole-role-info">
                            <strong>${user.name}</strong><br>
                            <p class="waiting-buyer-email">Email: ${user.email}</p>
                        </div>
                        <button class="changerole-change-btn" onclick="window.location.href='requesteventdetail.php?request_event_id=${user.eventid}';">ดูรายละเอียด</button>
                    </div>
                </div>
            </div>
        </div>
    `;

                pendingList.appendChild(userDiv);
            });
        }

        populatePendingList();

        let selectedUserId = "";

        function openPopup(userId) {
            selectedUserId = userId;
            document.getElementById("changerole-overlay").style.display = "block";
            document.getElementById("changerole-popup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("changerole-overlay").style.display = "none";
            document.getElementById("changerole-popup").style.display = "none";
        }
        function confirmChange() {
            if (confirm("ยืนยันการขอจัดอีเว้นท์?")) {
            let newRole = document.getElementById("changerole-role-select").value;
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "updateRole.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText);
                location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                }
            };
            xhr.send("email=" + encodeURIComponent(users.find(user => user.email === selectedUserId).email) + "&newRole=" + encodeURIComponent(newRole));
            closePopup(); // ปิด popup
            }
        }


        function revertRole(userId) {
            let userCard = document.getElementById(userId);
            let originalRole = userCard.getAttribute("data-original-role"); // ดึงค่า role เดิม
            let roleText = userCard.querySelector(".changerole-role-text");

            roleText.innerText = originalRole; // ตั้งค่า role ให้กลับไปเป็นค่าเดิม

            // ค้นหาทั้ง Event Container ที่ผู้ใช้อยู่
            let eventContainer = userCard.closest('.followevent-setting-container-event');

            // ย้ายกลับไปที่ "คืนค่าโรลแล้ว"
            document.getElementById("revertedContent").appendChild(eventContainer);

            // ลบปุ่ม "คืนค่าโรล"
            let revertBtn = userCard.querySelector(".changerole-revert-btn");
            if (revertBtn) {
                revertBtn.remove();
            }

            // เพิ่มปุ่ม "ขอจัดอีเว้นท์" กลับมา
            let changeBtn = document.createElement("button");
            changeBtn.className = "changerole-change-btn";
            changeBtn.innerText = "ขอจัดอีเว้นท์";
            changeBtn.onclick = function () {
                openPopup(userId);
            };
            userCard.appendChild(changeBtn);
        }


        function changeTab(tabName) {
            const tabs = document.querySelectorAll('.changeRole-setting-navbar span');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            const activeTab = document.getElementById(tabName);
            activeTab.classList.add('active');

            const tabContents = document.querySelectorAll('.changeRole-tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            const activeContent = document.getElementById(tabName + 'Content');
            activeContent.classList.add('active');

            // ถ้าสลับไปที่ tab "changed" ให้ส่งคำร้องขอ AJAX เพื่อย้ายข้อมูลจาก audience ไปยัง EventOrganizer แล้วลบข้อมูลใน audience

        }

    </script>
</body>

</html>