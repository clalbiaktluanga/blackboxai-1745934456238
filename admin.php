<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Fetch all classes
$classes_result = $conn->query("SELECT * FROM classes ORDER BY class_name");
$classes = [];
if ($classes_result) {
    while ($row = $classes_result->fetch_assoc()) {
        $classes[] = $row;
    }
}

// Fetch all teachers
$teachers_result = $conn->query("SELECT * FROM teachers ORDER BY full_name");
$teachers = [];
if ($teachers_result) {
    while ($row = $teachers_result->fetch_assoc()) {
        $teachers[] = $row;
    }
}

// Fetch all students
$students_result = $conn->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.full_name");
$students = [];
if ($students_result) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch class_teacher assignments
$class_teacher_result = $conn->query("SELECT * FROM class_teacher");
$class_teacher = [];
if ($class_teacher_result) {
    while ($row = $class_teacher_result->fetch_assoc()) {
        $class_teacher[$row['class_id']] = $row['teacher_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Panel - Kids Den School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            color: #000;
        }
    </style>
    <script>
        async function assignClass(classId) {
            const select = document.getElementById('teacher-select-' + classId);
            const teacherId = select.value;

            const response = await fetch('assign_class.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ class_id: classId, teacher_id: teacherId })
            });
            const data = await response.json();
            if (data.success) {
                alert('Class assigned successfully');
            } else {
                alert('Failed to assign class');
            }
        }
    </script>
</head>
<body class="min-h-screen p-6">
    <header class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-semibold">Admin Panel</h1>
        <a href="logout.php" class="text-black border border-black px-4 py-2 rounded hover:bg-black hover:text-white transition">Logout</a>
    </header>
    <main>
        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Classes</h2>
            <table class="w-full border-collapse border border-black mb-4">
                <thead>
                    <tr>
                        <th class="border border-black px-4 py-2">Class Name</th>
                        <th class="border border-black px-4 py-2">Assigned Teacher</th>
                        <th class="border border-black px-4 py-2">Assign Teacher</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td class="border border-black px-4 py-2"><?php echo htmlspecialchars($class['class_name']); ?></td>
                            <td class="border border-black px-4 py-2">
                                <?php
                                $teacher_id = $class_teacher[$class['id']] ?? null;
                                $teacher_name = '';
                                foreach ($teachers as $teacher) {
                                    if ($teacher['id'] == $teacher_id) {
                                        $teacher_name = $teacher['full_name'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($teacher_name);
                                ?>
                            </td>
                            <td class="border border-black px-4 py-2">
                                <select id="teacher-select-<?php echo $class['id']; ?>" class="border border-black rounded px-2 py-1">
                                    <option value="">-- Select Teacher --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>" <?php echo (isset($teacher_id) && $teacher_id == $teacher['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($teacher['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button onclick="assignClass(<?php echo $class['id']; ?>)" class="ml-2 bg-black text-white px-3 py-1 rounded hover:bg-gray-800 transition">Assign</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="3" class="border border-black px-4 py-2 text-center">No classes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button onclick="openAddTeacherModal()" class="mt-4 bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition">Add New Teacher</button>
        </section>

        <!-- Add Teacher Modal -->
        <div id="addTeacherModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white text-black p-6 rounded shadow-lg w-full max-w-md">
                <h3 class="text-xl font-semibold mb-4">Add New Teacher</h3>
                <form id="addTeacherForm" onsubmit="submitAddTeacher(event)">
                    <div class="mb-4">
                        <label for="addTeacherFullName" class="block mb-1 font-medium">Full Name</label>
                        <input type="text" id="addTeacherFullName" name="full_name" required class="w-full border border-black rounded px-3 py-2" />
                    </div>
                    <div class="mb-4">
                        <label for="addTeacherUsername" class="block mb-1 font-medium">Username</label>
                        <input type="text" id="addTeacherUsername" name="username" required class="w-full border border-black rounded px-3 py-2" />
                    </div>
                    <div class="mb-4">
                        <label for="addTeacherPhone" class="block mb-1 font-medium">Phone Number</label>
                        <input type="text" id="addTeacherPhone" name="phone" class="w-full border border-black rounded px-3 py-2" />
                    </div>
                    <div class="mb-4">
                        <label for="addTeacherEmail" class="block mb-1 font-medium">Email</label>
                        <input type="email" id="addTeacherEmail" name="email" class="w-full border border-black rounded px-3 py-2" />
                    </div>
                    <div class="mb-4">
                        <label for="addTeacherClass" class="block mb-1 font-medium">Class</label>
                        <select id="addTeacherClass" name="class_id" required class="w-full border border-black rounded px-3 py-2">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeAddTeacherModal()" class="px-4 py-2 border border-black rounded hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openAddTeacherModal() {
                document.getElementById('addTeacherModal').classList.remove('hidden');
            }
            function closeAddTeacherModal() {
                document.getElementById('addTeacherModal').classList.add('hidden');
            }
            async function submitAddTeacher(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const response = await fetch('add_teacher.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    alert('Teacher added successfully');
                    location.reload();
                } else {
                    alert('Failed to add teacher: ' + (result.message || ''));
                }
            }
        </script>
        </section>
        <section>
            <h2 class="text-xl font-semibold mb-4">Students</h2>
            <table class="w-full border-collapse border border-black">
                <thead>
                    <tr>
                        <th class="border border-black px-4 py-2">Full Name</th>
                        <th class="border border-black px-4 py-2">Class</th>
                        <th class="border border-black px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td class="border border-black px-4 py-2"><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td class="border border-black px-4 py-2"><?php echo htmlspecialchars($student['class_name']); ?></td>
                            <td class="border border-black px-4 py-2">
                                <button onclick="openEditStudentModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars(addslashes($student['full_name'])); ?>', <?php echo $student['class_id']; ?>)" class="bg-black text-white px-3 py-1 rounded hover:bg-gray-800 transition">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="3" class="border border-black px-4 py-2 text-center">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Edit Teacher Modal -->
    <div id="editTeacherModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white text-black p-6 rounded shadow-lg w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Edit Teacher</h3>
            <form id="editTeacherForm" onsubmit="submitEditTeacher(event)">
                <input type="hidden" id="editTeacherId" name="id" />
                <div class="mb-4">
                    <label for="editTeacherFullName" class="block mb-1 font-medium">Full Name</label>
                    <input type="text" id="editTeacherFullName" name="full_name" required class="w-full border border-black rounded px-3 py-2" />
                </div>
                <div class="mb-4">
                    <label for="editTeacherUsername" class="block mb-1 font-medium">Username</label>
                    <input type="text" id="editTeacherUsername" name="username" required class="w-full border border-black rounded px-3 py-2" />
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeEditTeacherModal()" class="px-4 py-2 border border-black rounded hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white text-black p-6 rounded shadow-lg w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Edit Student</h3>
            <form id="editStudentForm" onsubmit="submitEditStudent(event)">
                <input type="hidden" id="editStudentId" name="id" />
                <div class="mb-4">
                    <label for="editStudentFullName" class="block mb-1 font-medium">Full Name</label>
                    <input type="text" id="editStudentFullName" name="full_name" required class="w-full border border-black rounded px-3 py-2" />
                </div>
                <div class="mb-4">
                    <label for="editStudentClass" class="block mb-1 font-medium">Class</label>
                    <select id="editStudentClass" name="class_id" required class="w-full border border-black rounded px-3 py-2">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeEditStudentModal()" class="px-4 py-2 border border-black rounded hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Teacher modal functions
        function openEditTeacherModal(id, fullName, username) {
            document.getElementById('editTeacherId').value = id;
            document.getElementById('editTeacherFullName').value = fullName;
            document.getElementById('editTeacherUsername').value = username;
            document.getElementById('editTeacherModal').classList.remove('hidden');
        }
        function closeEditTeacherModal() {
            document.getElementById('editTeacherModal').classList.add('hidden');
        }
        async function submitEditTeacher(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            const response = await fetch('update_teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                alert('Teacher updated successfully');
                location.reload();
            } else {
                alert('Failed to update teacher');
            }
        }

        // Student modal functions
        function openEditStudentModal(id, fullName, classId) {
            document.getElementById('editStudentId').value = id;
            document.getElementById('editStudentFullName').value = fullName;
            document.getElementById('editStudentClass').value = classId;
            document.getElementById('editStudentModal').classList.remove('hidden');
        }
        function closeEditStudentModal() {
            document.getElementById('editStudentModal').classList.add('hidden');
        }
        async function submitEditStudent(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            const response = await fetch('update_student.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                alert('Student updated successfully');
                location.reload();
            } else {
                alert('Failed to update student');
            }
        }
    </script>
</body>
</html>
