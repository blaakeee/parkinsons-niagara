<?php
/**
 * Home page. News (Google Sheet) and Events (Google Calendar) render here,
 * server-side, so they appear in the HTML for every visitor regardless of
 * browser or settings. Every dynamic piece is wrapped so a failure degrades
 * to a quiet fallback and never breaks the page.
 */
// Never show PHP notices/warnings to a visitor on this page, whatever the
// server's php.ini says. Errors are logged, not printed.
@ini_set('display_errors', '0');

$__cfg = [];
$__news = ['items'=>[], 'fetched_at'=>null, 'stale_days'=>7, 'configured'=>false];
$__events = ['items'=>[], 'fetched_at'=>null, 'stale_days'=>7, 'configured'=>false];
try {
    $__cfg_file = __DIR__ . '/lib/config.php';
    if (is_file($__cfg_file)) {
        $__cfg = @require $__cfg_file;
        if (!is_array($__cfg)) $__cfg = [];
    }
    if (is_file(__DIR__ . '/lib/news.php'))   require_once __DIR__ . '/lib/news.php';
    if (is_file(__DIR__ . '/lib/events.php')) require_once __DIR__ . '/lib/events.php';
    @date_default_timezone_set($__cfg['timezone'] ?? 'America/Toronto');
    if (function_exists('np_news_get'))   $__news   = np_news_get($__cfg);
    if (function_exists('np_events_get')) $__events = np_events_get($__cfg);
} catch (Throwable $e) {
    // Swallow everything: the page renders with empty/fallback feed blocks.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Niagara Parkinson's Support Group</title>
<meta name="description" content="A home for people in the Niagara region living with Parkinson's — support, movement, and trusted resources across St. Catharines, Niagara Falls, and Welland.">
<link rel="canonical" href="https://parkinsonsniagara.ca/">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Niagara Parkinson's Support Group">
<meta property="og:title" content="Niagara Parkinson's Support Group">
<meta property="og:description" content="A local support group for people living with Parkinson's in Niagara, their care partners, and families.">
<meta property="og:url" content="https://parkinsonsniagara.ca/">
<meta name="twitter:card" content="summary">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>

<!-- Contact always visible without scrolling -->
<div class="contactbar">
  Questions? Call <a href="tel:9053217006">905-321-7006</a> ·
  <a href="mailto:mtoffolo@whiteoaksresort.com">mtoffolo@whiteoaksresort.com</a>
</div>

<header class="main"><div class="wrap">
  <div class="nav" id="nav">
    <a class="brand" href="/">
      <svg class="mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="23" fill="#0d6e7a"/>
        <path d="M14 30c4-1 6-5 6-10 0-4 2-7 5-7s5 3 5 7c0 6 3 9 7 9" stroke="#e8913a" stroke-width="3" stroke-linecap="round" fill="none"/>
        <circle cx="24" cy="13" r="2.6" fill="#fff"/>
      </svg>
      <span class="brand-text"><b>Niagara Parkinson's</b><span>Support Group</span></span>
    </a>
    <nav class="navlinks" aria-label="Main">
      <a href="/">Home</a>
      <a href="/#movement">Movement</a>
      <a href="/events.html">Events</a>
      <a href="/#news">News</a>
      <a href="/resources.html">Resources</a>
    </nav>
    <div class="nav-cta">
      <a href="/resources.html" class="btn btn-outline">Resources</a>
      <button class="menu-btn" aria-label="Open menu" aria-expanded="false"
        onclick="var n=document.getElementById('nav');var o=n.classList.toggle('open');this.setAttribute('aria-expanded',o)">
        <svg viewBox="0 0 24 24" fill="none" stroke="#0d6e7a" stroke-width="2" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</div></header>
<main id="main">
<section class="hero" id="top">
  <div class="wrap">
    <div>
      <span class="eyebrow">No one should face Parkinson's alone</span>
      <h1>Support and community — right here in Niagara.</h1>
      <p class="lead">We're a local support group for people living with Parkinson's in Niagara, their care partners, and families. Whatever stage you're at, you're welcome here.</p>
      <div class="hero-cta">
        <a href="#connect" class="btn btn-primary">Get in touch</a>
        <a href="/resources.html" class="btn btn-ghost">Helpful resources</a>
      </div>
    </div>
    <div class="hero-card" aria-label="Get in touch">
      <h3>Reach out</h3>
      <p>Questions about Parkinson's support in Niagara? We're glad to hear from you.</p>
      <div class="row"><span class="dot"></span> Call <a href="tel:9053217006" style="color:#fff;text-decoration:underline">905-321-7006</a></div>
      <div class="row"><span class="dot"></span> Email <a href="mailto:mtoffolo@whiteoaksresort.com" style="color:#fff;text-decoration:underline">mtoffolo@whiteoaksresort.com</a></div>
    </div>
  </div>
  <div class="river-curve" aria-hidden="true">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" style="width:100%;height:48px"><path d="M0 60V20c240 40 480 40 720 20S1200-10 1440 20v40z" fill="#ffffff"/></svg>
  </div>
</section>

<!-- WHO WE ARE -->
<section class="section" id="about">
  <div class="wrap who">
    <div class="copy">
      <span class="eyebrow">Who we are</span>
      <h2>A local Parkinson's support group in Niagara.</h2>
      <p>We're a community-led group for people in the Niagara region living with Parkinson's, along with their care partners and families. Our focus is simple: connection, mutual support, and pointing people toward trustworthy information. We're growing, and we'd love to hear from you.</p>
      <a href="#connect" class="btn btn-outline" style="margin-top:.6rem">Get in touch</a>
    </div>
    <div class="copy">
      <span class="eyebrow">A note on the region</span>
      <h2>You're not on your own.</h2>
      <p>Niagara is underserved for Parkinson's specialists, and many people travel to Hamilton or London for neurology care. Part of why this group exists is to help people in our community feel less alone in navigating that — and to share what we learn along the way.</p>
      <p>Locally, <a href="https://www.hoteldieushaver.org/site/specialty-programs/parkinsons-rehab-hds" target="_blank" rel="noopener">Hotel Dieu Shaver's Parkinson's Rehab Program</a> offers outpatient rehabilitation in St. Catharines (a physician's referral is required). See our <a href="/resources.html">resources</a> for more.</p>
    </div>
  </div>
</section>

<!-- NEWS & EVENTS (dynamic) -->
<section class="section" id="news" style="background:var(--mist)">
  <div class="wrap">
    <div class="home-feed">

      <!-- News column -->
      <div>
        <div class="feed-head">
          <h2>Latest news</h2>
          <?php
            if (!empty($__news['items'])) {
                echo np_freshness_note($__news['fetched_at'], $__news['stale_days']);
            }
          ?>
        </div>
        <?php if (!empty($__news['items'])): ?>
          <div class="news-list">
            <?php foreach (array_slice($__news['items'], 0, 4) as $it): ?>
              <article class="news-item">
                <span class="src">
                  <?php
                    $d = $it['date'] ? strtotime($it['date']) : 0;
                    echo $d ? np_e(date('M j, Y', $d)) : 'News';
                  ?>
                </span>
                <h3>
                  <?php if (!empty($it['link'])): ?>
                    <a href="<?php echo np_e($it['link']); ?>" target="_blank" rel="noopener"><?php echo np_e($it['title']); ?></a>
                  <?php else: ?>
                    <?php echo np_e($it['title']); ?>
                  <?php endif; ?>
                </h3>
                <?php if (!empty($it['body'])): ?>
                  <p class="body"><?php echo np_e($it['body']); ?></p>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="feed-empty">News updates will appear here soon. In the meantime, feel free to <a href="#connect">get in touch</a>.</p>
        <?php endif; ?>
      </div>

      <!-- Events column -->
      <div>
        <div class="feed-head">
          <h2>Upcoming events</h2>
          <?php
            if (!empty($__events['items'])) {
                echo np_freshness_note($__events['fetched_at'], $__events['stale_days']);
            }
          ?>
        </div>
        <?php if (!empty($__events['items'])): ?>
          <ul class="ev-list">
            <?php foreach ($__events['items'] as $ev): ?>
              <?php $ts = $ev['start'] ?? 0; ?>
              <li class="ev-item">
                <div class="ev-date">
                  <div class="d"><?php echo $ts ? np_e(date('j', $ts)) : '·'; ?></div>
                  <div class="mo"><?php echo $ts ? np_e(date('M', $ts)) : ''; ?></div>
                </div>
                <div class="ev-body">
                  <h3><?php echo np_e($ev['title']); ?></h3>
                  <p>
                    <?php
                      if ($ts) {
                          echo np_e(!empty($ev['all_day']) ? 'All day' : date('g:i a', $ts));
                      }
                      if (!empty($ev['location'])) {
                          echo ' · ' . np_e($ev['location']);
                      }
                    ?>
                  </p>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="feed-foot"><a href="/events.html" class="btn btn-outline">See full calendar</a></div>
        <?php else: ?>
          <p class="feed-empty">Our events calendar is being set up. <a href="/events.html">View the calendar page</a> or <a href="#connect">ask us what's coming up</a>.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<!-- MOVEMENT -->
<section class="section tight" id="movement" style="background:var(--mist)">
  <div class="wrap">
    <span class="eyebrow">Movement &amp; exercise</span>
    <h2>Rock Steady Boxing Niagara</h2>
    <div class="who" style="margin-top:2rem">
      <div class="copy">
        <p>Exercise has some of the strongest evidence for helping people manage Parkinson's symptoms and quality of life. Rock Steady Boxing Niagara is a non-contact boxing program built specifically for people with Parkinson's, running year-round at The Club, White Oaks Resort.</p>
        <p>Certified trainers lead sessions three times a week — every Monday, Wednesday and Friday — focused on agility, balance, strength, and confidence. It's an affiliated program with its own home, and a great place to start moving.</p>
        <div class="give-actions" style="margin-top:1.2rem">
          <a href="https://rocksteadyboxingniagara.ca" target="_blank" rel="noopener" class="btn btn-primary">Visit Rock Steady Boxing Niagara</a>
          <a href="mailto:mtoffolo@whiteoaksresort.com" class="btn btn-outline">Ask us about it</a>
        </div>
      </div>
      <div class="give-visual">
        <h3>At a glance</h3>
        <ul>
          <li>Non-contact boxing designed for people with Parkinson's</li>
          <li>Monday, Wednesday &amp; Friday, year-round</li>
          <li>Certified trainers · The Club, White Oaks Resort</li>
          <li>Free introductory sessions to see if it's a fit</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- GET INVOLVED -->
<section class="section" id="connect" style="background:var(--mist)">
  <div class="wrap give">
    <div class="copy">
      <span class="eyebrow">Get involved</span>
      <h2>Come say hello.</h2>
      <p>Whether you're living with Parkinson's, caring for someone who is, or want to help this group grow, the best first step is simply to reach out.</p>
      <div class="give-actions">
        <a href="mailto:mtoffolo@whiteoaksresort.com" class="btn btn-primary">Email us</a>
        <a href="tel:9053217006" class="btn btn-outline">Call 905-321-7006</a>
      </div>
    </div>
    <div class="give-visual">
      <h3>We'd love to hear from you if…</h3>
      <ul>
        <li>You or someone you love has Parkinson's and you'd like to connect</li>
        <li>You're a care partner looking for community</li>
        <li>You want to help build Parkinson's support in Niagara</li>
      </ul>
    </div>
  </div>
</section>
</main>

<footer id="contact">
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <b>Niagara Parkinson's Support Group</b>
        <p>A home for people impacted by Parkinson's across the Niagara region.</p>
        <p>Email: <a href="mailto:mtoffolo@whiteoaksresort.com">mtoffolo@whiteoaksresort.com</a><br>
        Phone: <a href="tel:9053217006">905-321-7006</a></p>
      </div>
      <div>
        <h4>Explore</h4>
        <div class="foot-links">
          <a href="/">Home</a>
            <a href="/#movement">Movement</a>
            <a href="/events.html">Events</a>
            <a href="/#news">News</a>
            <a href="/resources.html">Resources</a>
        </div>
      </div>
      <div>
        <h4>Get involved</h4>
        <div class="foot-links">
          <a href="/#connect">Get in touch</a>
          <a href="/#connect">Volunteer</a>
          <a href="/resources.html">Resources</a>
        </div>
      </div>
      <div>
        <h4>Serving</h4>
        <div class="foot-links">
          <span>St. Catharines</span><span>Niagara Falls</span>
          <span>Welland</span><span>Fort Erie &amp; region</span>
        </div>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© 2026 Niagara Parkinson's Support Group · Charitable status in progress</span>
      <span>Built to be portable — see MAINTAINERS.md</span>
    </div>
  </div>
</footer>

<script src="/assets/js/nav.js" defer></script>

</body>
</html>
