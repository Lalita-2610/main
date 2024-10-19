<?php
include_once("checkloginadmin.php");
include_once("connectdb.php");

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("เชื่อมต่อฐานข้อมูลไม่ได้: " . mysqli_connect_error());
}

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);  // รับค่า id ที่ส่งมาใน URL

    // ดึงข้อมูลลูกค้าจากฐานข้อมูลตาม id
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // กำหนดผลลัพธ์ให้กับตัวแปร $data1
        $data1 = mysqli_fetch_assoc($result);
    } else {
        echo "ไม่พบข้อมูลลูกค้าที่ต้องการแก้ไข";
        exit();
    }
} else {
    echo "ไม่พบ ID ที่ส่งมา";
    exit();
}

// หากมีการส่งข้อมูล POST มาให้ทำการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_hash = mysqli_real_escape_string($conn, $_POST['password_hash']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $created_at = mysqli_real_escape_string($conn, $_POST['created_at']);

    // อัปเดตข้อมูลในฐานข้อมูล
    $update_sql = "UPDATE users SET first_name='$first_name', last_name='$last_name', sex='$sex', 
                   username='$username', password_hash='$password_hash', phone='$phone', 
                   email='$email', address='$address', created_at='$created_at' WHERE id='$id'";

    if (mysqli_query($conn, $update_sql)) {
        // ตั้งค่าข้อความสำเร็จใน session
        $_SESSION['message'] = "แก้ไขข้อมูลลูกค้าเรียบร้อยแล้ว";
        // เปลี่ยนเส้นทางไปยังหน้า customer.php
        header("Location: customer.php");
        exit();
    } else {
        echo "เกิดข้อผิดพลาดในการแก้ไข: " . mysqli_error($conn);
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล หลังจากทำทุกอย่างเสร็จสิ้นแล้ว
mysqli_close($conn);
?>



<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="../assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <title>IT Shop</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/dashboard/">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
<link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
 <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Static:wght@300;400;500;600;700&display=swap" rel="stylesheet">
 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

<link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
	 .custom-input:focus {
        background-color: #e9f1ff;
        border-color: #80bdff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    }

    /* เปลี่ยนสีขอบฟอร์มเมื่อกรอกข้อมูล */
    .custom-input:not(:placeholder-shown) {
        border: 2px solid #ff7f50;
    }

    /* เอฟเฟกต์ hover สำหรับปุ่ม */
    button[type="submit"]:hover {
        background-color: #ff4500;
        box-shadow: 0 5px 15px rgba(255, 69, 0, 0.4);
        transform: scale(1.05);
    }

    /* เอฟเฟกต์ transition สำหรับการเปลี่ยนแปลง */
    .custom-input, button[type="submit"] {
        transition: all 0.3s ease;
    }

    /* ปรับความกว้างของฟิลด์ให้อยู่ในบรรทัดเดียวกัน */
    .form-label {
        width: 120px;
    }
	.btn {
        transition: transform 0.2s ease; /* เอฟเฟกต์การเปลี่ยนแปลงการเคลื่อนไหว */
    }

   .btn-warning {
        background-color: #28a745; /* สีพื้นหลังเป็นขาว */
        color: #000!important; /* ตัวอักษรเป็นสีดำ */
        transition: transform 0.2s ease, color 0.2s ease; /* เอฟเฟกต์การเปลี่ยนแปลง */
    }

    .btn-warning:hover {
        transform: translateY(-5px); /* เด้งขึ้น 5 พิกเซลเมื่อ hover */
        color: #fff; /* เปลี่ยนสีตัวอักษรเป็นขาวเมื่อ hover */
        background-color: #ffc107; /* เปลี่ยนสีพื้นหลังเป็นสีเตือน */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* เพิ่มเงาเมื่อ hover */
    }
	
    body {
        background-color: #ffffff; /* เปลี่ยนเป็นสีขาว */
        font-family: 'Noto Sans Thai', sans-serif; /* ฟอนต์สำหรับ body */
    }

    .navbar-brand {
        font-size: 2rem !important; /* ปรับขนาดฟอนต์ตามที่ต้องการ */
    }

    /* ปรับแต่งปุ่ม */
    .btn {
        padding: 5px 10px; /* ปรับขนาด padding ให้เล็กลง */
        font-size: 0.8rem; /* ขนาดตัวอักษร */
        border-radius: 3px; /* ทำให้มุมมนเล็กน้อย */
        transition: background-color 0.3s ease; /* เอฟเฟกต์การเปลี่ยนสีปุ่ม */
    }

    .btn-warning {
        background-color: #ffc107; /* สีพื้นหลังสำหรับปุ่มเตือน */
        color: #fff; /* สีตัวอักษร */
    }

    .btn-danger {
        background-color: #dc3545; /* สีพื้นหลังสำหรับปุ่มลบ */
        color: #fff; /* สีตัวอักษร */
    }

    /* เพิ่มการเปลี่ยนสีเมื่อ hover */
    .btn:hover {
        opacity: 0.8; /* ทำให้ปุ่มโปร่งใสลงเมื่อ hover */
    }

    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 10px;
        background-color: #ffffff; /* สีพื้นหลังการ์ด */
        border: 1px solid #ccc; /* ขอบการ์ด */
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        border-color: #000; /* สีขอบเมื่อ hover */
    }

    .card:hover .card-body {
        background-color: #e9ecef; /* สีพื้นหลังที่สว่างขึ้นเมื่อ hover */
    }

    .form-container {
        max-width: 400px; /* กำหนดความกว้างสูงสุดของฟอร์ม */
        margin: 20px auto; /* จัดกลางฟอร์ม */
        padding: 20px; /* เพิ่ม padding ให้ฟอร์ม */
        border: 1px solid #ccc; /* ขอบของฟอร์ม */
        border-radius: 5px; /* มุมโค้ง */
        background-color: #f8f9fa; /* สีพื้นหลัง */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* เงา */
        transition: transform 0.3s ease; /* เอฟเฟกต์การเปลี่ยนแปลง */
    }

    .form-container:hover {
        transform: scale(1.02); /* ขยายเมื่อมีการ hover */
    }

    .form-label, .form-control {
        font-size: 0.85rem; /* ขนาดฟอนต์ */
    }

    .btn-success:hover {
        background-color: #218838; /* เปลี่ยนสีเมื่อ hover */
    }

    .btn-secondary:hover {
        background-color: #6c757d; /* เปลี่ยนสีเมื่อ hover */
    }

    .form-control:focus {
        border-color: #80bdff; /* สีขอบเมื่ออยู่ในสถานะ focus */
        box-shadow: 0 0 0.2rem rgba(0, 123, 255, 0.25); /* เงาเมื่อ focus */
    }

    /* สไตล์ตาราง */
    #ordersTable {
        background-color: #f8f9fa; /* สีพื้นหลัง */
        border-radius: 0.5rem; /* ทำให้มุมมน */
        overflow: hidden; /* เพื่อให้มุมมนแสดงผล */
    }

    #ordersTable tbody tr:hover {
        background-color: #e2e6ea; /* เปลี่ยนสีเมื่อชี้เมาส์ */
        cursor: pointer; /* เปลี่ยนเคอร์เซอร์ */
    }

    th, td {
        text-align: center; /* จัดกึ่งกลาง */
    }

    /* สไตล์ navbar */
    .navbar .text-white {
        color: #fff !important; /* เปลี่ยนสีฟอนต์ใน navbar เป็นสีขาว */
    }

    /* สไตล์ sidebar */
    .sidebar .nav-link {
        color: black; /* เปลี่ยนสีฟอนต์ของลิงค์ใน sidebar เป็นสีดำ */
    }

    .sidebar .nav-link:hover {
        color: #007bff; /* เปลี่ยนสีเมื่อ hover เป็นสีน้ำเงิน */
    }

    .sidebar .active {
        color: #000; /* สีฟอนต์ของลิงค์ที่ active เป็นสีดำ */
    }
