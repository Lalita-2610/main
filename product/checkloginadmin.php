<?php
	session_start();
	if (!isset($_SESSION['aid'])) {
  	echo '<p style="color:red;">' . htmlspecialchars('Access Denied!', ENT_QUOTES, 'UTF-8') . '						</p>';
  header('Refresh: 3; URL=indexloginadmin.php');
  exit;
}
?>
