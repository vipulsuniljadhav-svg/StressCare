<?php
if(session_status()===PHP_SESSION_NONE) session_start();
function requireLogin(){ if(!isset($_SESSION['user_id'])){header('Location: /StressManagementSystem/login.php');exit;} }
function requireAdmin(){ if(!isset($_SESSION['admin_id'])){header('Location: /StressManagementSystem/admin/login.php');exit;} }
?>
