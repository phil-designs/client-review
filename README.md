# Client Review

Tags: client review, annotations, responsive preview, feedback, site review  
Requires at least: 6.0  
Requires PHP: 8.0  
Tested up to: 6.9.1  
License: GPL2

## Description

Send clients a private, single-use invite link to preview your WordPress site before launch. Clients register with a name, email, and password, then land in a full-screen preview shell where they can switch between Desktop, Tablet, and Mobile viewports and drop visual pin annotations anywhere on the page — similar to commenting in a PDF. All comments are recorded per page and per device size. When the client finishes their session they submit their review, which emails a formatted summary to the site admin. The admin can then view all feedback in a dedicated dashboard, mark items as Done or Needs Clarification, and leave response notes for the client.

## Features

- Single-use, expiring invite links — clients register once and are automatically assigned the Client Reviewer role
- Full-screen preview shell with Desktop (1440px), Tablet (768px), and Mobile (390px) viewport switching
- Click-to-pin annotations — clients click anywhere on a page to drop a numbered pin and write a comment
- Comments are stored per page URL and per device size
- Multiple reviewers supported — each user can only edit or delete their own comments; all users can read all comments on the page
- Admin response notes visible to the client on their next login
- "Finish Review" button sends a formatted HTML email summary to the site admin
- Admin dashboard with per-reviewer comment lists, status controls (Open / Done / Needs Clarification), and admin note fields
- Client Reviewer role is blocked from wp-admin and has the admin bar hidden
- No external dependencies — no third-party services or APIs required

## Tested On

- Firefox
- Safari
- Chrome
- MS Edge

## Website

https://phildesigns.com

## Prerequisites

- WordPress 6.0 or higher
- PHP 8.0 or higher
- A working `wp_mail()` setup on your server for email delivery (required for review summary emails)

---

## Installation

1. Upload the `client-review` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. After activation the plugin automatically:
   - Creates two database tables (`wp_cr_invites` and `wp_cr_annotations`)
   - Registers the **Client Reviewer** user role
   - Registers the `/client-review/` URL for the preview shell
4. Navigate to **Client Review → Invite Links** in the WordPress admin to get started

---

## How To Use

### Step 1 — Generate an invite link

1. Go to **wp-admin → Client Review → Invite Links**
2. Enter an optional label (e.g. the client's name or project)
3. Click **Generate Link**
4. Copy the generated URL and send it to your client via email, Slack, or any other channel

> Links are single-use and expire after 30 days. Once a client registers, the link is marked as used and cannot be reused.

---

### Step 2 — Client registers and enters the preview

1. The client opens the invite link in their browser
2. They fill in their **name**, **email address**, and a **password** (minimum 8 characters) and click **Create account**
3. They are automatically logged in and redirected to the preview shell at `/client-review/`
4. On future visits, the client logs in via the standard WordPress login page — they will always be redirected to the preview shell, never to wp-admin

---

### Step 3 — Client reviews the site

Inside the preview shell the client will see:

- **A toolbar at the top** with:
  - The site name on the left
  - Device size buttons in the centre: **Desktop**, **Tablet**, **Mobile**
  - **Add Comment**, **Comments**, **Finish Review**, and **Logout** buttons on the right
- **The site rendered in an iframe** on the left — they can click links and navigate the site normally
- **A collapsible comments sidebar** on the right showing all pins for the current page and device

#### Leaving a comment / pin

1. Click **Add Comment** in the toolbar — the button highlights and the cursor changes to a crosshair
2. Click anywhere on the page in the iframe — a numbered pin drops at that exact location and a text box appears nearby
3. Type the feedback and press **Save Comment** (or `Ctrl/Cmd + Enter`)
4. The pin appears on the page and the comment appears in the sidebar
5. Click **Add Comment** again (or press `Esc`) to exit annotation mode and browse the site normally

#### Navigating between pages

- Click any link inside the iframe to navigate — the overlay and sidebar automatically update to show comments for the new page and current device size

#### Switching device sizes

- Click **Desktop**, **Tablet**, or **Mobile** in the toolbar — the iframe resizes and the sidebar reloads the comments for that size

#### Editing or deleting a comment

- Only the author of a comment can edit or delete it
- In the sidebar, hover over a comment to see the **Edit** and **Delete** buttons (only shown on your own comments)
- Click **Edit**, update the text, and press **Save** (or `Ctrl/Cmd + Enter`) to confirm
- Other reviewers' comments are visible but read-only

---

### Step 4 — Submit the review

1. When the client is done, they click **Finish Review** in the top-right of the toolbar
2. A confirmation modal appears — click **Yes, Submit**
3. An HTML email summarising all comments (grouped by page and device size) is sent to the WordPress admin email address
4. The client can continue adding comments after submitting — each submission sends a fresh snapshot email

---

### Step 5 — Admin reviews the feedback

1. Go to **wp-admin → Client Review → Reviews**
2. The left panel lists all clients who have left comments, with open comment counts and the date of last activity
3. Click a client name to load their full comment list on the right, grouped by page and device
4. For each comment you can:
   - Change the **status** using the dropdown: **Open**, **Done**, or **Needs Clarification**
   - Add an **admin note** (visible to the client the next time they log in) and click **Save note**
5. You can also click **Open Preview Shell** to view the live site in the same preview the client used

---

## Admin Menu Reference

| Page | Location | Purpose |
|------|----------|---------|
| Invite Links | Client Review → Invite Links | Generate and manage invite URLs |
| Reviews | Client Review → Reviews | View client feedback, update statuses, add notes |

---

## Notes

- **Email delivery** — review summary emails use WordPress's built-in `wp_mail()`. If emails are not arriving, check that your hosting environment has a working mail setup or install an SMTP plugin such as WP Mail SMTP.
- **Permalink structure** — the plugin requires a non-plain permalink structure (e.g. Post name). Go to **Settings → Permalinks** and save after activating the plugin if the `/client-review/` URL returns a 404.
- **Multiple clients** — you can generate as many invite links as you like. Each client has their own account and their annotations are tracked separately in the admin dashboard.
- **Role cleanup** — deactivating the plugin does not remove the Client Reviewer role or existing annotations. To fully clean up, delete the plugin.

---

## Changelog

### Version 1.0.0
- Initial release
- Single-use invite link system with expiry
- Client Reviewer user role with wp-admin access blocked and admin bar hidden
- Full-screen preview shell with Desktop / Tablet / Mobile switcher
- Click-to-pin visual annotation system with per-page and per-device storage
- REST API for annotation CRUD with ownership enforcement
- Collapsible sidebar with comment list, status badges, and admin response display
- "Finish Review" modal with HTML email summary to admin
- Admin dashboard: Invite Links page and Reviews page with status and note controls
