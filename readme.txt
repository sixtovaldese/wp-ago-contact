=== aGo Contact ===
Contributors: agolab
Donate link: https://paypal.me/sixtovaldes
Tags: contact form, form, spam, submissions, email
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple contact form with spam protection, email notifications and submission management from the dashboard.

== Description ==

aGo Contact gives you a lightweight contact form shortcode with built-in spam protection, email notifications and a submissions inbox in the admin.

**Features**

* Shortcode `[agocontact]` or Gutenberg block to drop the form on any page or post.
* Configurable recipient email, subject and success message.
* Honeypot, math captcha and optional Cloudflare Turnstile, plus rate limit per IP to block bots.
* Email notification to the site admin on every submission, with optional auto-reply.
* Submissions inbox: read, mark as replied or spam, delete and export to CSV.
* GDPR-friendly: consent checkbox optional, IP only stored for spam control.
* Stylable through theme CSS or the included minimal styles, 3 visual themes.
* No tracking. The only optional external service is Cloudflare Turnstile, off by default.

== Installation ==

1. Upload the `ago-contact` folder to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to aGo Tools, then Contact.
4. Set the recipient email and other options.
5. Add `[agocontact]` to any page or post, or insert the aGo Contact Form block.

== Frequently Asked Questions ==

= Does it work with caching plugins? =

Yes. The form posts via standard form submission with nonce verification.

= Where are submissions stored? =

In a custom database table created on activation. View them in the Submissions tab.

= Can I export submissions? =

Yes. The Submissions screen includes a one-click CSV export of every submission.

== External services ==

This plugin connects to Cloudflare Turnstile only when the site owner enables Turnstile in the plugin settings. When disabled, no external request is made.

* Cloudflare Turnstile (`https://challenges.cloudflare.com/turnstile/v0/api.js` and `/turnstile/v0/siteverify`): loads a small JS widget on pages that include the contact form to challenge bot traffic, and validates the response server-side when a form is submitted. Required to use the Turnstile captcha option.
  * Terms: https://www.cloudflare.com/website-terms/
  * Privacy: https://www.cloudflare.com/privacypolicy/

== Privacy ==

* Submissions are stored locally in the site database, not sent to any third party.
* Visitor IP and user agent are stored alongside the submission for spam diagnostics.
* When Turnstile is enabled, the visitor's interaction with the widget is processed by Cloudflare for bot detection.

== Screenshots ==

1. The contact form on the front end, with configurable fields, math captcha and consent checkbox.
2. Settings screen: enable or require each field, choose a theme, set email notifications, spam protection and messages.
3. Submissions inbox: filter by status, view, mark as replied or spam, delete and export to CSV.

== Changelog ==

= 1.0.2 =
* Renamed the contact form shortcode to `[agocontact]` and the editor block to `agocontact/form`, and prefixed the remaining identifiers with a 4+ character prefix to avoid name collisions.

= 1.0.1 =
* Form fields are now fully configurable: every field, including name, email and message, can be enabled, disabled or marked as required from the settings screen.
* Translations now load automatically via WordPress just-in-time loading.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.2 =
The contact form shortcode is now `[agocontact]`.

= 1.0.1 =
All form fields are now fully configurable from the settings screen.

= 1.0.0 =
Initial release.
