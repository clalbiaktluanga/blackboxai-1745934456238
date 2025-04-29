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
    $username = $conn->real_escape_string($data['username']);
    $phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : null;
    $email = isset($data['email']) ? $conn->real_escape_string($data['email']) : null;
    $class_id = intval($data['class_id']);

    // Check if username already exists
    $sql_check = "SELECT id FROM teachers WHERE username = '$username'";
    $result_check = $conn->query($sql_check);
    if ($result_check && $result_check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }

    // Default password for new teacher
    $default_password = password_hash('teacher123', PASSWORD_DEFAULT);

    // Insert new teacher
    $sql_insert = "INSERT INTO teachers (full_name, username, password, phone, email) VALUES ('$full_name', '$username', '$default_password', " . ($phone ? "'$phone'" : "NULL") . ", " . ($email ? "'$email'" : "NULL") . ")";
    if ($conn->query($sql_insert) === TRUE) {
        $teacher_id = $conn->insert_id;
        // Assign class to teacher
        $sql_assign = "INSERT INTO class_teacher (class_id, teacher_id) VALUES ($class_id, $teacher_id)";
        if ($conn->query($sql_assign) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign class']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add teacher']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
