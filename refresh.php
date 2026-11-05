<?php
/**
 * OPTIONAL cache warmer.
 *
 * You do NOT need this for the site to work — the news and events refresh
 * themselves whenever someone visits and the cache is older than the
 * 'cache_minutes' setting. This script just lets you refresh on a schedule
 * so the very first visitor after a while doesn't wait the extra second.
 *
 * If you want it: in cPanel -> "Cron Jobs", add a job that runs, say, hourly:
 *     php /home/USERNAME/public_html/refresh.php
 * (cPanel usually fills in the php path for you; replace USERNAME/public_html
 *  with your actual document-root path.)
 *
 * Safe to run any time. Prints a one-line status. Never throws.
 */

@ini_set('display_errors', '0');
header('Content-Type: text/plain; charset=utf-8');

try {
    $cfg = require __DIR__ . '/lib/config.php';
    require_once __DIR__ . '/lib/news.php';
    require_once __DIR__ . '/lib/events.php';
    @date_default_timezone_set($cfg['timezone'] ?? 'America/Toronto');

    // Force a refresh by pretending the cache may be cold: set cache_minutes to 0
    // just for this run so it always re-fetches when a URL is configured.
    $cfg['cache_minutes'] = 0;

    $n = np_news_get($cfg);
    $e = np_events_get($cfg);

    echo "ok  news=" . count($n['items'])
       . "  events=" . count($e['items'])
       . "  at=" . date('c') . "\n";
} catch (Throwable $ex) {
    echo "error (handled): " . $ex->getMessage() . "\n";
}
