<?php
session_start();
include '../includes/db.php';
unset($_SESSION['admin']);
session_destroy();
header('Location: ' . url('admin/login'));
exit();