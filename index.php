<?php
require_once 'config/database.php';

// Handle Search
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM books";
$params = [];

if ($search) {
    $query .= " WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Book List</h1>
    <a href="create.php" class="btn btn-success">Add New Book</a>
</div>

<div class="card p-3 mb-4">
    <form action="index.php" method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search by title, author, or ISBN..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if($search): ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-responsive bg-white rounded shadow-sm">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Year</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($books) > 0): ?>
                <?php foreach($books as $book): ?>
                <tr>
                    <td><?= $book['id'] ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['isbn']) ?></td>
                    <td><?= $book['published_year'] ?></td>
                    <td>
                        <span class="badge bg-<?= $book['stock'] > 0 ? 'info' : 'danger' ?>">
                            <?= $book['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <button onclick="confirmDelete(<?= $book['id'] ?>)" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4">No books found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
