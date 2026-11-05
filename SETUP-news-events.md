# Setup: connecting News & Events (for Blake)

The code is built and tested. To go live, two URLs go into one file.

## 0. First: a dedicated group Google account

Before making the Sheet or Calendar, create (or decide on) a **Google account
owned by the organization** — e.g. a new Gmail like `niagaraparkinsons@gmail.com`
— and make the Sheet and Calendar in *that* account, not a personal one.

Why: whoever owns these controls the site's news and events. A group account
hands to the next volunteer with just a password, and the published URLs keep
working — nothing to migrate. (Same reasoning as registering the domain in the
org's name.) Keep the password somewhere the group's organizers can reach it.

## 1. News — publish the Google Sheet

1. Make a Google Sheet with one tab for news. Row 1 headers (exactly):
   `title`  `body`  `date`  `link`
2. File → Share → **Publish to web** → select that tab →
   **Comma-separated values (.csv)** → Publish.
3. Copy the URL it gives you.
4. Paste it into `lib/config.php` as `news_csv_url`.

## 2. Events — the calendar's iCal URL

1. In Google Calendar → the group calendar's **Settings**.
2. Make the calendar **public** (Access permissions → "Make available to public").
3. "Integrate calendar" → copy **Public address in iCal format** (ends `.ics`).
4. Paste it into `lib/config.php` as `calendar_ics_url`.
5. For the full embedded calendar on the Events page: open `events.html`, find
   the `data-calendar-id=""` line, and paste the calendar's **Calendar ID**
   (also under "Integrate calendar") between the quotes.

## 3. Upload

Upload everything in this folder to `public_html` on WHC (cPanel File Manager or
FTP), including the `lib/` folder, the (empty) `cache/` folder, `index.php`,
`.htaccess`, `events.html`, `resources.html`, and `assets/`.

**The `cache/` folder must be writable** by PHP (permissions `755` or `775`).
If news/events never appear, that's the first thing to check.

## 4. Confirm

- Visit the site. With the URLs set, news and events appear within a minute of
  the first visit (the first load triggers the fetch).
- Visit `/refresh.php` once in a browser — it should print `ok news=… events=…`.
  (Optional) add it as an hourly cron job in cPanel to pre-warm the cache.

## Not in this version: RSS feeds
There is a reserved, **disabled** `news_rss_feeds` slot in `lib/config.php` for a
later feature (auto-pulling headlines from Parkinson Canada, Davis Phinney, etc.).
It does nothing right now — News comes only from your Google Sheet. The candidate
feed URLs are parked there as comments so they're not lost. Don't uncomment them
expecting news to appear; that feature still has to be built, and it's gated
behind the staleness safeguard on purpose (third-party feeds break silently).

## Keeping external links & contacts current
External resources (the hospital program, national links) drift slowly — a page
moves, a phone number changes. Two things keep this honest:

1. **`linkcheck.php`** — scans the site's own pages for outbound links and checks
   each still responds (reachability only, not content, so no false alarms from
   layout changes). Run it in a browser at `/linkcheck.php`, or add a monthly
   cPanel cron job with MAILTO set so a broken link is emailed to you:
       php /home/USERNAME/public_html/linkcheck.php
   Consider removing or protecting the file after launch — it's an internal tool.

2. **A periodic human review.** A link checker can't see that a phone number or
   program detail changed on someone else's page. Put a recurring reminder in a
   calendar (every 3–6 months) to spend five minutes re-reading the Resources
   page against the sources. That's the only reliable way to catch content drift;
   these details change rarely, but they do.

**Who owns these reviews:** ______________________

## Notes
- PHP is on by default on WHC shared hosting; nothing to enable.
- `lib/` and `cache/` are blocked from the web by `.htaccess` (and their own
  deny files) — visitors can't browse them.
- Everything fails safe: a broken URL, a down Google, or a bad row never breaks
  the page — it keeps showing the last good data and ages the "updated" note.

## How it works (one paragraph)
`index.php` includes `lib/news.php` and `lib/events.php`. Each fetches its
source (Sheet CSV / Calendar iCal) at most once per `cache_minutes`, stores the
result in `cache/*.json` with a timestamp, and renders from that cache. The
timestamp drives the "Updated N days ago" note and the amber stale warning past
`stale_after_days`. All of it is wrapped so no failure can reach a visitor.
