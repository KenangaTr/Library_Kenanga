<?php
require_once 'config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
?>
