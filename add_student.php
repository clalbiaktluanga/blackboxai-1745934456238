<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $full_name = $conn->real_escape_string($data['full_name']);
    $parent_name = isset($data['parent_name']) ? $conn->real_escape_string($data['parent_name']) : null;
    $unique_id = isset($data['unique_id']) ? $conn->real_escape_string($data['unique_id']) : null;
    $class_id = intval($data['class_id']);

    $sql = "INSERT INTO students (full_name, parent_name, unique_id, class_id) VALUES ('$full_name', " . ($parent_name ? "'$parent_name'" : "NULL") . ", " . ($unique_id ? "'$unique_id'" : "NULL") . ", $class_id)";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add student']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
