<?php

require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Method not allowed.', 405);
}

try {
    $pdo = getDb();
    $stmt = $pdo->query('SELECT id, name, icon, color FROM categories ORDER BY id ASC');
    $categories = $stmt->fetchAll();
    jsonResponse(true, $categories, 'Categories loaded.');
} catch (Throwable $e) {
    jsonResponse(false, null, 'Unable to load categories.', 500);
}
