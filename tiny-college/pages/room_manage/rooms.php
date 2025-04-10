<?php
require_once '../../db/config.php';

// Handle Delete Operation
if(isset($_GET['delete'])){
    try {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM room WHERE ROOM_CODE = ?");
        $stmt->execute([$id]);
        header("Location: rooms.php");
        exit();
    } catch(PDOException $e) {
        die("Delete failed: " . $e->getMessage());
    }
}

// Handle Create/Update Operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomCode = $_POST['room_code'] ?? null;
    $roomType = $_POST['room_type'];
    $bldgCode = $_POST['bldg_code'];

    try {
        if(isset($_POST['create'])) {
            $stmt = $pdo->prepare("INSERT INTO room (ROOM_TYPE, BLDG_CODE) VALUES (?, ?)");
            $stmt->execute([$roomType, $bldgCode]);
        } elseif(isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE room SET ROOM_TYPE = ?, BLDG_CODE = ? WHERE ROOM_CODE = ?");
            $stmt->execute([$roomType, $bldgCode, $roomCode]);
        }
        header("Location: rooms.php");
        exit();
    } catch(PDOException $e) {
        die("Operation failed: " . $e->getMessage());
    }
}

// Fetch data
try {
    // Get all rooms with building names
    $rooms = $pdo->query("
        SELECT r.*, b.BLDG_NAME 
        FROM room r
        JOIN building b ON r.BLDG_CODE = b.BLDG_CODE
        ORDER BY r.ROOM_CODE
    ")->fetchAll();

    // Get buildings for dropdown
    $buildings = $pdo->query("SELECT * FROM building")->fetchAll();

    // Handle edit mode
    $room = [];
    if(isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM room WHERE ROOM_CODE = ?");
        $stmt->execute([$_GET['edit']]);
        $room = $stmt->fetch();
        
        if(!$room) {
            header("Location: rooms.php");
            exit();
        }
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once 'header.php';
?>

<h2>Room Management</h2>

<!-- Room Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST">
            <?php if(isset($_GET['edit'])): ?>
                <input type="hidden" name="room_code" value="<?= $room['ROOM_CODE'] ?>">
                <h4>Edit Room</h4>
            <?php else: ?>
                <h4>Add New Room</h4>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Room Type</label>
                    <input type="text" name="room_type" 
                        value="<?= htmlspecialchars($room['ROOM_TYPE'] ?? '') ?>" 
                        class="form-control" required>
                </div>
                
                <div class="col-md-6">
                    <label>Building</label>
                    <select name="bldg_code" class="form-select" required>
                        <?php foreach($buildings as $b): ?>
                            <option value="<?= $b['BLDG_CODE'] ?>"
                                <?= (isset($room['BLDG_CODE']) && $room['BLDG_CODE'] == $b['BLDG_CODE']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['BLDG_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12 mt-3">
                    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'create' ?>" 
                        class="btn btn-primary">
                        <?= isset($_GET['edit']) ? 'Update' : 'Create' ?>
                    </button>
                    <?php if(isset($_GET['edit'])): ?>
                        <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Rooms Table -->
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Code</th>
            <th>Type</th>
            <th>Building</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rooms as $r): ?>
        <tr>
            <td><?= $r['ROOM_CODE'] ?></td>
            <td><?= htmlspecialchars($r['ROOM_TYPE']) ?></td>
            <td><?= htmlspecialchars($r['BLDG_NAME']) ?></td>
            <td>
                <a href="rooms.php?edit=<?= $r['ROOM_CODE'] ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="rooms.php?delete=<?= $r['ROOM_CODE'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('Delete this room?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>