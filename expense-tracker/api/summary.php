<?php

require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Method not allowed.', 405);
}

try {
    $pdo = getDb();

    $now = new DateTime('now');
    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
    $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);

    $year = ($year !== false && $year !== null && $year >= 2000) ? $year : (int) $now->format('Y');
    $month = ($month !== false && $month !== null && $month >= 1 && $month <= 12)
        ? $month
        : (int) $now->format('n');

    $start = sprintf('%04d-%02d-01', $year, $month);
    $endDate = (new DateTime($start))->modify('last day of this month')->format('Y-m-d');
    $daysInMonth = (int) (new DateTime($start))->format('t');
    $today = $now->format('Y-m-d');
    $isCurrentMonth = ((int) $now->format('Y') === $year && (int) $now->format('n') === $month);
    // Average per day uses elapsed days for the current month so early-month totals are not diluted.
    $elapsedDays = $isCurrentMonth ? (int) $now->format('j') : $daysInMonth;

    $totalsStmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) AS monthly_total, COUNT(*) AS transaction_count
         FROM expenses
         WHERE date BETWEEN :start AND :end'
    );
    $totalsStmt->execute(['start' => $start, 'end' => $endDate]);
    $totals = $totalsStmt->fetch();

    $monthlyTotal = (float) $totals['monthly_total'];
    $transactionCount = (int) $totals['transaction_count'];
    $avgPerDay = $elapsedDays > 0 ? $monthlyTotal / $elapsedDays : 0;

    $topStmt = $pdo->prepare(
        'SELECT c.id, c.name, c.icon, c.color, SUM(e.amount) AS total
         FROM expenses e
         INNER JOIN categories c ON c.id = e.category_id
         WHERE e.date BETWEEN :start AND :end
         GROUP BY c.id, c.name, c.icon, c.color
         ORDER BY total DESC
         LIMIT 1'
    );
    $topStmt->execute(['start' => $start, 'end' => $endDate]);
    $topCategory = $topStmt->fetch() ?: null;

    $breakdownStmt = $pdo->prepare(
        'SELECT c.id AS category_id, c.name, c.icon, c.color, COALESCE(SUM(e.amount), 0) AS total
         FROM categories c
         LEFT JOIN expenses e ON e.category_id = c.id AND e.date BETWEEN :start AND :end
         GROUP BY c.id, c.name, c.icon, c.color
         ORDER BY total DESC, c.id ASC'
    );
    $breakdownStmt->execute(['start' => $start, 'end' => $endDate]);
    $breakdown = $breakdownStmt->fetchAll();

    jsonResponse(true, [
        'year' => $year,
        'month' => $month,
        'monthly_total' => $monthlyTotal,
        'transaction_count' => $transactionCount,
        'avg_per_day' => $avgPerDay,
        'top_category' => $topCategory,
        'category_breakdown' => $breakdown,
    ], 'Summary loaded.');
} catch (Throwable $e) {
    jsonResponse(false, null, 'Unable to load summary.', 500);
}
