<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['admin'])) {
    header('Location: ' . url('admin/dashboard'));
} else {
    header('Location: ' . url('admin/login'));
}
exit();