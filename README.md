<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->
<a id="readme-top"></a>

<!-- PROJECT SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![GPL-2.0 License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/phil-designs/client-review">
    <img src="https://phildesigns.com/wp-content/uploads/2025/12/phildesigns-logo.svg" alt="Logo" height="80">
  </a>

  <h3 align="center">Client Review</h3>

  <p align="center">
    Invite clients to preview your WordPress site privately, drop visual pin annotations, and submit a formatted review summary by email.

Tags: client review, annotations, feedback, preview, site review 
Requires at least: 6.7 
Tested up to: 7.0 
Requires PHP: 8.0 
License: GPL-2.0-or-later
    <br />
    <a href="https://github.com/phil-designs/client-review"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/phil-designs/client-review">View Demo</a>
    &middot;
    <a href="https://phildesigns.com/plugin-feedback/">Report Bug</a>
    &middot;
    <a href="https://phildesigns.com/plugin-feedback/">Request Feature</a>
  </p>
</div>

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#changelog">Changelog</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->
## About The Project

[![Product Name Screen Shot][product-screenshot]](https://github.com/phil-designs/client-review)

Send clients a private, single-use invite link to preview your WordPress site before launch. Clients register and land in a full-screen preview shell where they can switch between Desktop, Tablet, and Mobile viewports and drop numbered pin annotations anywhere on the page — similar to commenting in a PDF.

All comments are stored per page and per device size. When the client finishes, they submit their review, which emails a formatted HTML summary to the site admin. The admin can then view all feedback in a dedicated dashboard, mark items as Done or Needs Clarification, and leave response notes.

**Features**
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



<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With

* [![jQuery][JQuery-badge]][JQuery-url]
* [![WordPress][WordPress-badge]][WordPress-url]
* [![PHP][PHP-badge]][PHP-url]


<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

 

### Prerequisites

* WordPress 6.7 or higher
* PHP 8.0 or higher
* A working wp_mail() setup on your server for email delivery

### Installation

1. Upload the `client-review` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **Client Review → Invite Links** to generate your first invite link

After activation the plugin automatically:
- Creates two database tables (`wp_cr_invites` and `wp_cr_annotations`)
- Registers the **Client Reviewer** user role
- Registers the `/client-review/` URL for the preview shell

> If the `/client-review/` URL returns a 404, go to **Settings → Permalinks** and click Save to flush rewrite rules.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- USAGE EXAMPLES -->
## Usage

### Step 1 — Generate an invite link

1. Go to **wp-admin → Client Review → Invite Links**
2. Enter an optional label (e.g. the client's name or project)
3. Click **Generate Link**
4. Copy the URL and send it to your client

> Links are single-use and expire after 30 days. Once a client registers, the link is marked as used.

---

### Step 2 — Client registers and enters the preview

1. The client opens the invite link in their browser
2. They fill in their **name**, **email address**, and a **password** (minimum 8 characters) and click **Create account**
3. They are automatically logged in and redirected to the preview shell at `/client-review/`

---

### Step 3 — Client reviews the site

Inside the preview shell the client will see:

- **A toolbar at the top** with device size buttons, Add Comment, Comments, Finish Review, and Logout
- **The site rendered in an iframe** — they can click links and navigate normally
- **A collapsible comments sidebar** showing all pins for the current page and device

**Leaving a comment:**
1. Click **Add Comment** in the toolbar
2. Click anywhere on the page in the iframe — a numbered pin drops at that location
3. Type the feedback and press **Save Comment** (or `Ctrl/Cmd + Enter`)

---

### Step 4 — Submit the review

1. Click **Finish Review** in the top-right of the toolbar
2. Confirm in the modal — click **Yes, Submit**
3. An HTML email summarising all comments is sent to the WordPress admin email address

---

### Step 5 — Admin reviews the feedback

1. Go to **wp-admin → Client Review → Reviews**
2. Click a client name to load their full comment list, grouped by page and device
3. For each comment you can change the **status** (Open / Done / Needs Clarification) and add an **admin note**

---

<!-- ADMIN MENU REFERENCE -->
## Admin Menu Reference

| Page | Location | Purpose |
|------|----------|---------|
| Invite Links | Client Review → Invite Links | Generate and manage invite URLs |
| Reviews | Client Review → Reviews | View client feedback, update statuses, add notes |
| Settings | Client Review → Settings | Typography, colours, and button customisation |


<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- NOTES -->

## Notes

- **Email delivery** — uses WordPress's built-in `wp_mail()`. If emails are not arriving, check your hosting mail setup or install an SMTP plugin such as WP Mail SMTP.
- **Multiple clients** — generate as many invite links as you like; each client has their own account.
- **Role cleanup** — deactivating the plugin does not remove the Client Reviewer role or existing annotations. To fully clean up, delete the plugin.


<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTRIBUTING -->
## Changelog

1.0.0
* Initial release


<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- LICENSE -->
## License

Distributed under the GPL-2.0 License. See `LICENSE.txt` for more information.


<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTACT -->
## Contact

Phillip De Vita - [LinkedIn](https://linkedin.com/in/phildesigns) - phil@phildesigns.com

Project Link: [https://github.com/phil-designs/client-review](https://github.com/phil-designs/client-review)


<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- MARKDOWN LINKS & IMAGES -->
[contributors-shield]: https://img.shields.io/github/contributors/phil-designs/client-review.svg?style=for-the-badge
[contributors-url]: https://github.com/phil-designs/client-review/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/phil-designs/client-review.svg?style=for-the-badge
[forks-url]: https://github.com/phil-designs/client-review/network/members
[stars-shield]: https://img.shields.io/github/stars/phil-designs/client-review.svg?style=for-the-badge
[stars-url]: https://github.com/phil-designs/client-review/stargazers
[issues-shield]: https://img.shields.io/github/issues/phil-designs/client-review.svg?style=for-the-badge
[issues-url]: https://github.com/phil-designs/client-review/issues
[license-shield]: https://img.shields.io/github/license/phil-designs/client-review.svg?style=for-the-badge
[license-url]: https://github.com/phil-designs/client-review/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/phildesigns/
[product-screenshot]: https://phildesigns.com/wp-content/uploads/2026/06/review-page-client-system-feedback-feature-various-annotation.png
[JQuery-badge]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com
[WordPress-badge]: https://img.shields.io/badge/WordPress-21759B?style=for-the-badge&logo=wordpress&logoColor=white
[WordPress-url]: https://wordpress.org
[PHP-badge]: https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white
[PHP-url]: https://www.php.net/
