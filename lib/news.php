<?php
/**
 * News: read a published Google Sheet (CSV) into a list of items.
 *
 * Public function: np_news_get($config) -> [
 *     'items'      => [ ['title','body','date','link'], ... ],  // newest first
 *     'fetched_at' => int|null,
 *     'stale_days' => int,
 *     'configured' => bool,     // false if no URL set yet
 * ]
 *
 * Fail-safe: any error returns the last good cache (or empty). Never throws.
 */

require_once __DIR__ . '/helpers.php';

function np_news_parse_csv($csv) {
    // Parse CSV string into rows, honouring quotes/commas/newlines.
    $items = [];
    $fh = fopen('php://temp', 'r+');
    fwrite($fh, $csv);
    rewind($fh);

    $header = null;
    while (($row = fgetcsv($fh, 0, ',', '"', '"')) !== false) {
        if ($row === [null] || $row === false) continue;           // blank line
        if ($header === null) {
            $header = array_map(function ($h) {
                return strtolower(trim((string)$h));
            }, $row);
            continue;
        }
        // Skip fully-empty rows
        $joined = trim(implode('', array_map('strval', $row)));
        if ($joined === '') continue;

        $assoc = [];
        foreach ($header as $i => $key) {
            $assoc[$key] = isset($row[$i]) ? trim((string)$row[$i]) : '';
        }
        if (($assoc['title'] ?? '') === '') continue;              // title required

        $items[] = [
            'title' => $assoc['title'] ?? '',
            'body'  => $assoc['body']  ?? '',
            'date'  => $assoc['date']  ?? '',
            'link'  => $assoc['link']  ?? '',
        ];
    }
    fclose($fh);
    return $items;
}

function np_news_sort(array $items) {
    // Newest first when a parseable date exists; undated items keep order after dated ones.
    usort($items, function ($a, $b) {
        $ta = strtotime($a['date'] ?? '') ?: 0;
        $tb = strtotime($b['date'] ?? '') ?: 0;
        return $tb <=> $ta;
    });
    return $items;
}

function np_news_get($config) {
    $url   = trim($config['news_csv_url'] ?? '');
    $stale = (int)($config['stale_after_days'] ?? 7);
    $key   = 'news';

    $cache = np_cache_load($key);

    if ($url === '') {
        return [
            'items'      => $cache['items'] ?? [],
            'fetched_at' => $cache['fetched_at'] ?? null,
            'stale_days' => $stale,
            'configured' => false,
        ];
    }

    // Refresh if the cache is cold. Any failure keeps the old cache.
    if (np_cache_is_cold($cache, (int)($config['cache_minutes'] ?? 60))) {
        $csv = np_http_get($url);
        if ($csv !== null && trim($csv) !== '') {
            $parsed = np_news_parse_csv($csv);
            if (!empty($parsed)) {
                $parsed = np_news_sort($parsed);
                np_cache_save($key, $parsed);
                $cache = ['items' => $parsed, 'fetched_at' => time()];
            }
            // If parse came back empty, we keep the previous good cache rather
            // than blanking the section on a transient hiccup.
        }
    }

    return [
        'items'      => $cache['items'] ?? [],
        'fetched_at' => $cache['fetched_at'] ?? null,
        'stale_days' => $stale,
        'configured' => true,
    ];
}