</style>

    
    <!-- Custom styles for this template -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
  </head>
  <body>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>

    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
              id="bd-theme"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              aria-label="Toggle theme (auto)">
        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
        <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#sun-fill"></use></svg>
            Light
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
            Dark
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#circle-half"></use></svg>
            Auto
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
      </ul>
    </div>

    
<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
  <symbol id="calendar3" viewBox="0 0 16 16">
    <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"/>
    <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
  </symbol>
  <symbol id="cart" viewBox="0 0 16 16">
    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
  </symbol>
  <symbol id="chevron-right" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
  </symbol>
  <symbol id="door-closed" viewBox="0 0 16 16">
    <path d="M3 2a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v13h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V2zm1 13h8V2H4v13z"/>
    <path d="M9 9a1 1 0 1 0 2 0 1 1 0 0 0-2 0z"/>
  </symbol>
  <symbol id="file-earmark" viewBox="0 0 16 16">
    <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
  </symbol>
  <symbol id="file-earmark-text" viewBox="0 0 16 16">
    <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
    <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
  </symbol>
  <symbol id="gear-wide-connected" viewBox="0 0 16 16">
    <path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434l.071-.286zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5zm0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78h4.723zM5.048 3.967c-.03.021-.058.043-.087.065l.087-.065zm-.431.355A4.984 4.984 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8 4.617 4.322zm.344 7.646.087.065-.087-.065z"/>
  </symbol>
  <symbol id="graph-up" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
  </symbol>
  <symbol id="house-fill" viewBox="0 0 16 16">
    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5Z"/>
    <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6Z"/>
  </symbol>
  <symbol id="list" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
  </symbol>
  <symbol id="people" viewBox="0 0 16 16">
    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
  </symbol>
  <symbol id="plus-circle" viewBox="0 0 16 16">
    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
  </symbol>
  <symbol id="puzzle" viewBox="0 0 16 16">
    <path d="M3.112 3.645A1.5 1.5 0 0 1 4.605 2H7a.5.5 0 0 1 .5.5v.382c0 .696-.497 1.182-.872 1.469a.459.459 0 0 0-.115.118.113.113 0 0 0-.012.025L6.5 4.5v.003l.003.01c.004.01.014.028.036.053a.86.86 0 0 0 .27.194C7.09 4.9 7.51 5 8 5c.492 0 .912-.1 1.19-.24a.86.86 0 0 0 .271-.194.213.213 0 0 0 .039-.063v-.009a.112.112 0 0 0-.012-.025.459.459 0 0 0-.115-.118c-.375-.287-.872-.773-.872-1.469V2.5A.5.5 0 0 1 9 2h2.395a1.5 1.5 0 0 1 1.493 1.645L12.645 6.5h.237c.195 0 .42-.147.675-.48.21-.274.528-.52.943-.52.568 0 .947.447 1.154.862C15.877 6.807 16 7.387 16 8s-.123 1.193-.346 1.638c-.207.415-.586.862-1.154.862-.415 0-.733-.246-.943-.52-.255-.333-.48-.48-.675-.48h-.237l.243 2.855A1.5 1.5 0 0 1 11.395 14H9a.5.5 0 0 1-.5-.5v-.382c0-.696.497-1.182.872-1.469a.459.459 0 0 0 .115-.118.113.113 0 0 0 .012-.025L9.5 11.5v-.003a.214.214 0 0 0-.039-.064.859.859 0 0 0-.27-.193C8.91 11.1 8.49 11 8 11c-.491 0-.912.1-1.19.24a.859.859 0 0 0-.271.194.214.214 0 0 0-.039.063v.003l.001.006a.113.113 0 0 0 .012.025c.016.027.05.068.115.118.375.287.872.773.872 1.469v.382a.5.5 0 0 1-.5.5H4.605a1.5 1.5 0 0 1-1.493-1.645L3.356 9.5h-.238c-.195 0-.42.147-.675.48-.21.274-.528.52-.943.52-.568 0-.947-.447-1.154-.862C.123 9.193 0 8.613 0 8s.123-1.193.346-1.638C.553 5.947.932 5.5 1.5 5.5c.415 0 .733.246.943.52.255.333.48.48.675.48h.238l-.244-2.855zM4.605 3a.5.5 0 0 0-.498.55l.001.007.29 3.4A.5.5 0 0 1 3.9 7.5h-.782c-.696 0-1.182-.497-1.469-.872a.459.459 0 0 0-.118-.115.112.112 0 0 0-.025-.012L1.5 6.5h-.003a.213.213 0 0 0-.064.039.86.86 0 0 0-.193.27C1.1 7.09 1 7.51 1 8c0 .491.1.912.24 1.19.07.14.14.225.194.271a.213.213 0 0 0 .063.039H1.5l.006-.001a.112.112 0 0 0 .025-.012.459.459 0 0 0 .118-.115c.287-.375.773-.872 1.469-.872H3.9a.5.5 0 0 1 .498.542l-.29 3.408a.5.5 0 0 0 .497.55h1.878c-.048-.166-.195-.352-.463-.557-.274-.21-.52-.528-.52-.943 0-.568.447-.947.862-1.154C6.807 10.123 7.387 10 8 10s1.193.123 1.638.346c.415.207.862.586.862 1.154 0 .415-.246.733-.52.943-.268.205-.415.39-.463.557h1.878a.5.5 0 0 0 .498-.55l-.001-.007-.29-3.4A.5.5 0 0 1 12.1 8.5h.782c.696 0 1.182.497 1.469.872.05.065.091.099.118.115.013.008.021.01.025.012a.02.02 0 0 0 .006.001h.003a.214.214 0 0 0 .064-.039.86.86 0 0 0 .193-.27c.14-.28.24-.7.24-1.191 0-.492-.1-.912-.24-1.19a.86.86 0 0 0-.194-.271.215.215 0 0 0-.063-.039H14.5l-.006.001a.113.113 0 0 0-.025.012.459.459 0 0 0-.118.115c-.287.375-.773.872-1.469.872H12.1a.5.5 0 0 1-.498-.543l.29-3.407a.5.5 0 0 0-.497-.55H9.517c.048.166.195.352.463.557.274.21.52.528.52.943 0 .568-.447.947-.862 1.154C9.193 5.877 8.613 6 8 6s-1.193-.123-1.638-.346C5.947 5.447 5.5 5.068 5.5 4.5c0-.415.246-.733.52-.943.268-.205.415-.39.463-.557H4.605z"/>
  </symbol>
  <symbol id="search" viewBox="0 0 16 16">
    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
  </symbol>
