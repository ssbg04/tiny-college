<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM course WHERE CRS_CODE = ?");
        $stmt->execute([$id]);
        header("Location: courses.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crsCode = $_POST['crs_code'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $credits = $_POST['credits'];
    $deptCode = $_POST['dept_code'];

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO course 
                (CRS_TITLE, CRS_DESCRIPTION, CRS_CREDIT, DEPT_CODE)
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $credits, $deptCode]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE course SET
                CRS_TITLE = ?,
                CRS_DESCRIPTION = ?,
                CRS_CREDIT = ?,
                DEPT_CODE = ?
                WHERE CRS_CODE = ?");
            $stmt->execute([$title, $description, $credits, $deptCode, $crsCode]);
        }
        header("Location: courses.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch data for display
$courses = $pdo->query("
    SELECT c.*, d.DEPT_NAME 
    FROM course c
    JOIN department d ON c.DEPT_CODE = d.DEPT_CODE
    ORDER BY c.CRS_CODE
")->fetchAll(PDO::FETCH_ASSOC);

$departments = $pdo->query("SELECT * FROM department")->fetchAll();

require_once 'header.php';
?>

<h2>Courses Management</h2>

<!-- Create/Edit Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): 
                $editStmt = $pdo->prepare("SELECT * FROM course WHERE CRS_CODE = ?");
                $editStmt->execute([$_GET['edit']]);
                $course = $editStmt->fetch();
            ?>
                <input type="hidden" name="crs_code" value="<?= $course['CRS_CODE'] ?>">
                <h4>Edit Course</h4>
            <?php else: ?>
                <h4>Add New Course</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label>Course Title</label>
                    <input type="text" name="title" 
                        value="<?= $course['CRS_TITLE'] ?? '' ?>" 
                        class="form-control" required maxlength="20">
                </div>
                
                <div class="col-md-4">
                    <label>Course Credits</label>
                    <input type="number" name="credits" 
                        value="<?= $course['CRS_CREDIT'] ?? '' ?>" 
                        class="form-control" min="1" max="5" required>
                </div>
                
                <div class="col-md-12">
                    <label>Description</label>
                    <textarea name="description" class="form-control" maxlength="50"
                        ><?= $course['CRS_DESCRIPTION'] ?? '' ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label>Department</label>
                    <select name="dept_code" class="form-select" required>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['DEPT_CODE'] ?>"
                                <?= (isset($course) && $course['DEPT_CODE'] == $dept['DEPT_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['DEPT_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12 mt-3">
                    <?php if(isset($_GET['edit'])): ?>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                        <a href="courses.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="create" class="btn btn-success">Create</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Courses Table -->
<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Description</th>
            <th>Credits</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($courses as $course): ?>
        <tr>
            <td><?= $course['CRS_CODE'] ?></td>
            <td><?= htmlspecialchars($course['CRS_TITLE']) ?></td>
            <td><?= htmlspecialchars($course['CRS_DESCRIPTION']) ?></td>
            <td><?= $course['CRS_CREDIT'] ?></td>
            <td><?= htmlspecialchars($course['DEPT_NAME']) ?></td>
            <td>
                <a href="courses.php?edit=<?= $course['CRS_CODE'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="courses.php?delete=<?= $course['CRS_CODE'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this course?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>