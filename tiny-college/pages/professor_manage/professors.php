<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM professor WHERE PROF_NUM = ?");
        $stmt->execute([$id]);
        header("Location: professors.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profNum = $_POST['prof_num'] ?? null;
    $schoolCode = $_POST['school_code'];
    $deptCode = $_POST['dept_code'];
    $specialty = $_POST['specialty'];
    $rank = $_POST['rank'];
    $lname = $_POST['lname'];
    $fname = $_POST['fname'];
    $initial = $_POST['initial'];
    $email = $_POST['email'];

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO professor 
                (SCHOOL_CODE, DEPT_CODE, PROF_SPECIALTY, PROF_RANK, PROF_LNAME, PROF_FNAME, PROF_INITIAL, PROF_EMAIL)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$schoolCode, $deptCode, $specialty, $rank, $lname, $fname, $initial, $email]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE professor SET
                SCHOOL_CODE = ?,
                DEPT_CODE = ?,
                PROF_SPECIALTY = ?,
                PROF_RANK = ?,
                PROF_LNAME = ?,
                PROF_FNAME = ?,
                PROF_INITIAL = ?,
                PROF_EMAIL = ?
                WHERE PROF_NUM = ?");
            $stmt->execute([$schoolCode, $deptCode, $specialty, $rank, $lname, $fname, $initial, $email, $profNum]);
        }
        header("Location: professors.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch data for display
$professors = $pdo->query("
    SELECT p.*, s.SCHOOL_NAME, d.DEPT_NAME 
    FROM professor p
    JOIN school s ON p.SCHOOL_CODE = s.SCHOOL_CODE
    JOIN department d ON p.DEPT_CODE = d.DEPT_CODE
")->fetchAll(PDO::FETCH_ASSOC);

$schools = $pdo->query("SELECT * FROM school")->fetchAll();
$departments = $pdo->query("SELECT * FROM department")->fetchAll();

require_once 'header.php';
?>

<h2>Professors Management</h2>

<!-- Create/Edit Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): 
                $editProf = $pdo->prepare("SELECT * FROM professor WHERE PROF_NUM = ?");
                $editProf->execute([$_GET['edit']]);
                $prof = $editProf->fetch();
            ?>
                <input type="hidden" name="prof_num" value="<?= $prof['PROF_NUM'] ?>">
                <h4>Edit Professor</h4>
            <?php else: ?>
                <h4>Add New Professor</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label>First Name</label>
                    <input type="text" name="fname" 
                        value="<?= $prof['PROF_FNAME'] ?? '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label>Last Name</label>
                    <input type="text" name="lname" 
                        value="<?= $prof['PROF_LNAME'] ?? '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-2">
                    <label>Initial</label>
                    <input type="text" name="initial" 
                        value="<?= $prof['PROF_INITIAL'] ?? '' ?>" 
                        class="form-control" maxlength="4">
                </div>
                
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" 
                        value="<?= $prof['PROF_EMAIL'] ?? '' ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-3">
                    <label>Specialty</label>
                    <input type="text" name="specialty" 
                        value="<?= $prof['PROF_SPECIALTY'] ?? '' ?>" 
                        class="form-control" maxlength="100" required>
                </div>
                
                <div class="col-md-3">
                    <label>Rank</label>
                    <select name="rank" class="form-select" required>
                        <option value="Assistant" <?= ($prof['PROF_RANK'] ?? '') === 'Assistant' ? 'selected' : '' ?>>Assistant</option>
                        <option value="Associate" <?= ($prof['PROF_RANK'] ?? '') === 'Associate' ? 'selected' : '' ?>>Associate</option>
                        <option value="Full" <?= ($prof['PROF_RANK'] ?? '') === 'Full' ? 'selected' : '' ?>>Full</option>
                        <option value="Emeritus" <?= ($prof['PROF_RANK'] ?? '') === 'Emeritus' ? 'selected' : '' ?>>Emeritus</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label>School</label>
                    <select name="school_code" class="form-select" required>
                        <?php foreach($schools as $school): ?>
                            <option value="<?= $school['SCHOOL_CODE'] ?>"
                                <?= (isset($prof) && $prof['SCHOOL_CODE'] == $school['SCHOOL_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($school['SCHOOL_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label>Department</label>
                    <select name="dept_code" class="form-select" required>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['DEPT_CODE'] ?>"
                                <?= (isset($prof) && $prof['DEPT_CODE'] == $dept['DEPT_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['DEPT_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12 mt-3">
                    <?php if(isset($_GET['edit'])): ?>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                        <a href="professors.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="create" class="btn btn-success">Create</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Professors Table -->
<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Rank</th>
            <th>Specialty</th>
            <th>School</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($professors as $prof): ?>
        <tr>
            <td><?= $prof['PROF_NUM'] ?></td>
            <td><?= htmlspecialchars($prof['PROF_FNAME'] . ' ' . $prof['PROF_LNAME']) ?></td>
            <td><?= htmlspecialchars($prof['PROF_EMAIL']) ?></td>
            <td><?= htmlspecialchars($prof['PROF_RANK']) ?></td>
            <td><?= htmlspecialchars($prof['PROF_SPECIALTY']) ?></td>
            <td><?= htmlspecialchars($prof['SCHOOL_NAME']) ?></td>
            <td><?= htmlspecialchars($prof['DEPT_NAME']) ?></td>
            <td>
                <a href="professors.php?edit=<?= $prof['PROF_NUM'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="professors.php?delete=<?= $prof['PROF_NUM'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this professor?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>