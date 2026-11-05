<?php
/**
 * Link checker — a reliability aid, not part of the public site.
 *
 * It scans the site's own pages for outbound links (http/https to other
 * sites) and checks each one still responds. It reports only REACHABILITY
 * (does the link still work), never content — so it won't cry wolf over a
 * hospital site reshuffling its layout. It catches the common rot: a page
 * that moved or died (404/gone).
 *
 * WHAT IT DOES NOT DO: it can't tell you a phone number or program detail
 * changed on someone else's page. For that, see the periodic human review
 * reminder in SETUP-news-events.md — slowly-drifting institutional info
 * needs eyes, not a scraper.
 *
 * HOW TO USE
 *   - In a browser: visit /linkcheck.php (consider removing it after launch,
 *     or protect it, since it's an internal tool — it exposes nothing
 *     sensitive, it just isn't for visitors).
 *   - On a schedule: add a cPanel cron job, e.g. monthly:
 *       php /home/USERNAME/public_html/linkcheck.php
 *     Pair it with MAILTO in cPanel's cron settings to get the output emailed,
 *     so a broken link lands in your inbox without you remembering to look.
 *
 * Safe to run anytime. Never changes the site.
 */

@ini_set('display_errors', '0');
$cli = (php_sapi_name() === 'cli');
if (!$cli) header('Content-Type: text/plain; charset=utf-8');

$dir = __DIR__;
$own_host = 'parkinsonsniagara.ca';
$skip_hosts = ['fonts.googleapis.com', 'fonts.gstatic.com'];

/* 1. Collect outbound links from the site's pages. */
$links = [];
foreach (glob($dir . '/*.{html,php}', GLOB_BRACE) as $file) {
    $html = @file_get_contents($file);
    if ($html === false) continue;
    if (preg_match_all('/href="(https?:\/\/[^"]+)"/i', $html, $m)) {
        foreach ($m[1] as $url) {
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            if (stripos($host, $own_host) !== false) continue;   // skip self
            if (in_array(strtolower($host), $skip_hosts, true)) continue;
            $links[$url] = true;
        }
    }
}
$links = array_keys($links);
sort($links);

if (empty($links)) {
    echo "No external links found to check.\n";
    exit;
}

/* 2. Check each link's reachability (HEAD, fall back to GET). */
function np_check($url, $timeout = 10) {
    if (!function_exists('curl_init')) {
        // Fallback: a GET with a short timeout.
        $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => $timeout, 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $mm)) {
            return (int)$mm[1];
        }
        return $body === false ? 0 : 200;
    }
    foreach (['HEAD', 'GET'] as $method) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => $method === 'HEAD',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_USERAGENT      => 'ParkinsonsNiagara-LinkCheck/1.0',
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 400) return $code;   // good
        if ($method === 'GET') return $code;             // report whatever GET got
        // else loop to GET (some servers reject HEAD)
    }
    return 0;
}

/* 3. Report. */
$problems = 0;
echo "Link check — " . date('c') . "\n";
echo str_repeat('-', 48) . "\n";
foreach ($links as $url) {
    $code = np_check($url);
    $ok = ($code >= 200 && $code < 400);
    if (!$ok) $problems++;
    printf("%-6s %s\n", $ok ? "ok" : ($code ?: "DOWN"), $url);
}
echo str_repeat('-', 48) . "\n";
echo $problems === 0
    ? "All " . count($links) . " links OK.\n"
    : "$problems of " . count($links) . " links need attention (see above).\n";
