<?php
$pageTitle = 'Add Expense — Expense Tracker';
$currentPage = 'add';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title mb-1">Add expense</h1>
<p class="text-muted mb-4">Record a new line in the ledger.</p>

<form id="add-expense-form" class="ledger-form" novalidate>
    <div class="mb-3">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
        <div class="invalid-feedback" id="amount-error">Amount must be a positive number.</div>
    </div>
    <div class="mb-3">
        <label for="category_id" class="form-label">Category</label>
        <select class="form-select" id="category_id" name="category_id" required>
            <option value="">Loading categories…</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <input type="text" class="form-control" id="description" name="description" maxlength="255" required>
    </div>
    <div class="mb-4">
        <label for="date" class="form-label">Date</label>
        <input type="date" class="form-control" id="date" name="date" required>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-dark" id="submit-expense-btn">Save expense</button>
        <a href="index.php" class="btn btn-outline-dark">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
