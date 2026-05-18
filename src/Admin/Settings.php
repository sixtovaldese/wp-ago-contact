<?php

namespace AgoLab\Contact\Admin;

use AgoLab\Contact\Plugin;

defined( 'ABSPATH' ) || exit;

class Settings {

    public static function render(): void {
        $settings = Plugin::get_settings();
        $fields   = $settings['fields'] ?? Plugin::default_fields();
        ?>
        <div class="wrap ago-wrap">
            <div class="ago-layout">
                <div class="ago-main">
                    <div class="ago-header">
                        <img src="<?php echo esc_url( AGO_CONTACT_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" class="ago-logo">
                        <div>
                            <h1><?php esc_html_e( 'aGo Contact', 'ago-contact' ); ?></h1>
                            <p class="ago-desc"><?php esc_html_e( 'Simple contact form with spam protection and submission management.', 'ago-contact' ); ?></p>
                        </div>
                    </div>

                    <!-- Fields Configuration -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Form Fields', 'ago-contact' ); ?></h2>
                        <p class="ago-card-desc"><?php esc_html_e( 'Enable or disable fields and set them as required. Use the shortcode [ago-contact] or the Gutenberg block to display the form.', 'ago-contact' ); ?></p>
                        <table class="ago-fields-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Field', 'ago-contact' ); ?></th>
                                    <th><?php esc_html_e( 'Enabled', 'ago-contact' ); ?></th>
                                    <th><?php esc_html_e( 'Required', 'ago-contact' ); ?></th>
                                    <th><?php esc_html_e( 'Label', 'ago-contact' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $fields as $key => $field ) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html( ucfirst( $key ) ); ?></strong></td>
                                    <td>
                                        <?php if ( in_array( $key, [ 'name', 'email', 'message' ], true ) ) : ?>
                                            <input type="checkbox" checked disabled>
                                            <input type="hidden" data-field="<?php echo esc_attr( $key ); ?>" data-prop="enabled" value="1">
                                        <?php else : ?>
                                            <input type="checkbox" data-field="<?php echo esc_attr( $key ); ?>" data-prop="enabled" <?php checked( ! empty( $field['enabled'] ) ); ?>>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( in_array( $key, [ 'email', 'message' ], true ) ) : ?>
                                            <input type="checkbox" checked disabled>
                                            <input type="hidden" data-field="<?php echo esc_attr( $key ); ?>" data-prop="required" value="1">
                                        <?php else : ?>
                                            <input type="checkbox" data-field="<?php echo esc_attr( $key ); ?>" data-prop="required" <?php checked( ! empty( $field['required'] ) ); ?>>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="text" data-field="<?php echo esc_attr( $key ); ?>" data-prop="label" value="<?php echo esc_attr( $field['label'] ?? '' ); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Department Options -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Department Options', 'ago-contact' ); ?></h2>
                        <p class="ago-card-desc"><?php esc_html_e( 'One option per line. Only used when the Department field is enabled.', 'ago-contact' ); ?></p>
                        <textarea id="ago-department-options" rows="4" class="large-text"><?php echo esc_textarea( $settings['department_options'] ?? '' ); ?></textarea>
                    </div>

                    <!-- Appearance -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Appearance', 'ago-contact' ); ?></h2>
                        <div class="ago-radio-group">
                            <?php foreach ( [ 'modern' => 'Modern', 'classic' => 'Classic', 'minimal' => 'Minimal' ] as $val => $label ) : ?>
                            <label class="ago-radio-card">
                                <input type="radio" name="ago-theme" value="<?php echo esc_attr( $val ); ?>" <?php checked( $settings['theme'], $val ); ?>>
                                <span><?php echo esc_html( $label ); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Email Notifications', 'ago-contact' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Notification Email', 'ago-contact' ); ?></th>
                                <td><input type="email" id="ago-notification-email" value="<?php echo esc_attr( $settings['notification_email'] ); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Auto-Reply', 'ago-contact' ); ?></th>
                                <td>
                                    <label><input type="checkbox" id="ago-autoreply-enabled" <?php checked( ! empty( $settings['autoreply_enabled'] ) ); ?>> <?php esc_html_e( 'Send automatic reply to visitor', 'ago-contact' ); ?></label>
                                </td>
                            </tr>
                            <tr class="ago-autoreply-fields" style="<?php echo empty( $settings['autoreply_enabled'] ) ? 'display:none' : ''; ?>">
                                <th><?php esc_html_e( 'Reply Subject', 'ago-contact' ); ?></th>
                                <td><input type="text" id="ago-autoreply-subject" value="<?php echo esc_attr( $settings['autoreply_subject'] ); ?>" class="regular-text"></td>
                            </tr>
                            <tr class="ago-autoreply-fields" style="<?php echo empty( $settings['autoreply_enabled'] ) ? 'display:none' : ''; ?>">
                                <th><?php esc_html_e( 'Reply Body', 'ago-contact' ); ?></th>
                                <td><textarea id="ago-autoreply-body" rows="4" class="large-text"><?php echo esc_textarea( $settings['autoreply_body'] ); ?></textarea></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Spam Protection -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Spam Protection', 'ago-contact' ); ?></h2>
                        <p class="ago-card-desc"><?php esc_html_e( 'Honeypot is always active. Choose additional verification below.', 'ago-contact' ); ?></p>
                        <?php $captcha_type = $settings['captcha_type'] ?? 'none'; ?>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Rate Limit', 'ago-contact' ); ?></th>
                                <td>
                                    <input type="number" id="ago-rate-limit" value="<?php echo (int) $settings['rate_limit']; ?>" min="1" max="100" style="width:70px">
                                    <span class="description"><?php esc_html_e( 'submissions per hour per IP', 'ago-contact' ); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Additional Verification', 'ago-contact' ); ?></th>
                                <td>
                                    <fieldset>
                                        <label style="display:block;margin-bottom:6px">
                                            <input type="radio" name="ago-captcha-type" value="none" <?php checked( $captcha_type, 'none' ); ?>>
                                            <?php esc_html_e( 'None (honeypot only)', 'ago-contact' ); ?>
                                        </label>
                                        <label style="display:block;margin-bottom:6px">
                                            <input type="radio" name="ago-captcha-type" value="math" <?php checked( $captcha_type, 'math' ); ?>>
                                            <?php esc_html_e( 'Math captcha (e.g. "What is 4 + 7?")', 'ago-contact' ); ?>
                                            <span class="description"><?php esc_html_e( 'No external API needed', 'ago-contact' ); ?></span>
                                        </label>
                                        <label style="display:block;margin-bottom:6px">
                                            <input type="radio" name="ago-captcha-type" value="turnstile" <?php checked( $captcha_type, 'turnstile' ); ?>>
                                            <?php esc_html_e( 'Cloudflare Turnstile', 'ago-contact' ); ?>
                                            <span class="description"><?php esc_html_e( 'Requires site key and secret key', 'ago-contact' ); ?></span>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr class="ago-turnstile-fields" style="<?php echo $captcha_type !== 'turnstile' ? 'display:none' : ''; ?>">
                                <th><?php esc_html_e( 'Site Key', 'ago-contact' ); ?></th>
                                <td><input type="text" id="ago-turnstile-site-key" value="<?php echo esc_attr( $settings['turnstile_site_key'] ); ?>" class="regular-text"></td>
                            </tr>
                            <tr class="ago-turnstile-fields" style="<?php echo $captcha_type !== 'turnstile' ? 'display:none' : ''; ?>">
                                <th><?php esc_html_e( 'Secret Key', 'ago-contact' ); ?></th>
                                <td><input type="text" id="ago-turnstile-secret-key" value="<?php echo esc_attr( $settings['turnstile_secret_key'] ); ?>" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Messages -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Messages', 'ago-contact' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Success Message', 'ago-contact' ); ?></th>
                                <td><input type="text" id="ago-success-message" value="<?php echo esc_attr( $settings['success_message'] ); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'GDPR Text', 'ago-contact' ); ?></th>
                                <td><input type="text" id="ago-gdpr-text" value="<?php echo esc_attr( $settings['gdpr_text'] ); ?>" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Shortcode Info -->
                    <div class="ago-card">
                        <h2><?php esc_html_e( 'Usage', 'ago-contact' ); ?></h2>
                        <p><?php esc_html_e( 'Add the contact form to any page or post:', 'ago-contact' ); ?></p>
                        <code style="display:inline-block;padding:8px 16px;background:#f0f0f1;border-radius:4px;font-size:14px">[ago-contact]</code>
                        <p style="margin-top:12px"><?php esc_html_e( 'Or use the "aGo Contact Form" Gutenberg block.', 'ago-contact' ); ?></p>
                    </div>

                    <button id="ago-save-settings" class="button button-primary button-hero"><?php esc_html_e( 'Save Settings', 'ago-contact' ); ?></button>
                    <div id="ago-contact-status" style="display:none"></div>
                </div>

                <!-- Sidebar -->
                <div class="ago-sidebar">

                    <!-- About -->
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-contact' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'Simple, lightweight contact form plugin. Honeypot + Turnstile spam protection, email notifications, and submission management.', 'ago-contact' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><?php esc_html_e( 'Configurable form fields', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Honeypot anti-spam (always active)', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Cloudflare Turnstile support', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Math captcha (no API needed)', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Email notifications + auto-reply', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Submission management + CSV export', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( '3 visual themes', 'ago-contact' ); ?></li>
                            <li><?php esc_html_e( 'Works with aGo SMTP', 'ago-contact' ); ?></li>
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
                                    /* translators: 1: heart icon HTML, 2: link to ago.cl */
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
