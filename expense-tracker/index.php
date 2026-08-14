<?php
$pageTitle = 'Dashboard — Expense Tracker';
$currentPage = 'dashboard';
$includeChartJs = true;
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Ledger</h1>
        <p class="text-muted mb-0" id="dashboard-month-label">Current month</p>
    </div>
    <a href="add-expense.php" class="btn btn-dark">Add Expense</a>
</div>

<section class="ledger-stat mb-4">
    <p class="stat-label mb-1">This month total</p>
    <p class="stat-hero text-expense mb-0" id="stat-month-total">₱0.00</p>
</section>

<div class="row g-0 border mb-4">
    <div class="col-md-4 ledger-cell">
        <p class="stat-label mb-1">Transactions</p>
        <p class="stat-value mb-0" id="stat-count">0</p>
    </div>
    <div class="col-md-4 ledger-cell">
        <p class="stat-label mb-1">Top category</p>
        <p class="stat-value mb-0" id="stat-top-category">—</p>
    </div>
    <div class="col-md-4 ledger-cell">
        <p class="stat-label mb-1">Average per day</p>
        <p class="stat-value text-expense mb-0" id="stat-avg-day">₱0.00</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <h2 class="section-title">Recent expenses</h2>
        <div class="table-responsive">
            <table class="table table-bordered ledger-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody id="recent-expenses-body">
                    <tr><td colspan="4" class="text-muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <h2 class="section-title">Category breakdown</h2>
        <div class="chart-wrap">
            <canvas id="category-chart" aria-label="Spending by category"></canvas>
            <p id="chart-empty" class="text-muted d-none mb-0">No spending recorded this month.</p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
