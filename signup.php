<?php
include "db.php";

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูล Faculty
$faculty_sql = "SELECT FacultyID, Faculty_Name FROM Faculty";
$faculty_result = $conn->query($faculty_sql);

// ดึงข้อมูล Gender
$gender_sql = "SELECT GenderID, GenderType FROM Gender";
$gender_result = $conn->query($gender_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $email      = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $faculty_id = $_POST['faculty_id'];
    $major_id   = $_POST['major_id'];
    $phone      = $_POST['phone'];
    $password   = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $gender_id  = $_POST['gender_id'];
    $role       = $_POST['role'];
    $student_id = $_POST['student_id'];

    // ถ้า role เป็น Student ให้เช็คว่าข้อมูลใน studentsdata มีตรงทุกค่า
    if ($role === "Student") {
        // เช็คว่ามีแถวที่ email, student_id, first_name, last_name ตรงกันหรือไม่
        $check_studentdata = "
            SELECT * FROM studentsdata 
            WHERE email = '$email' 
              AND student_id = '$student_id'
              AND first_name = '$first_name'
              AND last_name = '$last_name'
        ";
        $studentdata_result = $conn->query($check_studentdata);

        // หากหาไม่เจอ แสดงว่าไม่มีข้อมูลในระบบนักศึกษา
        if ($studentdata_result->num_rows == 0) {
            $error = "คุณไม่สามารถสมัครสมาชิกได้ เนื่องจากข้อมูลของคุณไม่อยู่ในระบบนักศึกษา!";
        } else {
            // เมื่อตรวจสอบผ่าน ให้ insert ลงตาราง Audience
            $insert_sql = "INSERT INTO Audience 
                    (Audience_email, Audience_FirstName, Audience_LastName, FacultyID, MajorID,
                     Audience_Phone, Audience_Password, GenderID, Audience_Role, StudentID) 
                    VALUES 
                    ('$email', '$first_name', '$last_name', '$faculty_id', '$major_id',
                     '$phone', '$password', '$gender_id', '$role', '$student_id')";

            if ($conn->query($insert_sql) === TRUE) {
                $success = "บันทึกข้อมูลสำเร็จ!";
            } else {
                $error = "Error: " . $insert_sql . "<br>" . $conn->error;
            }
        }
    } else {
        // ถ้า role ไม่ใช่ Student ก็ไม่ต้องเช็คใน studentsdata
        // ตัวอย่างนี้จะบังคับเก็บ StudentID เป็น null
        $student_id = null;

        $insert_sql = "INSERT INTO Audience
                (Audience_email, Audience_FirstName, Audience_LastName, FacultyID, MajorID, 
                 Audience_Phone, Audience_Password, GenderID, Audience_Role, StudentID) 
                VALUES
                ('$email', '$first_name', '$last_name', '$faculty_id', '$major_id',
                 '$phone', '$password', '$gender_id', '$role', NULL)";

        if ($conn->query($insert_sql) === TRUE) {
            $success = "บันทึกข้อมูลสำเร็จ!";
        } else {
            $error = "Error: " . $insert_sql . "<br>" . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="assets/css/meyawo.css"/>
    <title>Sign Up</title>

    <script>
    // แสดง/ซ่อน ช่อง Student ID เมื่อ role เป็น Student
    document.addEventListener("DOMContentLoaded", function () {
        const roleSelect = document.getElementById("role");
        const studentIdField = document.getElementById("student_id_field");

        function toggleStudentId() {
            if (roleSelect.value === "Student") {
                studentIdField.style.display = "block";
            } else {
                studentIdField.style.display = "none";
            }
        }

        roleSelect.addEventListener("change", toggleStudentId);
        toggleStudentId(); // เรียกครั้งแรกเพื่อกำหนดตามค่า default
    });
    </script>
</head>
<body class="signup-body">

<div class="signup-container">
    <img src="assets/imgs/logoEventNU.png" alt="NU Event Logo" class="signup-logo">
    <?php if(isset($error)) : ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if(isset($success)) : ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="first_name" class="signup-input" placeholder="First Name" required>
        <input type="text" name="last_name" class="signup-input" placeholder="Last Name" required>
        <input type="email" name="email" class="signup-input" placeholder="Email" required>

        <select name="faculty_id" id="faculty" class="signup-input" required>
            <option value="">Select Faculty</option>
            <?php while($row = $faculty_result->fetch_assoc()) { ?>
                <option value="<?php echo $row['FacultyID']; ?>"><?php echo $row['Faculty_Name']; ?></option>
            <?php } ?>
        </select>

        <select name="major_id" id="major" class="signup-input">
            <option value="">Select Major</option>
        </select>

        <!-- ดึง major ตาม faculty -->
        <script>
            document.getElementById("faculty").addEventListener("change", function() {
                let facultyID = this.value;
                let majorSelect = document.getElementById("major");

                // ล้างข้อมูลเดิม
                majorSelect.innerHTML = '<option value="">Select Major</option>';

                if (facultyID) {
                    fetch("get_majors.php?faculty_id=" + facultyID)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(major => {
                                let option = document.createElement("option");
                                option.value = major.MajorID;
                                option.textContent = major.Major_Name;
                                majorSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error("Error:", error));
                }
            });
        </script>

        <input type="tel" name="phone" class="signup-input" placeholder="Phone Number" required>

        <!-- Password + Confirm -->
        <input type="password" id="password" name="password" class="signup-input" placeholder="Password" required>
        <input type="password" id="confirm_password" name="confirm_password" class="signup-input" placeholder="Confirm Password" required>
        <p id="password_error" class="error" style="color: red; display: none;">Passwords do not match!</p>

        <select name="gender_id" class="signup-input" required>
            <option value="">Select Gender</option>
            <?php while($row = $gender_result->fetch_assoc()) { ?>
                <option value="<?php echo $row['GenderID']; ?>"><?php echo $row['GenderType']; ?></option>
            <?php } ?>
        </select>

        <select name="role" id="role" class="signup-input" required>
            <option value="">Select Role</option>
            <option value="Student">Student</option>
            <option value="Lecturer">Lecturer</option>
            <option value="Guest User">Guest User</option>
        </select>

        <!-- Student ID field (ซ่อนถ้าไม่ใช่ Student) -->
        <div id="student_id_field" style="display: none;">
            <input type="text" name="student_id" id="student_id" class="signup-input" placeholder="Student ID">
        </div>

        <button type="submit" class="signup-button">Sign Up</button>
    </form>

    <p class="signup-text">
        Already have an account?
        <a href="login.php">Log In</a>
    </p>
</div>

<!-- ตรวจสอบการยืนยันรหัสผ่าน -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const errorText = document.getElementById("password_error");
    const form = document.querySelector("form");

    form.addEventListener("submit", function(event) {
        if (password.value !== confirmPassword.value) {
            event.preventDefault(); // ป้องกันการส่งฟอร์ม
            errorText.style.display = "block"; // แสดงข้อความ error
            confirmPassword.style.border = "2px solid red";
        } else {
            errorText.style.display = "none";
            confirmPassword.style.border = "2px solid green";
        }
    });

    confirmPassword.addEventListener("input", function() {
        if (password.value === confirmPassword.value) {
            confirmPassword.style.border = "2px solid green";
            errorText.style.display = "none";
        } else {
            confirmPassword.style.border = "2px solid red";
            errorText.style.display = "block";
        }
    });
});
</script>

</body>
</html>