</svg>


<header class="navbar sticky-top bg-dark flex-md-nowrap p-0 shadow d-flex justify-content-between align-items-center" data-bs-theme="dark">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 text-white" href="#"style="font-size: 1.8rem;font-weight: bold;">IT Shop</a>

  <!-- Form ค้นหาทางขวามือ -->
  <form class="d-flex align-items-center me-3" role="search" action="indexproductadmin.php" method="GET">
    <span class="text-white me-2">ค้นหา</span>
    <input type="text" class="form-control me-2" name="search" placeholder="ค้นหา..." 
           aria-label="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
    <button class="btn btn-outline-warning" type="submit" name="Submit" aria-label="Search">
      <i class="bi bi-search"></i>
    </button>
  </form>
</header>
<div class="container-fluid">
  <div class="row">
    <div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary">
      <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
  <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="sidebarMenuLabel">IT Shop</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center gap-2" href="indexproduct.php">
                 <i class="bi bi-house"></i>
               Home
              </a>
              <li class="nav-item">
              <a class="nav-link d-flex align-items-center gap-2" href="order_admin.php">
                <svg class="bi"><use xlink:href="#file-earmark"/></svg>
                Orders
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center gap-2" href="indexproductadmin.php">
              <svg class="bi"><use xlink:href="#cart"/></svg>
                Products
