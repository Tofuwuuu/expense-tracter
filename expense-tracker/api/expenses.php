<?php

require_once __DIR__ . '/_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDb();

    if ($method === 'GET') {
        handleGetExpenses($pdo);
    } elseif ($method === 'POST') {
        handleCreateExpense($pdo);
    } elseif ($method === 'PUT') {
        handleUpdateExpense($pdo);
    } elseif ($method === 'DELETE') {
        handleDeleteExpense($pdo);
    } else {
        jsonResponse(false, null, 'Method not allowed.', 405);
    }
} catch (Throwable $e) {
    jsonResponse(false, null, 'Unable to process expense request.', 500);
}

function handleGetExpenses(PDO $pdo): void
{
    $sql = 'SELECT e.id, e.category_id, e.amount, e.description, e.date, e.created_at,
                   c.name AS category_name, c.icon AS category_icon, c.color AS category_color
            FROM expenses e
            INNER JOIN categories c ON c.id = e.category_id
            WHERE 1 = 1';
    $params = [];

    $categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
    if ($categoryId !== null && $categoryId !== false && $categoryId > 0) {
        $sql .= ' AND e.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }

    $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
    if ($dateFrom !== '' && isValidDate($dateFrom)) {
        $sql .= ' AND e.date >= :date_from';
        $params['date_from'] = $dateFrom;
    }

    $dateTo = trim((string) ($_GET['date_to'] ?? ''));
    if ($dateTo !== '' && isValidDate($dateTo)) {
        $sql .= ' AND e.date <= :date_to';
        $params['date_to'] = $dateTo;
    }

    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    $sql .= ' ORDER BY e.date DESC, e.id DESC';
    if ($limit !== null && $limit !== false && $limit > 0) {
        $sql .= ' LIMIT :limit';
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    if ($limit !== null && $limit !== false && $limit > 0) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();

    jsonResponse(true, $stmt->fetchAll(), 'Expenses loaded.');
}

function handleCreateExpense(PDO $pdo): void
{
    $input = jsonInput();
    $errors = validateExpensePayload($input, $pdo);

    if ($errors) {
        jsonResponse(false, $errors, 'Please fix the highlighted fields.', 400);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO expenses (category_id, amount, description, date)
         VALUES (:category_id, :amount, :description, :date)'
    );
    $stmt->execute([
        'category_id' => (int) $input['category_id'],
        'amount' => number_format((float) $input['amount'], 2, '.', ''),
        'description' => trim((string) $input['description']),
        'date' => $input['date'],
    ]);

    $id = (int) $pdo->lastInsertId();
    jsonResponse(true, fetchExpenseById($pdo, $id), 'Expense added.', 201);
}

function handleUpdateExpense(PDO $pdo): void
{
    $input = jsonInput();
    if (!isset($input['id'])) {
        $input['id'] = $_GET['id'] ?? null;
    }

    $errors = validateExpensePayload($input, $pdo, true);
    if ($errors) {
        jsonResponse(false, $errors, 'Please fix the highlighted fields.', 400);
    }

    $id = (int) $input['id'];
    if (!expenseExists($pdo, $id)) {
        jsonResponse(false, null, 'Expense not found.', 404);
    }

    $stmt = $pdo->prepare(
        'UPDATE expenses
         SET category_id = :category_id, amount = :amount, description = :description, date = :date
         WHERE id = :id'
    );
    $stmt->execute([
        'id' => $id,
        'category_id' => (int) $input['category_id'],
        'amount' => number_format((float) $input['amount'], 2, '.', ''),
        'description' => trim((string) $input['description']),
        'date' => $input['date'],
    ]);

    jsonResponse(true, fetchExpenseById($pdo, $id), 'Expense updated.');
}

function handleDeleteExpense(PDO $pdo): void
{
    $input = jsonInput();
    $id = filter_var($input['id'] ?? ($_GET['id'] ?? null), FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        jsonResponse(false, null, 'A valid expense id is required.', 400);
    }

    if (!expenseExists($pdo, $id)) {
        jsonResponse(false, null, 'Expense not found.', 404);
    }

    $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = :id');
    $stmt->execute(['id' => $id]);

    jsonResponse(true, ['id' => $id], 'Expense deleted.');
}

function fetchExpenseById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT e.id, e.category_id, e.amount, e.description, e.date, e.created_at,
                c.name AS category_name, c.icon AS category_icon, c.color AS category_color
         FROM expenses e
         INNER JOIN categories c ON c.id = e.category_id
         WHERE e.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}
