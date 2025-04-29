<?php
// Script to create initial admin and teacher users with hashed passwords
require_once 'db.php';

function createUser($conn, $table, $username, $password, $full_name = null) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if ($table === 'teachers') {
        $stmt = $conn->prepare("INSERT INTO teachers (username, password, full_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $full_name);
    } else if ($table === 'admin') {
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
    } else {
        return false;
    }
    return $stmt->execute();
}

// Create admin user
$admin_created = createUser($conn, 'admin', 'admin', 'adminpassword');

// Create a sample teacher user
$teacher_created = createUser($conn, 'teachers', 'teacher1', 'teacherpassword', 'Teacher One');

if ($admin_created && $teacher_created) {
    echo "Admin and teacher users created successfully.\n";
    echo "Admin login: username='admin', password='adminpassword'\n";
    echo "Teacher login: username='teacher1', password='teacherpassword'\n";
} else {
    echo "Failed to create users or users already exist.\n";
}
?>
