<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $class_name = $conn->real_escape_string($data['class_name']);
    $teacher_id = intval($data['teacher_id']);
    $student_ids = isset($data['student_ids']) ? $data['student_ids'] : [];

    // Insert new class
    $sql_insert_class = "INSERT INTO classes (class_name) VALUES ('$class_name')";
    if ($conn->query($sql_insert_class) === TRUE) {
        $class_id = $conn->insert_id;

        // Assign teacher to class
        $sql_assign_teacher = "INSERT INTO class_teacher (class_id, teacher_id) VALUES ($class_id, $teacher_id)";
        if (!$conn->query($sql_assign_teacher)) {
            echo json_encode(['success' => false, 'message' => 'Failed to assign teacher']);
            exit();
        }

        // Update students to belong to this class
        if (!empty($student_ids)) {
            $student_ids_int = array_map('intval', $student_ids);
            $ids_str = implode(',', $student_ids_int);
            $sql_update_students = "UPDATE students SET class_id = $class_id WHERE id IN ($ids_str)";
            if (!$conn->query($sql_update_students)) {
                echo json_encode(['success' => false, 'message' => 'Failed to assign students']);
                exit();
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add class']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
