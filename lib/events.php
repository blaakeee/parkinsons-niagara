<?php
/**
 * Events: read a public Google Calendar .ics feed into upcoming events.
 *
 * Public function: np_events_get($config) -> [
 *     'items'      => [ ['title','start','end','location','all_day'], ... ], // soonest first
 *     'fetched_at' => int|null,
 *     'stale_days' => int,
 *     'configured' => bool,
 * ]
 *
 * Only future (and currently-running) events are returned, limited to
 * events_home_count. Fail-safe: errors return last good cache. Never throws.
 */

require_once __DIR__ . '/helpers.php';

/** Unfold ICS lines (RFC 5545: continuation lines start with a space/tab). */
function np_ics_unfold($raw) {
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);
    return preg_replace("/\n[ \t]/", '', $raw);
}

/** Parse an ICS DTSTART/DTEND value into [unix_ts, is_all_day]. */
function np_ics_parse_dt($line, $tzid_default) {
    // Examples:
    //   DTSTART;TZID=America/Toronto:20261118T130000
    //   DTSTART:20261118T180000Z
    //   DTSTART;VALUE=DATE:20261118   (all-day)
    $all_day = stripos($line, 'VALUE=DATE') !== false
               && stripos($line, 'VALUE=DATE-TIME') === false;

    $tzid = $tzid_default;
    if (preg_match('/TZID=([^:;]+)/i', $line, $m)) {
        $tzid = trim($m[1]);
    }

    $colon = strrpos($line, ':');
    if ($colon === false) return [null, $all_day];
    $val = trim(substr($line, $colon + 1));

    try {
        if (preg_match('/^\d{8}$/', $val)) {
            // all-day date
            $dt = DateTime::createFromFormat('Ymd', $val, new DateTimeZone($tzid ?: 'UTC'));
            if ($dt) $dt->setTime(0, 0, 0);
        } elseif (substr($val, -1) === 'Z') {
            $dt = new DateTime($val, new DateTimeZone('UTC'));
        } else {
            $dt = DateTime::createFromFormat('Ymd\THis', $val, new DateTimeZone($tzid ?: 'UTC'));
        }
        if (!$dt) return [null, $all_day];
        return [$dt->getTimestamp(), $all_day];
    } catch (Exception $e) {
        return [null, $all_day];
    }
}

/** Unescape ICS text values (\, \; \n). */
function np_ics_text($line) {
    $colon = strpos($line, ':');
    $val = $colon === false ? '' : substr($line, $colon + 1);
    $val = str_replace(['\\n', '\\N'], ' ', $val);
    $val = str_replace(['\\,', '\\;', '\\\\'], [',', ';', '\\'], $val);
    return trim($val);
}

function np_events_parse_ics($ics, $tz_default) {
    $ics = np_ics_unfold($ics);
    $lines = explode("\n", $ics);

    $events = [];
    $cur = null;
    foreach ($lines as $line) {
        $line = rtrim($line);
        if ($line === 'BEGIN:VEVENT') {
            $cur = ['title' => '', 'start' => null, 'end' => null, 'location' => '', 'all_day' => false];
            continue;
        }
        if ($line === 'END:VEVENT') {
            if ($cur && $cur['start'] !== null && $cur['title'] !== '') {
                $events[] = $cur;
            }
            $cur = null;
            continue;
        }
        if ($cur === null) continue;

        if (stripos($line, 'SUMMARY') === 0) {
            $cur['title'] = np_ics_text($line);
        } elseif (stripos($line, 'DTSTART') === 0) {
            list($ts, $ad) = np_ics_parse_dt($line, $tz_default);
            $cur['start'] = $ts;
            $cur['all_day'] = $ad;
        } elseif (stripos($line, 'DTEND') === 0) {
            list($ts, ) = np_ics_parse_dt($line, $tz_default);
            $cur['end'] = $ts;
        } elseif (stripos($line, 'LOCATION') === 0) {
            $cur['location'] = np_ics_text($line);
        }
    }
    return $events;
}

function np_events_get($config) {
    $url   = trim($config['calendar_ics_url'] ?? '');
    $stale = (int)($config['stale_after_days'] ?? 7);
    $count = max(1, (int)($config['events_home_count'] ?? 3));
    $tz    = $config['timezone'] ?? 'America/Toronto';
    $key   = 'events';

    $cache = np_cache_load($key);

    $finish = function ($items, $fetched_at, $configured) use ($stale, $count) {
        // Filter to upcoming (end in future, or start in future), soonest first.
        $now = time();
        $up = array_filter($items, function ($e) use ($now) {
            $endish = $e['end'] ?? $e['start'];
            return ($endish ?? 0) >= $now - 3600; // small grace so in-progress events show
        });
        usort($up, function ($a, $b) { return ($a['start'] ?? 0) <=> ($b['start'] ?? 0); });
        return [
            'items'      => array_slice(array_values($up), 0, $count),
            'fetched_at' => $fetched_at,
            'stale_days' => $stale,
            'configured' => $configured,
        ];
    };

    if ($url === '') {
        return $finish($cache['items'] ?? [], $cache['fetched_at'] ?? null, false);
    }

    if (np_cache_is_cold($cache, (int)($config['cache_minutes'] ?? 60))) {
        $ics = np_http_get($url);
        if ($ics !== null && stripos($ics, 'BEGIN:VCALENDAR') !== false) {
            $parsed = np_events_parse_ics($ics, $tz);
            // Store ALL parsed events (not just upcoming) so the cache stays
            // useful as time passes; filtering to upcoming happens on read.
            np_cache_save($key, $parsed);
            $cache = ['items' => $parsed, 'fetched_at' => time()];
        }
    }

    return $finish($cache['items'] ?? [], $cache['fetched_at'] ?? null, true);
}
