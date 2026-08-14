<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(bool $success, mixed $data = null, string $message = '', int $status = 200): void
{
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return $_POST ?: [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function categoryExists(PDO $pdo, int $categoryId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
    $stmt->execute(['id' => $categoryId]);
    return (bool) $stmt->fetch();
}

function expenseExists(PDO $pdo, int $expenseId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM expenses WHERE id = :id');
    $stmt->execute(['id' => $expenseId]);
    return (bool) $stmt->fetch();
}

function isValidDate(string $date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt !== false && $dt->format('Y-m-d') === $date;
}

/**
 * Validate shared expense fields. Returns errors keyed by field name.
 */
function validateExpensePayload(array $input, PDO $pdo, bool $requireId = false): array
{
    $errors = [];

    if ($requireId) {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            $errors['id'] = 'A valid expense id is required.';
        }
    }

    $amount = $input['amount'] ?? null;
    if (!is_numeric($amount) || (float) $amount <= 0) {
        $errors['amount'] = 'Amount must be a number greater than 0.';
    }

    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    if ($categoryId === false || $categoryId <= 0) {
        $errors['category_id'] = 'A valid category is required.';
    } elseif (!categoryExists($pdo, $categoryId)) {
        $errors['category_id'] = 'Selected category does not exist.';
    }

    $description = trim((string) ($input['description'] ?? ''));
    if ($description === '') {
        $errors['description'] = 'Description is required.';
    } elseif (mb_strlen($description) > 255) {
        $errors['description'] = 'Description must be 255 characters or fewer.';
    }

    $date = trim((string) ($input['date'] ?? ''));
    if ($date === '' || !isValidDate($date)) {
        $errors['date'] = 'Date must be a valid YYYY-MM-DD value.';
    }

    return $errors;
}
