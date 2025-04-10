<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM student WHERE STU_NUM = ?");
        $stmt->execute([$id]);
        header("Location: students.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stuNum = $_POST['stu_num'] ?? null;
    $lname = $_POST['lname'];
    $fname = $_POST['fname'];
    $initial = $_POST['initial'];
    $email = $_POST['email'];
    $deptCode = isset($_POST['dept_code']) && $_POST['dept_code'] !== '' ? (int)$_POST['dept_code'] : null;
    $profNum = isset($_POST['prof_num']) && $_POST['prof_num'] !== '' ? (int)$_POST['prof_num'] : null;

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO student 
                (STU_LNAME, STU_FNAME, STU_INITIAL, STU_EMAIL, DEPT_CODE, PROF_NUM)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$lname, $fname, $initial, $email, $deptCode, $profNum]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE student SET
                STU_LNAME = ?,
                STU_FNAME = ?,
                STU_INITIAL = ?,
                STU_EMAIL = ?,
                DEPT_CODE = ?,
                PROF_NUM = ?
                WHERE STU_NUM = ?");
            $stmt->execute([$lname, $fname, $initial, $email, $deptCode, $profNum, $stuNum]);
        }
        header("Location: students.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch data for display
$students = $pdo->query("
    SELECT s.*, d.DEPT_NAME, 
           CONCAT(p.PROF_FNAME, ' ', p.PROF_LNAME) AS PROF_NAME
    FROM student s
    LEFT JOIN department d ON s.DEPT_CODE = d.DEPT_CODE
    LEFT JOIN professor p ON s.PROF_NUM = p.PROF_NUM
    ORDER BY s.STU_LNAME, s.STU_FNAME
")->fetchAll(PDO::FETCH_ASSOC);

$departments = $pdo->query("SELECT * FROM department")->fetchAll();
$professors = $pdo->query("SELECT * FROM professor")->fetchAll();

require_once 'header.php';
?>

<h2>Students Management</h2>

<!-- Create/Edit Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): 
                $editStmt = $pdo->prepare("SELECT * FROM student WHERE STU_NUM = ?");
                $editStmt->execute([$_GET['edit']]);
                $student = $editStmt->fetch();
            ?>
                <input type="hidden" name="stu_num" value="<?= $student['STU_NUM'] ?>">
                <h4>Edit Student</h4>
            <?php else: ?>
                <h4>Add New Student</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label>First Name</label>
                    <input type="text" name="fname" 
                        value="<?= $student['STU_FNAME'] ?? '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label>Last Name</label>
                    <input type="text" name="lname" 
                        value="<?= $student['STU_LNAME'] ?? '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-2">
                    <label>Initial</label>
                    <input type="text" name="initial" 
                        value="<?= $student['STU_INITIAL'] ?? '' ?>" 
                        class="form-control" maxlength="4">
                </div>
                
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" 
                        value="<?= $student['STU_EMAIL'] ?? '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-6">
                    <label>Department</label>
                    <select name="dept_code" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['DEPT_CODE'] ?>"
                                <?= (isset($student) && $student['DEPT_CODE'] == $dept['DEPT_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['DEPT_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label>Advisor</label>
                    <select name="prof_num" class="form-select">
                        <option value="">Select Advisor</option>
                        <?php foreach($professors as $prof): ?>
                            <option value="<?= $prof['PROF_NUM'] ?>"
                                <?= (isset($student) && $student['PROF_NUM'] == $prof['PROF_NUM']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prof['PROF_FNAME'] . ' ' . $prof['PROF_LNAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12 mt-3">
                    <?php if(isset($_GET['edit'])): ?>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="create" class="btn btn-success">Create</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Advisor</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($students as $stu): ?>
        <tr>
            <td><?= $stu['STU_NUM'] ?></td>
            <td><?= htmlspecialchars($stu['STU_FNAME'] . ' ' . $stu['STU_LNAME']) ?></td>
            <td><?= htmlspecialchars($stu['STU_EMAIL']) ?></td>
            <td><?= $stu['DEPT_NAME'] ?? 'N/A' ?></td>
            <td><?= $stu['PROF_NAME'] ?? 'N/A' ?></td>
            <td>
                <a href="students.php?edit=<?= $stu['STU_NUM'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="students.php?delete=<?= $stu['STU_NUM'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this student?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>