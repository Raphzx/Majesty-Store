<?php
session_start();
include '../includes/db.php';
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
header('Location: ' . url(''));
exit();