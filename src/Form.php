<?php

namespace AgoLab\Contact;

defined( 'ABSPATH' ) || exit;

class Form {

    public static function render( array $settings ): string {
        $fields = $settings['fields'] ?? Plugin::default_fields();
        $theme  = $settings['theme'] ?? 'modern';

        ob_start();
        ?>
        <div class="ago-contact-form-wrap ago-theme-<?php echo esc_attr( $theme ); ?>">
            <form class="ago-contact-form" novalidate>
                <?php foreach ( $fields as $key => $field ) :
                    if ( empty( $field['enabled'] ) || $key === 'gdpr' ) continue;
                    $is_required = ! empty( $field['required'] );
                    $required    = $is_required ? 'required' : '';
                    $label_text  = $field['label'] ?? ucfirst( $key );
                ?>
                    <div class="ago-field ago-field-<?php echo esc_attr( $key ); ?>">
                        <label for="ago-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label_text ); ?><?php if ( $is_required ) : ?> <span class="ago-required">*</span><?php endif; ?></label>
                        <?php if ( $key === 'message' ) : ?>
                            <textarea id="ago-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="5"<?php if ( $is_required ) echo ' required'; ?>></textarea>
                        <?php elseif ( $key === 'department' ) :
                            $options = array_filter( array_map( 'trim', explode( "\n", $settings['department_options'] ?? '' ) ) );
                        ?>
                            <select id="ago-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php if ( $is_required ) echo ' required'; ?>>
                                <option value=""><?php esc_html_e( 'Select...', 'ago-contact' ); ?></option>
                                <?php foreach ( $options as $opt ) : ?>
                                    <option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ( $key === 'email' ) : ?>
                            <input type="email" id="ago-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php if ( $is_required ) echo ' required'; ?>>
                        <?php elseif ( $key === 'phone' ) : ?>
                            <input type="tel" id="ago-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php if ( $is_required ) echo ' required'; ?>>
                        <?php else : ?>
                            <input type="text" id="ago-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php if ( $is_required ) echo ' required'; ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php // GDPR consent
                if ( ! empty( $fields['gdpr']['enabled'] ) ) : ?>
                    <div class="ago-field ago-field-gdpr">
                        <label class="ago-checkbox-label">
                            <input type="checkbox" name="gdpr" value="1" required>
                            <span><?php echo esc_html( $settings['gdpr_text'] ?? $fields['gdpr']['label'] ); ?></span>
                        </label>
                    </div>
                <?php endif; ?>

                <div class="ago-hp" aria-hidden="true" style="position:absolute;left:-9999px">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <?php // Captcha
                $captcha_type = $settings['captcha_type'] ?? 'none';
                if ( $captcha_type === 'math' ) :
                    $math = Spam::generate_math();
                ?>
                    <div class="ago-field ago-field-captcha">
                        <?php /* translators: %s: math question (e.g. "3 + 4") */ ?>
                        <label for="ago-math-answer"><?php echo wp_kses_post( sprintf( __( 'What is %s?', 'ago-contact' ), '<strong>' . esc_html( $math['question'] ) . '</strong>' ) ); ?> <span class="ago-required">*</span></label>
                        <input type="number" id="ago-math-answer" name="math_answer" required style="max-width:120px">
                        <input type="hidden" name="math_hash" value="<?php echo esc_attr( $math['hash'] ); ?>">
                    </div>
                <?php elseif ( $captcha_type === 'turnstile' && ! empty( $settings['turnstile_site_key'] ) ) : ?>
                    <div class="ago-field ago-field-turnstile">
                        <div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $settings['turnstile_site_key'] ); ?>"></div>
                    </div>
                <?php endif; ?>

                <div class="ago-field ago-field-submit">
                    <button type="submit" class="ago-submit"><?php esc_html_e( 'Send Message', 'ago-contact' ); ?></button>
                </div>

                <div class="ago-form-status" style="display:none"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
