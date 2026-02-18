<?php
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die("Book not found!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $published_year = $_POST['published_year'];
    $isbn = $_POST['isbn'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, published_year = ?, isbn = ?, stock = ? WHERE id = ?");
    
    if ($stmt->execute([$title, $author, $published_year, $isbn, $stock, $id])) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Failed to update book.";
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Edit Book</h4>
            </div>
            <div class="card-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Published Year</label>
                            <input type="number" name="published_year" class="form-control" value="<?= $book['published_year'] ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="<?= $book['stock'] ?>" required min="0">
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">Update Book</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
