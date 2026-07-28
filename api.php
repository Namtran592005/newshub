<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/includes/functions.php';

$action = $_GET['action'] ?? 'all';
$user_city = $_GET['location'] ?? '';

switch ($action) {
    case 'refresh':
        foreach (['news','finance','weather','social'] as $f) {
            $p = __DIR__ . "/cache/{$f}_cache.json";
            if (file_exists($p)) unlink($p);
        }
        $data = fetch_all_news($user_city);
        break;

    case 'weather':
        $data = ['weather' => fetch_weather($user_city)];
        break;

    case 'social':
        $data = ['social_trends' => fetch_social_trends()];
        break;

    case 'clocks':
        $data = ['world_clocks' => get_world_clocks()];
        break;

    case 'finance':
        $data = ['finance' => fetch_finance()];
        break;

    case 'stats':
        $d = fetch_all_news($user_city);
        $data = [
            'total' => $d['total'], 'source_stats' => $d['source_stats'],
            'category_stats' => $d['category_stats'], 'top_keywords' => $d['top_keywords'],
            'timeline' => $d['timeline'], 'trends' => $d['trends'],
            'finance' => $d['finance'], 'weather' => $d['weather'],
            'social_trends' => $d['social_trends'],
            'updated_at' => $d['updated_at'],
        ];
        break;

    case 'all':
    default:
        $data = fetch_all_news($user_city);
        break;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
