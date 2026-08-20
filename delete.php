<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Delete story only if user_id matches session
    $stmt = $pdo->prepare("DELETE FROM blogPost WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
}

header("Location: index.php");
exit;
?>