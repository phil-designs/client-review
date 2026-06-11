=== PhilDesigns Client Review ===
Contributors: phildesigns
Tags: client review, annotations, feedback, preview, site review
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Invite clients to preview your site privately, drop visual pin annotations, and submit a formatted review summary by email.

== Description ==

Send clients a private, single-use invite link to preview your WordPress site before launch. Clients register and land in a full-screen preview shell where they can switch between Desktop, Tablet, and Mobile viewports and drop numbered pin annotations anywhere on the page — similar to commenting in a PDF.

All comments are stored per page and per device size. When the client finishes, they submit their review, which emails a formatted HTML summary to the site admin. The admin can then view all feedback in a dedicated dashboard, mark items as Done or Needs Clarification, and leave response notes.

= Features =

* Single-use, expiring invite links — clients register once and are assigned the Client Reviewer role automatically
* Full-screen preview shell with Desktop (1440px), Tablet (768px), and Mobile (390px) viewport switching
* Click-to-pin annotations — clients click anywhere on the page to drop a numbered pin and write a comment
* Comments stored per page URL and per device size
* Multiple reviewers supported — each user can only edit or delete their own comments
* Admin response notes visible to the client on their next login
* "Finish Review" button sends a formatted HTML email summary to the site admin
* Admin dashboard with per-reviewer comment lists, status controls (Open / Done / Needs Clarification), and admin note fields
* Client Reviewer role is blocked from wp-admin and has the admin bar hidden
* Customisable typography, accent colour, and button styles via the Settings page
* No external dependencies — no third-party services or APIs required

== Installation ==

1. Upload the `client-review` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **Client Review → Invite Links** to generate your first invite link

= Requirements =

* WordPress 6.7 or higher
* PHP 8.0 or higher
* A working `wp_mail()` setup on your server for email delivery

= After Activation =

The plugin automatically creates two database tables (`wp_cr_invites` and `wp_cr_annotations`), registers the **Client Reviewer** user role, and registers the `/client-review/` URL for the preview shell.

If the `/client-review/` URL returns a 404, go to **Settings → Permalinks** and click Save to flush rewrite rules.

== Frequently Asked Questions ==

= Do clients need an existing WordPress account? =

No. Clients register via their invite link using their name, email, and a password. The plugin creates their account and logs them in automatically.

= Can I invite multiple clients? =

Yes. Each invite link is single-use and creates a separate account. You can generate as many links as you need from **Client Review → Invite Links**.

= Why are review summary emails not arriving? =

The plugin uses WordPress's built-in `wp_mail()`. If emails are not being delivered, check that your hosting environment has a working mail setup or install an SMTP plugin such as WP Mail SMTP.

= Does deactivating the plugin remove client data? =

No. Deactivating preserves the Client Reviewer role and all annotation data. To fully remove all data, delete the plugin.

= Can clients browse the whole site from the preview shell? =

Yes. The site is rendered in an iframe and clients can navigate normally. Clicking any link updates the URL in the toolbar and reloads comments for the new page.

== Screenshots ==

1. Preview shell with pin annotations and the comment sidebar open
2. Generating a new invite link in the admin dashboard
3. Invite links table showing status (Active / Used / Expired)
4. Reviews dashboard — reviewer list on the left, annotations on the right
5. Annotation card with status dropdown and admin note field
6. Registration form clients see when opening their invite link
7. Login page for returning clients
8. Settings page — typography, accent colour, and button customisation

== Changelog ==

= 1.0.0 =
* Initial release
* Single-use invite link system with 30-day expiry
* Client Reviewer user role with wp-admin access blocked and admin bar hidden
* Full-screen preview shell with Desktop / Tablet / Mobile switcher
* Click-to-pin visual annotation system with per-page and per-device storage
* REST API for annotation CRUD with ownership enforcement
* Collapsible sidebar with comment list, status badges, and admin response display
* "Finish Review" modal with HTML email summary to admin
* Admin dashboard: Invite Links page and Reviews page with status and note controls
* Settings page for typography, accent colour, and button styling customisation

== Upgrade Notice ==

= 1.0.0 =
Initial release.
