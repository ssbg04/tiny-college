<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM semester WHERE SEMESTER_CODE = ?");
        $stmt->execute([$id]);
        header("Location: semester.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semesterCode = $_POST['semester_code'] ?? null;
    $year = $_POST['year'];
    $term = $_POST['term'];
    $startDate = strtotime($_POST['start_date']);
    $endDate = strtotime($_POST['end_date']);

    // Validate dates
    if(!$startDate || !$endDate || $startDate > $endDate) {
        die("Invalid date range");
    }

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO semester 
                (SEMESTER_YEAR, SEMESTER_TERM, SEMESTER_START_DATE, SEMESTER_END_DATE)
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$year, $term, $startDate, $endDate]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE semester SET
                SEMESTER_YEAR = ?,
                SEMESTER_TERM = ?,
                SEMESTER_START_DATE = ?,
                SEMESTER_END_DATE = ?
                WHERE SEMESTER_CODE = ?");
            $stmt->execute([$year, $term, $startDate, $endDate, $semesterCode]);
        }
        header("Location: semester.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch data
try {
    $semesters = $pdo->query("
        SELECT *, 
               FROM_UNIXTIME(SEMESTER_START_DATE) AS start_date,
               FROM_UNIXTIME(SEMESTER_END_DATE) AS end_date
        FROM semester
        ORDER BY SEMESTER_YEAR DESC, SEMESTER_TERM DESC
    ")->fetchAll();

    // Handle edit mode
    $semester = [];
    if(isset($_GET['edit'])) {
        $stmt = $pdo->prepare("
            SELECT *, 
                   FROM_UNIXTIME(SEMESTER_START_DATE) AS start_date,
                   FROM_UNIXTIME(SEMESTER_END_DATE) AS end_date
            FROM semester 
            WHERE SEMESTER_CODE = ?
        ");
        $stmt->execute([$_GET['edit']]);
        $semester = $stmt->fetch();
        
        if(!$semester) {
            header("Location: semester.php");
            exit();
        }
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once 'header.php';
?>

<h2>Semester Management</h2>

<!-- Semester Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): ?>
                <input type="hidden" name="semester_code" value="<?= $semester['SEMESTER_CODE'] ?>">
                <h4>Edit Semester</h4>
            <?php else: ?>
                <h4>Add New Semester</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-3">
                    <label>Year</label>
                    <input type="number" name="year" 
                        value="<?= htmlspecialchars($semester['SEMESTER_YEAR'] ?? date('Y')) ?>" 
                        class="form-control" min="2000" max="2100" required>
                </div>
                
                <div class="col-md-3">
                    <label>Term</label>
                    <select name="term" class="form-select" required>
                        <option value="1" <?= ($semester['SEMESTER_TERM'] ?? '') == 1 ? 'selected' : '' ?>>Spring</option>
                        <option value="2" <?= ($semester['SEMESTER_TERM'] ?? '') == 2 ? 'selected' : '' ?>>Summer</option>
                        <option value="3" <?= ($semester['SEMESTER_TERM'] ?? '') == 3 ? 'selected' : '' ?>>Fall</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" 
                        value="<?= htmlspecialchars($semester['start_date'] ?? '') ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-3">
                    <label>End Date</label>
                    <input type="date" name="end_date" 
                        value="<?= htmlspecialchars($semester['end_date'] ?? '') ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-12 mt-3">
                    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'create' ?>" 
                        class="btn btn-primary">
                        <?= isset($_GET['edit']) ? 'Update' : 'Create' ?>
                    </button>
                    <?php if(isset($_GET['edit'])): ?>
                        <a href="semester.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Semesters Table -->
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Code</th>
            <th>Year</th>
            <th>Term</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($semesters as $s): ?>
        <tr>
            <td><?= $s['SEMESTER_CODE'] ?></td>
            <td><?= $s['SEMESTER_YEAR'] ?></td>
            <td><?= match($s['SEMESTER_TERM']) {1 => 'Spring', 2 => 'Summer', 3 => 'Fall'} ?></td>
            <td><?= date('M d, Y', strtotime($s['start_date'])) ?></td>
            <td><?= date('M d, Y', strtotime($s['end_date'])) ?></td>
            <td>
                <a href="semester.php?edit=<?= $s['SEMESTER_CODE'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="semester.php?delete=<?= $s['SEMESTER_CODE'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this semester?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>