<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/includes/functions.php';

$action = $_GET['action'] ?? 'all';
$user_city = $_GET['location'] ?? '';

switch ($action) {
    case 'refresh':
        $data = load_cached_data();
        $data['_refreshed'] = time();
        break;

    case 'weather':
        $cached = load_cached_data();
        $data = ['weather' => $cached['weather']];
        break;

    case 'social':
        $cache = __DIR__ . '/cache/social_cache.json';
        if (file_exists($cache)) {
            $data = ['social_trends' => json_decode(file_get_contents($cache), true)];
        } else {
            $data = ['social_trends' => ['youtube'=>[],'tiktok'=>[],'facebook'=>[],'google'=>[]]];
        }
        break;

    case 'clocks':
        $data = ['world_clocks' => get_world_clocks()];
        break;

    case 'finance':
        $cache = __DIR__ . '/cache/finance_cache.json';
        if (file_exists($cache)) {
            $data = ['finance' => json_decode(file_get_contents($cache), true)];
        } else {
            $data = ['finance' => ['indices'=>[],'gold'=>[],'currency'=>[],'commodities'=>[],'petrol'=>[]]];
        }
        break;

    case 'stats':
        $d = load_cached_data();
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
        $data = load_cached_data();
        if ($user_city) {
            $data['weather'] = fetch_weather($user_city);
            $data['user_city'] = $user_city;
            $data['user_region'] = detect_region($user_city);
        }
        break;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
