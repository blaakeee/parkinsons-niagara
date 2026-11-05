<?php
/**
 * Niagara Parkinson's Support Group — site configuration
 * ------------------------------------------------------
 * This is the ONE file to edit to connect News and Events.
 * You do not need to touch any other PHP.
 *
 * WHICH GOOGLE ACCOUNT?
 * The Sheet and Calendar below should live in a dedicated GROUP Google
 * account (e.g. a new gmail owned by the organization), NOT a personal
 * one. Whoever owns them controls the site's news and events, so keeping
 * them in a group account means the next volunteer inherits everything by
 * just getting the account password — no URLs to change, nothing to migrate.
 * This mirrors the domain, which is registered in the org's name, not an
 * individual's.
 */

return [

    /**
     * NEWS — Google Sheet (published to the web as CSV)
     * -------------------------------------------------
     * In your Google Sheet:  File -> Share -> Publish to web
     *   -> pick the news tab -> "Comma-separated values (.csv)"
     *   -> Publish -> copy the link it gives you, paste it below.
     *
     * The sheet's first row must be these column headers (any order):
     *   title | body | date | link
     * One news item per row. Leave this blank to hide the news section.
     */
    'news_csv_url' => '',

    /**
     * NEWS — RSS feeds (NOT YET ENABLED)
     * ----------------------------------
     * This slot is reserved for a later feature: automatically pulling
     * headlines from outside organizations into the News section, so no one
     * has to copy them by hand. It is DISABLED right now — the code does not
     * read this list yet, and nothing here affects the live site. It's here
     * so the structure is ready, and so the candidate feeds aren't lost.
     *
     * When this feature is built, each entry will be ['name' => ..., 'url' => ...].
     * Candidate feeds to confirm before switching it on (a feed can move or die
     * without notice, which is exactly why this is gated behind the staleness
     * safeguard — see 'stale_after_days' below):
     *   - Parkinson Canada        https://www.parkinson.ca/feed/            (confirmed working earlier)
     *   - Davis Phinney Foundation https://davisphinneyfoundation.org/feed/ (confirmed working earlier)
     *   - Michael J. Fox Foundation — no reliable public feed found; keep as a plain link, not a feed
     *   - PubMed (Parkinson research) — needs a real saved-search RSS URL generated on PubMed
     *
     * To keep it OFF, leave this an empty array.
     */
    'news_rss_feeds' => [
        // ['name' => 'Parkinson Canada',          'url' => 'https://www.parkinson.ca/feed/'],
        // ['name' => 'Davis Phinney Foundation',  'url' => 'https://davisphinneyfoundation.org/feed/'],
    ],

    /**
     * EVENTS — Google Calendar (public address in iCal format)
     * --------------------------------------------------------
     * In Google Calendar:  Settings -> (your group calendar)
     *   -> "Integrate calendar" -> copy "Public address in iCal format"
     *   (ends in /public/basic.ics) -> paste it below.
     *
     * The same calendar is embedded on the Events page. Leave this
     * blank to hide the "upcoming events" list on the home page.
     */
    'calendar_ics_url' => '',

    /**
     * How many upcoming events to show in the short list on the home page.
     */
    'events_home_count' => 3,

    /**
     * STALENESS — when to warn that content hasn't refreshed.
     * The news/events blocks keep showing the last good data no matter
     * what; this only controls when the quiet "last updated" note turns
     * into an amber "hasn't refreshed recently" warning.
     * Value is in DAYS. 7 is a sensible default for this kind of source.
     */
    'stale_after_days' => 7,

    /**
     * How long (in minutes) to keep cached data before trying to refresh.
     * The page always serves from cache for speed; a background-ish refresh
     * happens when the cache is older than this AND someone loads the page,
     * or when the cron job runs. 60 minutes is plenty for this site.
     */
    'cache_minutes' => 60,

    /**
     * Timezone for displaying dates/times.
     */
    'timezone' => 'America/Toronto',
];
