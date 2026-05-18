=== aGo Contact ===
Contributors: sixtovaldese
Donate link: https://paypal.me/sixtovaldes
Tags: contact form, form, spam, submissions, email
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple contact form with spam protection, email notifications and submission management from the dashboard.

== Description ==

aGo Contact gives you a lightweight contact form shortcode with built-in spam protection, email notifications and a submissions inbox in the admin.

**Features**

* Shortcode `[ago_contact]` to drop the form on any page or post.
* Configurable recipient email, subject and success message.
* Honeypot + time check + rate limit per IP to block bots.
* Email notification to the site admin on every submission.
* Submissions inbox: read, mark as resolved or delete.
* GDPR-friendly: consent checkbox optional, IP only stored for spam control.
* Stylable through theme CSS or the included minimal styles.
* No external services. No tracking. No third-party APIs.

== Installation ==

1. Upload the `ago-contact` folder to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to aGo Tools, then Contact.
4. Set the recipient email and other options.
5. Add `[ago_contact]` to any page or post.

== Frequently Asked Questions ==

= Does it work with caching plugins? =

Yes. The form posts via standard form submission with nonce verification.

= Where are submissions stored? =

In a custom database table created on activation. View them in the Submissions tab.

= Can I export submissions? =

CSV export is planned for a future release.

== External services ==

This plugin connects to Cloudflare Turnstile only when the site owner enables Turnstile in the plugin settings. When disabled, no external request is made.

* Cloudflare Turnstile (`https://challenges.cloudflare.com/turnstile/v0/api.js` and `/turnstile/v0/siteverify`): loads a small JS widget on pages that include the contact form to challenge bot traffic, and validates the response server-side when a form is submitted. Required to use the Turnstile captcha option.
  * Terms: https://www.cloudflare.com/website-terms/
  * Privacy: https://www.cloudflare.com/privacypolicy/

== Privacy ==

* Submissions are stored locally in the site database, not sent to any third party.
* Visitor IP and user agent are stored alongside the submission for spam diagnostics.
* When Turnstile is enabled, the visitor's interaction with the widget is processed by Cloudflare for bot detection.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
