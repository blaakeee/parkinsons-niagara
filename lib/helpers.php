<?php
/**
 * Shared helpers for News and Events.
 * Everything here is written to FAIL SAFE: if anything goes wrong,
 * callers get back the last good cached data (or an empty list) and
 * the page keeps working. Nothing in here should ever throw up to the page.
 */

/** Absolute path to the cache directory (created if missing). */
function np_cache_dir() {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

/**
 * Fetch a URL and return its body as a string, or null on any failure.
 * Uses cURL if available, falls back to file_get_contents. Short timeouts
 * so a slow Google never hangs a page load.
 */
function np_http_get($url, $timeout = 8) {
    if (!$url) return null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_USERAGENT      => 'ParkinsonsNiagara-Site/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body !== false && $code >= 200 && $code < 400) {
            return $body;
        }
        return null;
    }

    // Fallback
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'user_agent' => 'ParkinsonsNiagara-Site/1.0']]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? null : $body;
}

/**
 * Load a cached payload for a given key. Returns an array:
 *   ['items' => [...], 'fetched_at' => int|null, 'ok' => bool]
 * Never throws; missing/corrupt cache returns empty.
 */
function np_cache_load($key) {
    $file = np_cache_dir() . '/' . $key . '.json';
    if (!is_file($file)) {
        return ['items' => [], 'fetched_at' => null, 'ok' => false];
    }
    $raw = @file_get_contents($file);
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data) || !isset($data['items'])) {
        return ['items' => [], 'fetched_at' => null, 'ok' => false];
    }
    $data['ok'] = true;
    return $data;
}

/**
 * Write a cached payload (items + fetched_at timestamp). Best-effort.
 */
function np_cache_save($key, array $items) {
    $file = np_cache_dir() . '/' . $key . '.json';
    $payload = json_encode(['items' => $items, 'fetched_at' => time()]);
    @file_put_contents($file, $payload, LOCK_EX);
}

/** Is the cache older than cache_minutes (so we should try a refresh)? */
function np_cache_is_cold($cache, $cache_minutes) {
    if (empty($cache['fetched_at'])) return true;
    return (time() - (int)$cache['fetched_at']) > ($cache_minutes * 60);
}

/** Days since last successful fetch, or null if never. */
function np_days_since($fetched_at) {
    if (!$fetched_at) return null;
    return (int) floor((time() - (int)$fetched_at) / 86400);
}

/** Safe HTML output. */
function np_e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * Render the small "last updated / stale" note for a block.
 * Returns an HTML string. Healthy = quiet muted text; stale = amber warning.
 */
function np_freshness_note($fetched_at, $stale_after_days) {
    if (!$fetched_at) return '';
    $days = np_days_since($fetched_at);
    if ($days === null) return '';

    if ($days >= $stale_after_days) {
        $label = $days === 0 ? 'today'
               : ($days === 1 ? '1 day ago' : $days . ' days ago');
        return '<span class="freshness stale" role="status">'
             . 'Last updated ' . np_e($label)
             . ' — this may be out of date.</span>';
    }

    if ($days <= 0) {
        $label = 'today';
    } elseif ($days === 1) {
        $label = 'yesterday';
    } else {
        $label = $days . ' days ago';
    }
    return '<span class="freshness fresh">Updated ' . np_e($label) . '</span>';
}
