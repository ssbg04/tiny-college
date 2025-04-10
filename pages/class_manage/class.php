<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM class WHERE CLASS_CODE = ?");
        $stmt->execute([$id]);
        header("Location: class.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classCode = $_POST['class_code'] ?? null;
    $section = $_POST['section'];
    $time = $_POST['time'];
    $crsCode = $_POST['crs_code'];
    $semesterCode = $_POST['semester_code'];

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO class 
                (CLASS_SECTION, CLASS_TIME, CRS_CODE, SEMESTER_CODE)
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$section, $time, $crsCode, $semesterCode]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE class SET
                CLASS_SECTION = ?,
                CLASS_TIME = ?,
                CRS_CODE = ?,
                SEMESTER_CODE = ?
                WHERE CLASS_CODE = ?");
            $stmt->execute([$section, $time, $crsCode, $semesterCode, $classCode]);
        }
        header("Location: class.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch Data
try {
    // Get all classes with related info
    $classes = $pdo->query("
        SELECT c.*, 
               crs.CRS_TITLE,
               sem.SEMESTER_TERM,
               sem.SEMESTER_YEAR
        FROM class c
        JOIN course crs ON c.CRS_CODE = crs.CRS_CODE
        JOIN semester sem ON c.SEMESTER_CODE = sem.SEMESTER_CODE
        ORDER BY sem.SEMESTER_YEAR DESC, sem.SEMESTER_TERM DESC
    ")->fetchAll();

    // Get courses for dropdown
    $courses = $pdo->query("SELECT * FROM course")->fetchAll();

    // Get semesters for dropdown
    $semesters = $pdo->query("SELECT * FROM semester")->fetchAll();

    // Handle edit mode
    $class = [];
    if(isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM class WHERE CLASS_CODE = ?");
        $stmt->execute([$_GET['edit']]);
        $class = $stmt->fetch();
        
        if(!$class) {
            header("Location: class.php");
            exit();
        }
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once 'header.php';
?>

<h2>Class Management</h2>

<!-- Class Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): ?>
                <input type="hidden" name="class_code" value="<?= $class['CLASS_CODE'] ?>">
                <h4>Edit Class</h4>
            <?php else: ?>
                <h4>Add New Class</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label>Section</label>
                    <input type="text" name="section" 
                        value="<?= htmlspecialchars($class['CLASS_SECTION'] ?? '') ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label>Class Time</label>
                    <input type="text" name="time" 
                        value="<?= htmlspecialchars($class['CLASS_TIME'] ?? '') ?>" 
                        class="form-control" placeholder="HH:MM - HH:MM" required>
                </div>
                
                <div class="col-md-6">
                    <label>Course</label>
                    <select name="crs_code" class="form-select" required>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= $c['CRS_CODE'] ?>"
                                <?= (isset($class['CRS_CODE']) && $class['CRS_CODE'] == $c['CRS_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['CRS_TITLE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label>Semester</label>
                    <select name="semester_code" class="form-select" required>
                        <?php foreach($semesters as $s): ?>
                            <option value="<?= $s['SEMESTER_CODE'] ?>"
                                <?= (isset($class['SEMESTER_CODE']) && $class['SEMESTER_CODE'] == $s['SEMESTER_CODE']) ? 'selected' : '' ?>>
                                <?= $s['SEMESTER_TERM'] ?> <?= $s['SEMESTER_YEAR'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12 mt-3">
                    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'create' ?>" 
                        class="btn btn-primary">
                        <?= isset($_GET['edit']) ? 'Update' : 'Create' ?>
                    </button>
                    <?php if(isset($_GET['edit'])): ?>
                        <a href="class.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Classes Table -->
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Code</th>
            <th>Course</th>
            <th>Section</th>
            <th>Time</th>
            <th>Semester</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($classes as $c): ?>
        <tr>
            <td><?= $c['CLASS_CODE'] ?></td>
            <td><?= htmlspecialchars($c['CRS_TITLE']) ?></td>
            <td><?= htmlspecialchars($c['CLASS_SECTION']) ?></td>
            <td><?= htmlspecialchars($c['CLASS_TIME']) ?></td>
            <td><?= $c['SEMESTER_TERM'] ?> <?= $c['SEMESTER_YEAR'] ?></td>
            <td>
                <a href="class.php?edit=<?= $c['CLASS_CODE'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="class.php?delete=<?= $c['CLASS_CODE'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this class?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>