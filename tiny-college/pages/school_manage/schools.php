<?php
require_once '../../db/config.php';
require_once 'header.php';

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

<?php require_once 'footer.php'; ?>