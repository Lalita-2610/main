<?php
session_start();
if (!isset($_SESSION['uid'])) {
    echo "<div style='text-align:center; margin-top:20%;'>
            <h2>โปรดเข้าสู่ระบบก่อน</h2>
            <p>กำลังไปยังหน้าล็อกอิน...</p>
            <script>
                setTimeout(function() {
                    window.location.href = 'indexlogin.php'; // เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
                }, 3000); // รอ 3 วินาทีก่อนเปลี่ยนเส้นทาง
            </script>
          </div>";
    exit; // หยุดการทำงานของสคริปต์
}
?>
