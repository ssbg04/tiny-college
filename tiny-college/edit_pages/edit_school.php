<?php
require_once '../db/config.php';

if(isset($_POST['update'])){
    $id = $_POST['school_code'];
    $name = $_POST['school_name'];
    
    try {
        $stmt = $pdo->prepare("UPDATE school SET SCHOOL_NAME = ? WHERE SCHOOL_CODE = ?");
        $stmt->execute([$name, $id]);
        header("Location: schools.php");
        exit();
    } catch(PDOException $e) {
        die("Update failed: " . $e->getMessage());
    }
}

try {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM school WHERE SCHOOL_CODE = ?");
    $stmt->execute([$id]);
    $school = $stmt->fetch();
    
    if(!$school) die("School not found");
} catch(PDOException $e) {
    die("Error fetching school: " . $e->getMessage());
}

require_once '../pages/school_manage/header.php';
?>

<h2>Edit School</h2>

<form method="POST">
    <input type="hidden" name="school_code" value="<?= $school['SCHOOL_CODE'] ?>">
    
    <div class="mb-3">
        <label>School Name</label>
        <input type="text" name="school_name" value="<?= htmlspecialchars($school['SCHOOL_NAME']) ?>" class="form-control" required>
    </div>
    
    <button type="submit" name="update" class="btn btn-primary">Update</button>
    <a href="../pages/school_manage/schools.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../pages/school_manage/footer.php'; ?>