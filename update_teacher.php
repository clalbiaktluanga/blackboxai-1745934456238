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
    $username = $conn->real_escape_string($data['username']);

    // Check if username is unique (excluding current user)
    $sql_check = "SELECT id FROM teachers WHERE username = '$username' AND id != $id";
    $result_check = $conn->query($sql_check);
    if ($result_check && $result_check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }

    $sql = "UPDATE teachers SET full_name = '$full_name', username = '$username' WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update teacher']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
