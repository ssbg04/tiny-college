<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny College Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Tiny College</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" style="text-decoration: underline orange 1px; color: white;" href="../../pages/school_manage/schools.php">Schools</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/building_manage/buildings.php">Buildings</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/room_manage/rooms.php">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/department_manage/departments.php">Departments</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/professor_manage/professors.php">Professors</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/student_manage/students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/class_manage/class.php">Class</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/semester_manage/semester.php">Semester</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/enroll_manage/enroll.php">Enroll</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/course_manage/courses.php">Courses</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
<?php
require_once 'db/config.php';

// Create
if(isset($_POST['create'])){
    $schoolName = $_POST['school_name'];
    $stmt = $pdo->prepare("INSERT INTO school (SCHOOL_NAME) VALUES (?)");
    $stmt->execute([$schoolName]);
}

// Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM school WHERE SCHOOL_CODE = ?");
    $stmt->execute([$id]);
}

// Read
$stmt = $pdo->query("SELECT * FROM school");
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Schools Management</h2>

<!-- Create Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" name="school_name" class="form-control" placeholder="School Name" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="create" class="btn btn-primary">Add School</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Schools Table -->
<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>School Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($schools as $school): ?>
        <tr>
            <td><?= $school['SCHOOL_CODE'] ?></td>
            <td><?= htmlspecialchars($school['SCHOOL_NAME']) ?></td>
            <td>
                <a href="edit_school.php?id=<?= $school['SCHOOL_CODE'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="?delete=<?= $school['SCHOOL_CODE'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>