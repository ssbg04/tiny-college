<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM department WHERE DEPT_CODE = ?");
        $stmt->execute([$id]);
        header("Location: departments.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptCode = $_POST['dept_code'] ?? null;
    $deptName = $_POST['dept_name'];
    $schoolCode = $_POST['school_code'];
    
    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO department (DEPT_NAME, SCHOOL_CODE) VALUES (?, ?)");
            $stmt->execute([$deptName, $schoolCode]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE department SET DEPT_NAME = ?, SCHOOL_CODE = ? WHERE DEPT_CODE = ?");
            $stmt->execute([$deptName, $schoolCode, $deptCode]);
        }
        header("Location: departments.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch data for display
$departments = $pdo->query("
    SELECT d.*, s.SCHOOL_NAME 
    FROM department d
    JOIN school s ON d.SCHOOL_CODE = s.SCHOOL_CODE
")->fetchAll(PDO::FETCH_ASSOC);

$schools = $pdo->query("SELECT * FROM school")->fetchAll();

require_once 'header.php';
?>

<h2>Departments Management</h2>

<!-- Create/Edit Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): 
                $editDept = $pdo->prepare("SELECT * FROM department WHERE DEPT_CODE = ?");
                $editDept->execute([$_GET['edit']]);
                $dept = $editDept->fetch();
            ?>
                <input type="hidden" name="dept_code" value="<?= $dept['DEPT_CODE'] ?>">
                <h4>Edit Department</h4>
            <?php else: ?>
                <h4>Add New Department</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Department Name</label>
                    <input type="text" name="dept_name" 
                        value="<?= isset($dept) ? htmlspecialchars($dept['DEPT_NAME']) : '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-6">
                    <label>School</label>
                    <select name="school_code" class="form-select" required>
                        <?php foreach($schools as $school): ?>
                            <option value="<?= $school['SCHOOL_CODE'] ?>"
                                <?= (isset($dept) && $dept['SCHOOL_CODE'] == $school['SCHOOL_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($school['SCHOOL_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <?php if(isset($_GET['edit'])): ?>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                        <a href="departments.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="create" class="btn btn-success">Create</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Departments Table -->
<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>Code</th>
            <th>Department Name</th>
            <th>School</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($departments as $dept): ?>
        <tr>
            <td><?= $dept['DEPT_CODE'] ?></td>
            <td><?= htmlspecialchars($dept['DEPT_NAME']) ?></td>
            <td><?= htmlspecialchars($dept['SCHOOL_NAME']) ?></td>
            <td>
                <a href="departments.php?edit=<?= $dept['DEPT_CODE'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="departments.php?delete=<?= $dept['DEPT_CODE'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this department?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>