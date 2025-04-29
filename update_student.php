<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id']);
    $full_name = $conn->real_escape_string($data['full_name']);
    $class_id = intval($data['class_id']);

    $sql = "UPDATE students SET full_name = '$full_name', class_id = $class_id WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update student']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