</a></li>
           <li class="nav-item">
              <a class="nav-link d-flex align-items-center gap-2" href="indextype.php"><i class="bi bi-card-checklist"></i>
                Product type
              </a>
            </li>
  <li class="nav-item">
              <a class="nav-link d-flex align-items-center gap-2" href="customer.php">
              <svg class="bi"><use xlink:href="#people"/></svg>
                Customers
              </a>
            </li>
          <ul class="nav flex-column mb-auto">
  <li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2" href="#">
     <i class="bi bi-person-circle"></i>
      <?= $_SESSION['aname']; ?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2" href="logoutadmin.php">
      <svg class="bi"><use xlink:href="#door-closed"/></svg>
      Sign out
    </a>
  </li>
</ul>
        </div>
      </div>
    </div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    </nav>
<main class="col-md-9 col-lg-10">
    <section class="py-3 text-center container">
        <div class="row py-lg-2 justify-content-center">
            <div class="col-lg-8 col-md-10">
                <form method="post" action="" enctype="multipart/form-data" class="p-5 bg-gradient rounded shadow-lg" style="background: linear-gradient(135deg, #f5f7fa, #c3cfe2);">
                    
<h2 class="mb-4 text-white bg-dark p-3 rounded shadow-sm">แก้ไขข้อมูลลูกค้า</h2>

    <!-- ID -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-id-badge fa-lg me-2 text-dark"></i>
        <label for="id" class="form-label text-dark mb-0 me-3">ID</label>
        <input type="text" id="id" name="id" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['id']; ?>" readonly>
    </div>

    <!-- First Name -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-user fa-lg me-2 text-dark"></i>
        <label for="first_name" class="form-label text-dark mb-0 me-3">ชื่อจริง</label>
        <input type="text" id="first_name" name="first_name" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['first_name']; ?>">
    </div>

    <!-- Last Name -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-user fa-lg me-2 text-dark"></i>
        <label for="last_name" class="form-label text-dark mb-0 me-3">นามสกุล</label>
        <input type="text" id="last_name" name="last_name" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['last_name']; ?>">
    </div>

    <!-- Sex -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-venus-mars fa-lg me-2 text-dark"></i>
        <label for="sex" class="form-label text-dark mb-0 me-3">เพศ</label>
        <input type="text" id="sex" name="sex" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['sex']; ?>">
    </div>

    <!-- Username -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-user-circle fa-lg me-2 text-dark"></i>
        <label for="username" class="form-label text-dark mb-0 me-3">ชื่อผู้ใช้</label>
        <input type="text" id="username" name="username" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['username']; ?>">
    </div>

    <!-- Password -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-lock fa-lg me-2 text-dark"></i>
        <label for="password_hash" class="form-label text-dark mb-0 me-3">รหัสผ่าน</label>
        <input type="text" id="password_hash" name="password_hash" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['password_hash']; ?>">
    </div>

    <!-- Phone -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-phone fa-lg me-2 text-dark"></i>
        <label for="phone" class="form-label text-dark mb-0 me-3">เบอร์โทรศัพท์</label>
        <input type="text" id="phone" name="phone" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['phone']; ?>">
    </div>

    <!-- Email -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-envelope fa-lg me-2 text-dark"></i>
        <label for="email" class="form-label text-dark mb-0 me-3">อีเมล</label>
        <input type="email" id="email" name="email" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['email']; ?>">
    </div>

    <!-- Address -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-map-marker-alt fa-lg me-2 text-dark"></i>
        <label for="address" class="form-label text-dark mb-0 me-3">ที่อยู่</label>
        <input type="text" id="address" name="address" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['address']; ?>">
    </div>

    <!-- Created At -->
    <div class="mb-4 d-flex align-items-center text-start">
        <i class="fas fa-calendar-alt fa-lg me-2 text-dark"></i>
        <label for="created_at" class="form-label text-dark mb-0 me-3">วันที่สร้าง</label>
        <input type="text" id="created_at" name="created_at" class="form-control custom-input flex-fill border-0 shadow-sm" required value="<?= $data1['created_at']; ?>">
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary">บันทึก</button>
</form>





<script src="../assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>