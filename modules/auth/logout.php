<?php
require_once __DIR__ . '/../includes/session_check.php';

// Destroy session completely
session_unset();
session_destroy();

set_flash_message("You have been logged out successfully.", "success");
header("Location: login.php");
exit();
?>