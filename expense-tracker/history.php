<?php
$pageTitle = 'History — Expense Tracker';
$currentPage = 'history';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">History</h1>
        <p class="text-muted mb-0">All recorded expenses, newest first.</p>
    </div>
    <a href="add-expense.php" class="btn btn-dark">Add Expense</a>
</div>

<form id="history-filter-form" class="row g-2 align-items-end border p-3 mb-4">
    <div class="col-md-3">
        <label for="filter-category" class="form-label">Category</label>
        <select class="form-select" id="filter-category">
            <option value="">All categories</option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="filter-date-from" class="form-label">From</label>
        <input type="date" class="form-control" id="filter-date-from">
    </div>
    <div class="col-md-3">
        <label for="filter-date-to" class="form-label">To</label>
        <input type="date" class="form-control" id="filter-date-to">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-dark">Filter</button>
        <button type="button" class="btn btn-outline-dark" id="clear-filters">Clear</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered ledger-table mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody id="history-body">
            <tr><td colspan="5" class="text-muted">Loading…</td></tr>
        </tbody>
    </table>
</div>

<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit-expense-form" novalidate>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="editExpenseModalLabel">Edit expense</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="edit-amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-category" class="form-label">Category</label>
                        <select class="form-select" id="edit-category" required></select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="edit-description" maxlength="255" required>
                    </div>
                    <div class="mb-0">
                        <label for="edit-date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit-date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
