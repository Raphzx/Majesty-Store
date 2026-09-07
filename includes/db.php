<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'toko_online';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $rootDir   = str_replace('\\', '/', realpath(dirname(__DIR__)));
    $docRoot   = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');

    $cleanDoc = rtrim($docRoot, '/');
    $cleanRoot = rtrim($rootDir, '/');
    $basePath  = str_replace($cleanDoc, '', $cleanRoot);
 
    if ($basePath === $cleanRoot || $basePath === $cleanDoc || $basePath === $cleanRoot . '/' . $cleanDoc) {
        $basePath = $scriptDir;
        if (substr($basePath, -9) === '/includes') {
            $basePath = substr($basePath, 0, -9);
        } elseif (substr($basePath, -6) === '/admin' || substr($basePath, -9) === '/customer') {
            $basePath = dirname($basePath);
        }
    }

    define('BASE_URL', rtrim($basePath, '/'));
}

function base_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function url($path = '') {
    return htmlspecialchars(base_url($path));
}

function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function format_rupiah($number) {
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

function ensure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function rupiah_only($number) {
    return number_format((float)$number, 0, ',', '.');
}
?>
