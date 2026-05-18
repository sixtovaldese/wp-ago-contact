<?php

namespace AgoLab\Contact\Admin;

defined( 'ABSPATH' ) || exit;

class Page {

    public static function render(): void {
        ?>
        <div class="wrap">
            <h1>
                <img src="<?php echo esc_url( AGO_CONTACT_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:28px;width:auto;vertical-align:middle;margin-right:8px">
                <?php esc_html_e( 'aGo Contact', 'ago-contact' ); ?>
                <span style="font-size:12px;color:#999;margin-left:8px">v<?php echo esc_html( AGO_CONTACT_VERSION ); ?></span>
            </h1>

            <div class="ago-layout">
                <div class="ago-main">

                    <!-- Form Fields -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Form Fields', 'ago-contact' ); ?></h2>
                        <p class="description"><?php esc_html_e( 'Enable, reorder, and configure the fields shown in your contact form.', 'ago-contact' ); ?></p>

                        <table class="widefat ago-fields-table" id="ago-fields-table">
                            <thead>
                                <tr>
                                    <th style="width:30px"></th>
                                    <th><?php esc_html_e( 'Field', 'ago-contact' ); ?></th>
                                    <th style="width:100px"><?php esc_html_e( 'Label', 'ago-contact' ); ?></th>
                                    <th style="width:80px;text-align:center"><?php esc_html_e( 'Enabled', 'ago-contact' ); ?></th>
                                    <th style="width:80px;text-align:center"><?php esc_html_e( 'Required', 'ago-contact' ); ?></th>
                                    <th style="width:160px"><?php esc_html_e( 'Options', 'ago-contact' ); ?></th>
                                </tr>
                            </thead>
                            <tbody id="ago-fields-body">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Email Notifications -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Email Notifications', 'ago-contact' ); ?></h2>

                        <table class="form-table ago-form-table">
                            <tr>
                                <th><label for="ago-email-to"><?php esc_html_e( 'Send to', 'ago-contact' ); ?></label></th>
                                <td>
                                    <input type="email" id="ago-email-to" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                                    <p class="description"><?php esc_html_e( 'Leave empty to use the admin email.', 'ago-contact' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="ago-email-cc"><?php esc_html_e( 'CC', 'ago-contact' ); ?></label></th>
                                <td>
                                    <input type="text" id="ago-email-cc" class="regular-text" placeholder="cc@example.com">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="ago-email-subject"><?php esc_html_e( 'Subject template', 'ago-contact' ); ?></label></th>
                                <td>
                                    <input type="text" id="ago-email-subject" class="regular-text" value="">
                                    <p class="description"><?php esc_html_e( 'Available placeholders: {name}, {email}, {subject}, {site_name}', 'ago-contact' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="ago-auto-reply"><?php esc_html_e( 'Auto-reply', 'ago-contact' ); ?></label></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="ago-auto-reply">
                                        <?php esc_html_e( 'Send automatic reply to the sender', 'ago-contact' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr class="ago-auto-reply-fields">
                                <th><label for="ago-auto-reply-subject"><?php esc_html_e( 'Reply subject', 'ago-contact' ); ?></label></th>
                                <td>
                                    <input type="text" id="ago-auto-reply-subject" class="regular-text">
                                </td>
                            </tr>
                            <tr class="ago-auto-reply-fields">
                                <th><label for="ago-auto-reply-message"><?php esc_html_e( 'Reply message', 'ago-contact' ); ?></label></th>
                                <td>
                                    <textarea id="ago-auto-reply-message" class="large-text" rows="5"></textarea>
                                    <p class="description"><?php esc_html_e( 'Available placeholders: {name}, {site_name}', 'ago-contact' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Anti-Spam -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Anti-Spam', 'ago-contact' ); ?></h2>

                        <table class="form-table ago-form-table">
                            <tr>
                                <th><?php esc_html_e( 'Honeypot', 'ago-contact' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="ago-spam-honeypot" checked>
                                        <?php esc_html_e( 'Enable honeypot field (recommended)', 'ago-contact' ); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e( 'Invisible trap for bots. Zero impact on real users.', 'ago-contact' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Turnstile', 'ago-contact' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="ago-spam-turnstile">
                                        <?php esc_html_e( 'Enable Cloudflare Turnstile', 'ago-contact' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr class="ago-turnstile-fields">
                                <th><label for="ago-turnstile-site"><?php esc_html_e( 'Site Key', 'ago-contact' ); ?></label></th>
                                <td><input type="text" id="ago-turnstile-site" class="regular-text"></td>
                            </tr>
                            <tr class="ago-turnstile-fields">
                                <th><label for="ago-turnstile-secret"><?php esc_html_e( 'Secret Key', 'ago-contact' ); ?></label></th>
                                <td><input type="text" id="ago-turnstile-secret" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Appearance -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Appearance', 'ago-contact' ); ?></h2>

                        <table class="form-table ago-form-table">
                            <tr>
                                <th><?php esc_html_e( 'Style', 'ago-contact' ); ?></th>
                                <td>
                                    <fieldset class="ago-style-radios">
                                        <label><input type="radio" name="ago-style" value="modern" checked> <?php esc_html_e( 'Modern', 'ago-contact' ); ?></label>
                                        <label><input type="radio" name="ago-style" value="classic"> <?php esc_html_e( 'Classic', 'ago-contact' ); ?></label>
                                        <label><input type="radio" name="ago-style" value="minimal"> <?php esc_html_e( 'Minimal', 'ago-contact' ); ?></label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="ago-button-text"><?php esc_html_e( 'Button text', 'ago-contact' ); ?></label></th>
                                <td><input type="text" id="ago-button-text" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="ago-success-msg"><?php esc_html_e( 'Success message', 'ago-contact' ); ?></label></th>
                                <td><input type="text" id="ago-success-msg" class="large-text"></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'GDPR consent', 'ago-contact' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="ago-gdpr-enabled">
                                        <?php esc_html_e( 'Show GDPR consent checkbox', 'ago-contact' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr class="ago-gdpr-fields">
                                <th><label for="ago-gdpr-text"><?php esc_html_e( 'GDPR text', 'ago-contact' ); ?></label></th>
                                <td><input type="text" id="ago-gdpr-text" class="large-text"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Usage -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Usage', 'ago-contact' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Add the contact form to any page or post:', 'ago-contact' ); ?>
                        </p>
                        <ul style="margin:10px 0 0;list-style:disc;padding-left:20px">
                            <?php /* translators: %s: name of the Gutenberg block */ ?>
                            <li><?php echo wp_kses_post( sprintf( __( '<strong>Gutenberg block:</strong> Search for %s in the block inserter.', 'ago-contact' ), '<code>aGo Contact Form</code>' ) ); ?></li>
                            <?php /* translators: %s: shortcode tag */ ?>
                            <li><?php echo wp_kses_post( sprintf( __( '<strong>Shortcode:</strong> Use %s anywhere.', 'ago-contact' ), '<code>[ago-contact]</code>' ) ); ?></li>
                        </ul>
                    </div>

                    <p class="submit">
                        <button id="ago-save-btn" class="button button-primary">
                            <?php esc_html_e( 'Save Settings', 'ago-contact' ); ?>
                        </button>
                        <span id="ago-save-status" class="ago-status"></span>
                    </p>

                </div>

                <!-- SIDEBAR -->
                <div class="ago-sidebar">

                    <!-- About -->
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-contact' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'Simple, focused contact form. Activate, configure email, insert block, done. No complexity, no bloat.', 'ago-contact' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><?php esc_html_e( 'ONE form, works in 2 minutes', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Submissions stored in database', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Email notifications + auto-reply', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Honeypot + Turnstile anti-spam', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( '3 visual themes', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'GDPR consent checkbox', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'CSV export', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'No jQuery, under 3KB JS', 'ago-contact' ); ?></li>
                        </ul>
                    </div>

                    <!-- Donation -->
                    <div class="card ago-card ago-donation">
                        <h3><?php esc_html_e( 'Support Open Source', 'ago-contact' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'If this plugin saves you time, consider supporting our open-source work.', 'ago-contact' ); ?>
                        </p>
                        <div class="ago-donation-amounts">
                            <a href="https://paypal.me/sixtovaldes/3" class="ago-amount" target="_blank" rel="noopener">$3</a>
                            <a href="https://paypal.me/sixtovaldes/5" class="ago-amount" target="_blank" rel="noopener">$5</a>
                            <a href="https://paypal.me/sixtovaldes/10" class="ago-amount" target="_blank" rel="noopener">$10</a>
                        </div>
                        <a href="https://paypal.me/sixtovaldes" class="ago-coffee-btn" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-coffee" style="margin-right:6px"></span>
                            <?php esc_html_e( 'Buy us a coffee', 'ago-contact' ); ?>
                        </a>
                        <p class="ago-donation-note">
                            <?php esc_html_e( 'Voluntary donation. Thank you!', 'ago-contact' ); ?>
                        </p>
                    </div>

                    <!-- Footer with logo -->
                    <div class="ago-footer">
                        <a href="https://ago.cl" target="_blank" rel="noopener" class="ago-footer-logo">
                            <img src="<?php echo esc_url( AGO_CONTACT_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:40px;width:auto">
                        </a>
                        <p>
                            <?php
                            echo wp_kses_post(
                                sprintf(
                                    /* translators: %1$s: heart, %2$s: link to ago.cl */
                                    __( 'Developed with %1$s by %2$s', 'ago-contact' ),
                                    '<span style="color:#e25555">&#10084;</span>',
                                    '<a href="https://ago.cl" target="_blank" rel="noopener"><strong>aGo Lab</strong></a>'
                                )
                            );
                            ?>
                        </p>
                        <p style="font-size:11px;color:#999">
                            <?php esc_html_e( 'Building tools for the web, one plugin at a time.', 'ago-contact' ); ?>
                        </p>
                    </div>

                </div>
            </div>

        </div>
        <?php
    }
}
