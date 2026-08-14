<?php
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Expense Tracker') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-md border-bottom">
    <div class="container">
        <a class="navbar-brand" href="index.php">Expense Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link<?= $currentPage === 'dashboard' ? ' active' : '' ?>" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $currentPage === 'add' ? ' active' : '' ?>" href="add-expense.php">Add Expense</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $currentPage === 'history' ? ' active' : '' ?>" href="history.php">History</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <div id="alert-banner" class="alert d-none" role="alert"></div>
