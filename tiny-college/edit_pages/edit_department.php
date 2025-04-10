<?php
require_once 'config.php';
require_once 'header.php';

if(isset($_POST['update'])){
    $id = $_POST['school_code'];
    $name = $_POST['school_name'];
    
    $stmt = $pdo->prepare("UPDATE school SET SCHOOL_NAME = ? WHERE SCHOOL_CODE = ?");
    $stmt->execute([$name, $id]);
    header("Location: schools.php");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM school WHERE SCHOOL_CODE = ?");
$stmt->execute([$id]);
$school = $stmt->fetch();
?>

<h2>Edit School</h2>

<form method="POST">
    <input type="hidden" name="school_code" value="<?= $school['SCHOOL_CODE'] ?>">
    
    <div class="mb-3">
        <label>School Name</label>
        <input type="text" name="school_name" value="<?= htmlspecialchars($school['SCHOOL_NAME']) ?>" class="form-control" required>
    </div>
    
    <button type="submit" name="update" class="btn btn-primary">Update</button>
    <a href="schools.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'footer.php'; ?>