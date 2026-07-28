<?php
/**
 * Cron job endpoint for Ofelia scheduler.
 * Regenerates all cache files so the dashboard always serves fresh data.
 * Usage:   docker exec php php /app/cron.php
 *   or:    php cron.php
 */
require_once __DIR__ . '/includes/functions.php';

$start = microtime(true);

// Clear all cache to force regeneration
$cacheDir = __DIR__ . '/cache';
foreach (glob($cacheDir . '/*.json') as $f) {
    if (basename($f) !== '.gitkeep') unlink($f);
}

// Regenerate all data (this calls fetch_weather, fetch_finance, fetch_social_trends internally)
$data = fetch_all_news();

$elapsed = round(microtime(true) - $start, 2);
$count = count($data['articles'] ?? []);
$breaking = count($data['breaking'] ?? []);
$weather = count($data['weather'] ?? []);

$output = sprintf(
    "[%s] OK — %d articles, %d breaking, %d cities weather, %.2fs\n",
    date('Y-m-d H:i:s'), $count, $breaking, $weather, $elapsed
);

if (PHP_SAPI === 'cli') {
    echo $output;
} else {
    header('Content-Type: text/plain');
    echo $output;
}
