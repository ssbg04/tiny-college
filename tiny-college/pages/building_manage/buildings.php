
<?php
require_once '../../db/config.php';

// Handle Delete
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM building WHERE BLDG_CODE = ?");
        $stmt->execute([$id]);
        header("Location: buildings.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bldgCode = $_POST['bldg_code'] ?? null;
    $name = $_POST['name'];
    $location = $_POST['location'];

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO building (BLDG_NAME, BLDG_LOCATION) VALUES (?, ?)");
            $stmt->execute([$name, $location]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE building SET BLDG_NAME = ?, BLDG_LOCATION = ? WHERE BLDG_CODE = ?");
            $stmt->execute([$name, $location, $bldgCode]);
        }
        header("Location: buildings.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch buildings and handle edit mode
try {
    $buildings = $pdo->query("SELECT * FROM building")->fetchAll();
    
    $building = [];
    if(isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM building WHERE BLDG_CODE = ?");
        $stmt->execute([$_GET['edit']]);
        $building = $stmt->fetch();
        
        if(!$building) {
            header("Location: buildings.php");
            exit();
        }
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once 'header.php';
?>

<h2>Building Management</h2>

<!-- Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): ?>
                <input type="hidden" name="bldg_code" value="<?= $building['BLDG_CODE'] ?>">
                <h4>Edit Building</h4>
            <?php else: ?>
                <h4>Add Building</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Building Name</label>
                    <input type="text" name="name" 
                        value="<?= htmlspecialchars($building['BLDG_NAME'] ?? '') ?>" 
                        class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Location</label>
                    <input type="text" name="location" 
                        value="<?= htmlspecialchars($building['BLDG_LOCATION'] ?? '') ?>" 
                        class="form-control">
                </div>
                <div class="col-md-12">
                    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'create' ?>" 
                        class="btn btn-primary">
                        <?= isset($_GET['edit']) ? 'Update' : 'Create' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>Building Name</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($buildings as $b): ?>
        <tr>
            <td><?= $b['BLDG_CODE'] ?></td>
            <td><?= htmlspecialchars($b['BLDG_NAME']) ?></td>
            <td><?= htmlspecialchars($b['BLDG_LOCATION']) ?></td>
            <td>
                <a href="buildings.php?edit=<?= $b['BLDG_CODE'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="buildings.php?delete=<?= $b['BLDG_CODE'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this building?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>