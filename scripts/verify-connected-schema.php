<?php
// Run after migration: php scripts/verify-connected-schema.php
require_once __DIR__ . '/../Includes/db.php';
$pdo = db();
$checks = [
    'orphaned bookings users' => 'SELECT COUNT(*) FROM bookings b LEFT JOIN users u ON u.id=b.user_id WHERE u.id IS NULL',
    'unlinked booking spaces' => 'SELECT COUNT(*) FROM bookings WHERE space_id IS NULL',
    'mismatched booking space names' => 'SELECT COUNT(*) FROM bookings b JOIN spaces s ON s.id=b.space_id WHERE b.space <> s.name',
    'orphaned messages users' => 'SELECT COUNT(*) FROM messages m LEFT JOIN users u ON u.id=m.user_id WHERE m.user_id IS NOT NULL AND u.id IS NULL',
];
foreach ($checks as $name => $sql) {
    echo $name . ': ' . (int)$pdo->query($sql)->fetchColumn() . PHP_EOL;
}
