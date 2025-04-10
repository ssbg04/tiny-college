<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete_class']) && isset($_GET['delete_student'])){
    try {
        $classCode = $_GET['delete_class'];
        $stuNum = $_GET['delete_student'];
        $stmt = $pdo->prepare("DELETE FROM enroll WHERE CLASS_CODE = ? AND STU_NUM = ?");
        $stmt->execute([$classCode, $stuNum]);
        header("Location: enroll.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalClass = $_POST['original_class_code'] ?? null;
    $originalStudent = $_POST['original_stu_num'] ?? null;
    $classCode = $_POST['class_code'];
    $stuNum = $_POST['stu_num'];
    $enrollDate = $_POST['enroll_date'];
    $grade = $_POST['grade'] ?? null;

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO enroll 
                (CLASS_CODE, STU_NUM, ENROLL_DATE, ENROLL_GRADE)
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$classCode, $stuNum, $enrollDate, $grade]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE enroll SET
                CLASS_CODE = ?,
                STU_NUM = ?,
                ENROLL_DATE = ?,
                ENROLL_GRADE = ?
                WHERE CLASS_CODE = ? AND STU_NUM = ?");
            $stmt->execute([$classCode, $stuNum, $enrollDate, $grade, $originalClass, $originalStudent]);
        }
        header("Location: enroll.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch Data
try {
    $enrollments = $pdo->query("
        SELECT e.*, c.CLASS_SECTION, crs.CRS_TITLE, 
               sem.SEMESTER_TERM, sem.SEMESTER_YEAR,
               CONCAT(s.STU_FNAME, ' ', s.STU_LNAME) AS STUDENT_NAME
        FROM enroll e
        JOIN class c ON e.CLASS_CODE = c.CLASS_CODE
        JOIN course crs ON c.CRS_CODE = crs.CRS_CODE
        JOIN semester sem ON c.SEMESTER_CODE = sem.SEMESTER_CODE
        JOIN student s ON e.STU_NUM = s.STU_NUM
        ORDER BY e.ENROLL_DATE DESC
    ")->fetchAll();

    $classes = $pdo->query("
        SELECT c.*, crs.CRS_TITLE, sem.SEMESTER_TERM, sem.SEMESTER_YEAR
        FROM class c
        JOIN course crs ON c.CRS_CODE = crs.CRS_CODE
        JOIN semester sem ON c.SEMESTER_CODE = sem.SEMESTER_CODE
    ")->fetchAll();

    $students = $pdo->query("
        SELECT STU_NUM, CONCAT(STU_FNAME, ' ', STU_LNAME) AS FULL_NAME
        FROM student
        ORDER BY STU_LNAME
    ")->fetchAll();

    // Handle edit mode
    $enrollment = [];
    if(isset($_GET['edit_class']) && isset($_GET['edit_student'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM enroll 
            WHERE CLASS_CODE = ? AND STU_NUM = ?
        ");
        $stmt->execute([$_GET['edit_class'], $_GET['edit_student']]);
        $enrollment = $stmt->fetch();
        
        if(!$enrollment) {
            header("Location: enroll.php");
            exit();
        }
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once 'header.php';
?>

<h2>Enrollment Management</h2>

<!-- Enrollment Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit_class']) && isset($_GET['edit_student'])): ?>
                <input type="hidden" name="original_class_code" value="<?= $enrollment['CLASS_CODE'] ?>">
                <input type="hidden" name="original_stu_num" value="<?= $enrollment['STU_NUM'] ?>">
                <h4>Edit Enrollment</h4>
            <?php else: ?>
                <h4>Add New Enrollment</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Class</label>
                    <select name="class_code" class="form-select" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['CLASS_CODE'] ?>"
                                <?= (isset($enrollment['CLASS_CODE']) && $enrollment['CLASS_CODE'] == $c['CLASS_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['CRS_TITLE']) ?> - 
                                <?= $c['SEMESTER_TERM'] ?> <?= $c['SEMESTER_YEAR'] ?> 
                                (Section <?= $c['CLASS_SECTION'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label>Student</label>
                    <select name="stu_num" class="form-select" required>
                        <?php foreach($students as $s): ?>
                            <option value="<?= $s['STU_NUM'] ?>"
                                <?= (isset($enrollment['STU_NUM']) && $enrollment['STU_NUM'] == $s['STU_NUM']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['FULL_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label>Enrollment Date</label>
                    <input type="date" name="enroll_date" 
                        value="<?= htmlspecialchars($enrollment['ENROLL_DATE'] ?? date('Y-m-d')) ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label>Grade (0-100)</label>
                    <input type="number" name="grade" 
                        value="<?= htmlspecialchars($enrollment['ENROLL_GRADE'] ?? '') ?>" 
                        class="form-control" min="0" max="100">
                </div>
                
                <div class="col-md-12 mt-3">
                    <button type="submit" name="<?= isset($_GET['edit_class']) ? 'update' : 'create' ?>" 
                        class="btn btn-primary">
                        <?= isset($_GET['edit_class']) ? 'Update' : 'Create' ?>
                    </button>
                    <?php if(isset($_GET['edit_class'])): ?>
                        <a href="enroll.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Enrollments Table -->
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Enrollment Date</th>
            <th>Grade</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($enrollments as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['STUDENT_NAME']) ?></td>
            <td><?= htmlspecialchars($e['CRS_TITLE']) ?></td>
            <td>
                <?= match($e['SEMESTER_TERM']) {
                    1 => 'Spring',
                    2 => 'Summer',
                    3 => 'Fall'
                } ?> 
                <?= $e['SEMESTER_YEAR'] ?>
            </td>
            <td><?= date('M d, Y', strtotime($e['ENROLL_DATE'])) ?></td>
            <td><?= $e['ENROLL_GRADE'] ?: 'N/A' ?></td>
            <td>
                <a href="enroll.php?edit_class=<?= $e['CLASS_CODE'] ?>&edit_student=<?= $e['STU_NUM'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="enroll.php?delete_class=<?= $e['CLASS_CODE'] ?>&delete_student=<?= $e['STU_NUM'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this enrollment?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>