<?php

namespace AgoLab\Contact;

defined( 'ABSPATH' ) || exit;

class Plugin {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'maybe_upgrade_db' ] );
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ] );
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( 'admin_post_agocontact_export', [ $this, 'export_csv' ] );
        add_shortcode( 'agocontact', [ $this, 'render_shortcode' ] );
    }

    /* ───── DB schema guard ───── */

    public function maybe_upgrade_db(): void {
        if ( get_option( 'agocontact_db_version' ) !== AGOCONTACT_VERSION ) {
            Submission::create_table();
            update_option( 'agocontact_db_version', AGOCONTACT_VERSION );
        }
    }

    /* ───── Admin Menu ───── */

    public function admin_menu(): void {
        if ( empty( $GLOBALS['admin_page_hooks']['agolab-tools'] ) ) {
            add_menu_page(
                __( 'aGo Tools', 'ago-contact' ),
                __( 'aGo Tools', 'ago-contact' ),
                'manage_options',
                'agolab-tools',
                '__return_null',
                'dashicons-hammer',
                81
            );
        }

        add_submenu_page(
            'agolab-tools',
            __( 'aGo Contact', 'ago-contact' ),
            __( 'Contact', 'ago-contact' ),
            'manage_options',
            'agocontact',
            [ Admin\Settings::class, 'render' ]
        );

        remove_submenu_page( 'agolab-tools', 'agolab-tools' );

        // Submissions as top-level menu (next to Comments, position 26)
        $unread = Submission::count_unread();
        $badge  = $unread ? " <span class='update-plugins count-{$unread}'><span class='plugin-count'>{$unread}</span></span>" : '';

        add_menu_page(
            __( 'Submissions', 'ago-contact' ),
            __( 'Submissions', 'ago-contact' ) . $badge,
            'manage_options',
            'agocontact-submissions',
            [ Admin\Submissions::class, 'render' ],
            'dashicons-email-alt',
            26
        );
    }

    /* ───── Admin Assets ───── */

    public function admin_assets( string $hook ): void {
        if ( ! str_ends_with( $hook, '_page_agocontact' ) && $hook !== 'toplevel_page_agocontact-submissions' && ! str_ends_with( $hook, '_page_agocontact-submissions' ) ) {
            return;
        }

        wp_enqueue_style(
            'agocontact-admin',
            AGOCONTACT_URL . 'assets/css/admin.css',
            [],
            AGOCONTACT_VERSION
        );

        wp_enqueue_script(
            'agocontact-admin',
            AGOCONTACT_URL . 'assets/js/admin.js',
            [],
            AGOCONTACT_VERSION,
            true
        );

        wp_localize_script( 'agocontact-admin', 'agocontactAdmin', [
            'restUrl'   => rest_url( 'agocontact/v1' ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'exportUrl' => wp_nonce_url( admin_url( 'admin-post.php?action=agocontact_export' ), 'agocontact_export' ),
            'settings'  => self::get_settings(),
        ] );
    }

    /* ───── Frontend Assets ───── */

    public function frontend_assets(): void {
        // Only enqueue if shortcode or block is used
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }
        if ( ! has_shortcode( $post->post_content, 'agocontact' ) && ! has_block( 'agocontact/form', $post ) ) {
            return;
        }

        $settings = self::get_settings();

        wp_enqueue_style(
            'agocontact-form',
            AGOCONTACT_URL . 'assets/css/form.css',
            [],
            AGOCONTACT_VERSION
        );

        wp_enqueue_script(
            'agocontact-form',
            AGOCONTACT_URL . 'assets/js/form.js',
            [],
            AGOCONTACT_VERSION,
            true
        );

        $captcha_type = $settings['captcha_type'] ?? 'none';

        wp_localize_script( 'agocontact-form', 'agocontactForm', [
            'ajaxUrl'     => rest_url( 'agocontact/v1/submit' ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'captchaType' => $captcha_type,
            'turnstile'   => $captcha_type === 'turnstile' && ! empty( $settings['turnstile_site_key'] ),
            'siteKey'     => $settings['turnstile_site_key'] ?? '',
            'i18n'        => [
                'sending'  => __( 'Sending...', 'ago-contact' ),
                'success'  => __( 'Message sent successfully!', 'ago-contact' ),
                'error'    => __( 'Error sending message. Please try again.', 'ago-contact' ),
                'required' => __( 'Please fill in all required fields.', 'ago-contact' ),
            ],
        ] );

        // Turnstile script. Cloudflare requires their CDN for the widget; documented in readme External Services.
        if ( $captcha_type === 'turnstile' && ! empty( $settings['turnstile_site_key'] ) ) {
            // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Turnstile must load from Cloudflare CDN; declared as external service in readme.txt.
            wp_enqueue_script( 'agocontact-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], AGOCONTACT_VERSION, true );
        }
    }

    /* ───── Shortcode ───── */

    public function render_shortcode( $atts ): string {
        return Form::render( self::get_settings() );
    }

    /* ───── Gutenberg Block ───── */

    public function register_block(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        wp_register_script(
            'agocontact-block',
            AGOCONTACT_URL . 'blocks/form/index.js',
            [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-i18n' ],
            AGOCONTACT_VERSION,
            true
        );

        register_block_type( AGOCONTACT_PATH . 'blocks/form', [
            'render_callback' => function () {
                return Form::render( self::get_settings() );
            },
        ] );
    }

    /* ───── REST API ───── */

    public function register_routes(): void {
        $ns = 'agocontact/v1';

        // Admin: settings
        register_rest_route( $ns, '/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'rest_get_settings' ],
                'permission_callback' => [ $this, 'can_manage' ],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'rest_save_settings' ],
                'permission_callback' => [ $this, 'can_manage' ],
            ],
        ] );

        // Public by design: front-end visitors submit the contact form without
        // authentication. rest_submit() enforces honeypot, per-IP rate limiting,
        // optional captcha, and sanitizes every field before any side effect.
        register_rest_route( $ns, '/submit', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'rest_submit' ],
            'permission_callback' => '__return_true',
        ] );

        // Admin: submissions
        register_rest_route( $ns, '/submissions', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'rest_get_submissions' ],
            'permission_callback' => [ $this, 'can_manage' ],
        ] );

        // Admin: single submission actions
        register_rest_route( $ns, '/submissions/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'rest_get_submission' ],
            'permission_callback' => [ $this, 'can_manage' ],
        ] );

        register_rest_route( $ns, '/submissions/(?P<id>\d+)/status', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'rest_update_status' ],
            'permission_callback' => [ $this, 'can_manage' ],
        ] );

        register_rest_route( $ns, '/submissions/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [ $this, 'rest_delete_submission' ],
            'permission_callback' => [ $this, 'can_manage' ],
        ] );
    }

    public function can_manage(): bool {
        return current_user_can( 'manage_options' );
    }

    /* ── REST: Settings ── */

    public function rest_get_settings(): \WP_REST_Response {
        return new \WP_REST_Response( [ 'settings' => self::get_settings() ] );
    }

    public function rest_save_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $settings = self::sanitize_settings( $data );
        update_option( 'agocontact_settings', $settings );
        return new \WP_REST_Response( [ 'saved' => true, 'settings' => $settings ] );
    }

    /* ── REST: Submit (public) ── */

    public function rest_submit( \WP_REST_Request $request ): \WP_REST_Response {
        $data     = $request->get_json_params();
        $settings = self::get_settings();

        // Honeypot check
        if ( ! empty( $data['website'] ) ) {
            // Bot detected, silently accept
            return new \WP_REST_Response( [ 'ok' => true ] );
        }

        // Rate limiting
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
        $rate_key = 'agocontact_rate_' . md5( $ip );
        $count = (int) get_transient( $rate_key );
        $limit = (int) ( $settings['rate_limit'] ?? 5 );
        if ( $count >= $limit ) {
            return new \WP_REST_Response( [ 'ok' => false, 'error' => __( 'Too many submissions. Please try again later.', 'ago-contact' ) ], 429 );
        }

        // Captcha verification
        $captcha_type = $settings['captcha_type'] ?? 'none';

        if ( $captcha_type === 'math' ) {
            $answer = $data['math_answer'] ?? '';
            $hash   = $data['math_hash'] ?? '';
            if ( ! Spam::verify_math( (string) $answer, $hash ) ) {
                return new \WP_REST_Response( [ 'ok' => false, 'error' => __( 'Incorrect answer. Please try again.', 'ago-contact' ) ], 403 );
            }
        } elseif ( $captcha_type === 'turnstile' ) {
            // Turnstile selected but no secret configured: reject instead of
            // silently accepting unverified submissions.
            if ( empty( $settings['turnstile_secret_key'] ) ) {
                return new \WP_REST_Response( [ 'ok' => false, 'error' => __( 'Spam verification is misconfigured. Please contact the site administrator.', 'ago-contact' ) ], 403 );
            }
            $token = $data['cf-turnstile-response'] ?? '';
            if ( ! Spam::verify_turnstile( $token, $settings['turnstile_secret_key'] ) ) {
                return new \WP_REST_Response( [ 'ok' => false, 'error' => __( 'Spam verification failed. Please try again.', 'ago-contact' ) ], 403 );
            }
        }

        // Validate required fields
        $fields = $settings['fields'] ?? self::default_fields();
        $submission = [];

        foreach ( $fields as $key => $field ) {
            if ( empty( $field['enabled'] ) ) {
                continue;
            }
            $value = sanitize_text_field( $data[ $key ] ?? '' );
            if ( $key === 'message' ) {
                $value = sanitize_textarea_field( $data['message'] ?? '' );
            }
            if ( $key === 'email' ) {
                $value = sanitize_email( $data['email'] ?? '' );
            }
            if ( ! empty( $field['required'] ) && empty( $value ) ) {
                return new \WP_REST_Response( [
                    'ok'    => false,
                    /* translators: %s: name of the required field */
                    'error' => sprintf( __( 'The field "%s" is required.', 'ago-contact' ), $field['label'] ),
                ], 400 );
            }
            $submission[ $key ] = $value;
        }

        // GDPR consent check
        if ( ! empty( $fields['gdpr']['enabled'] ) && empty( $data['gdpr'] ) ) {
            return new \WP_REST_Response( [
                'ok'    => false,
                'error' => __( 'You must accept the privacy policy.', 'ago-contact' ),
            ], 400 );
        }

        // Save to DB
        $id = Submission::insert( $submission, $ip );
        if ( ! $id ) {
            return new \WP_REST_Response( [ 'ok' => false, 'error' => __( 'Could not save your message. Please try again later.', 'ago-contact' ) ], 500 );
        }

        // Update rate limit
        set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

        // Send email notification to admin
        $this->send_notification( $submission, $settings );

        // Send auto-reply
        if ( ! empty( $settings['autoreply_enabled'] ) && ! empty( $submission['email'] ) ) {
            $this->send_autoreply( $submission, $settings );
        }

        return new \WP_REST_Response( [ 'ok' => true ] );
    }

    /* ── REST: Submissions ── */

    public function rest_get_submissions( \WP_REST_Request $request ): \WP_REST_Response {
        $status = $request->get_param( 'status' ) ?: '';
        $page   = (int) ( $request->get_param( 'page' ) ?: 1 );
        $result = Submission::get_list( $status, $page, 20 );
        return new \WP_REST_Response( $result );
    }

    public function rest_get_submission( \WP_REST_Request $request ): \WP_REST_Response {
        $id   = (int) $request['id'];
        $item = Submission::get( $id );
        if ( ! $item ) {
            return new \WP_REST_Response( [ 'error' => 'Not found' ], 404 );
        }
        // Mark as read
        if ( $item->status === 'new' ) {
            Submission::update_status( $id, 'read' );
            $item->status = 'read';
        }
        return new \WP_REST_Response( $item );
    }

    public function rest_update_status( \WP_REST_Request $request ): \WP_REST_Response {
        $id     = (int) $request['id'];
        $status = sanitize_text_field( $request->get_json_params()['status'] ?? '' );
        if ( ! in_array( $status, [ 'new', 'read', 'replied', 'spam' ], true ) ) {
            return new \WP_REST_Response( [ 'error' => 'Invalid status' ], 400 );
        }
        Submission::update_status( $id, $status );
        return new \WP_REST_Response( [ 'ok' => true ] );
    }

    public function rest_delete_submission( \WP_REST_Request $request ): \WP_REST_Response {
        $id = (int) $request['id'];
        Submission::delete( $id );
        return new \WP_REST_Response( [ 'ok' => true ] );
    }

    /**
     * CSV export via admin-post.php (not REST): a REST response is always
     * JSON-encoded by core, so it cannot serve a raw CSV download.
     */
    public function export_csv(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to export submissions.', 'ago-contact' ), '', [ 'response' => 403 ] );
        }
        check_admin_referer( 'agocontact_export' );

        $items = Submission::get_all();
        $csv   = Submission::to_csv( $items );

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="ago-contact-submissions-' . gmdate( 'Y-m-d' ) . '.csv"' );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV body; values neutralized against formula injection in Submission::to_csv().
        echo $csv;
        exit;
    }

    /* ───── Email ───── */

    private function send_notification( array $submission, array $settings ): void {
        $to      = $settings['notification_email'] ?: get_option( 'admin_email' );
        $subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), $submission['subject'] ?? __( 'New contact message', 'ago-contact' ) );

        $body = __( 'New contact form submission:', 'ago-contact' ) . "\n\n";
        foreach ( $submission as $key => $value ) {
            if ( $key === 'gdpr' ) continue;
            $label = ucfirst( str_replace( '_', ' ', $key ) );
            $body .= "{$label}: {$value}\n";
        }
        $body .= "\n" . __( 'Sent from:', 'ago-contact' ) . ' ' . home_url();

        $headers = [];
        if ( ! empty( $submission['email'] ) && is_email( $submission['email'] ) ) {
            $name      = str_replace( [ '"', "\r", "\n" ], '', (string) ( $submission['name'] ?? '' ) );
            $headers[] = 'Reply-To: "' . $name . '" <' . $submission['email'] . '>';
        }

        wp_mail( $to, $subject, $body, $headers );
    }

    private function send_autoreply( array $submission, array $settings ): void {
        $to = $submission['email'];
        if ( ! is_email( $to ) ) {
            return;
        }
        /* translators: %s: site name */
        $subject = $settings['autoreply_subject'] ?: sprintf( __( 'We received your message, %s', 'ago-contact' ), get_bloginfo( 'name' ) );
        $body    = $settings['autoreply_body'] ?: __( "Thank you for contacting us. We have received your message and will get back to you as soon as possible.\n\nBest regards.", 'ago-contact' );
        $from    = get_bloginfo( 'name' );
        $headers = [ "From: {$from} <" . get_option( 'admin_email' ) . ">" ];

        wp_mail( $to, $subject, $body, $headers );
    }

    /* ───── Settings Helpers ───── */

    public static function get_settings(): array {
        $defaults = [
            'fields'              => self::default_fields(),
            'theme'               => 'modern',
            'notification_email'  => get_option( 'admin_email' ),
            'autoreply_enabled'   => false,
            'autoreply_subject'   => '',
            'autoreply_body'      => '',
            'captcha_type'        => 'none',
            'turnstile_site_key'  => '',
            'turnstile_secret_key'=> '',
            'rate_limit'          => 5,
            'gdpr_text'           => __( 'I accept the privacy policy', 'ago-contact' ),
            'success_message'     => __( 'Message sent successfully!', 'ago-contact' ),
            'department_options'  => "General\nSales\nSupport",
        ];
        $saved = get_option( 'agocontact_settings', [] );
        return wp_parse_args( $saved, $defaults );
    }

    public static function default_fields(): array {
        return [
            'name'       => [ 'enabled' => true, 'required' => true,  'label' => __( 'Name', 'ago-contact' ) ],
            'email'      => [ 'enabled' => true, 'required' => true,  'label' => __( 'Email', 'ago-contact' ) ],
            'phone'      => [ 'enabled' => false, 'required' => false, 'label' => __( 'Phone', 'ago-contact' ) ],
            'subject'    => [ 'enabled' => true, 'required' => false, 'label' => __( 'Subject', 'ago-contact' ) ],
            'company'    => [ 'enabled' => false, 'required' => false, 'label' => __( 'Company', 'ago-contact' ) ],
            'department' => [ 'enabled' => false, 'required' => false, 'label' => __( 'Department', 'ago-contact' ) ],
            'message'    => [ 'enabled' => true, 'required' => true,  'label' => __( 'Message', 'ago-contact' ) ],
            'gdpr'       => [ 'enabled' => false, 'required' => true,  'label' => __( 'I accept the privacy policy', 'ago-contact' ) ],
        ];
    }

    private static function sanitize_settings( array $data ): array {
        $clean = [];
        $clean['fields'] = [];
        $allowed_fields = array_keys( self::default_fields() );

        if ( ! empty( $data['fields'] ) && is_array( $data['fields'] ) ) {
            foreach ( $allowed_fields as $key ) {
                if ( isset( $data['fields'][ $key ] ) ) {
                    $clean['fields'][ $key ] = [
                        'enabled'  => ! empty( $data['fields'][ $key ]['enabled'] ),
                        'required' => ! empty( $data['fields'][ $key ]['required'] ),
                        'label'    => sanitize_text_field( $data['fields'][ $key ]['label'] ?? '' ),
                    ];
                }
            }
        }

        $clean['theme']              = in_array( $data['theme'] ?? '', [ 'modern', 'classic', 'minimal' ], true ) ? $data['theme'] : 'modern';
        $clean['notification_email'] = sanitize_email( $data['notification_email'] ?? '' );
        $clean['autoreply_enabled']  = ! empty( $data['autoreply_enabled'] );
        $clean['autoreply_subject']  = sanitize_text_field( $data['autoreply_subject'] ?? '' );
        $clean['autoreply_body']     = sanitize_textarea_field( $data['autoreply_body'] ?? '' );
        $clean['captcha_type']       = in_array( $data['captcha_type'] ?? '', [ 'none', 'math', 'turnstile' ], true ) ? $data['captcha_type'] : 'none';
        $clean['turnstile_site_key'] = sanitize_text_field( $data['turnstile_site_key'] ?? '' );
        $clean['turnstile_secret_key'] = sanitize_text_field( $data['turnstile_secret_key'] ?? '' );
        $clean['rate_limit']         = max( 1, min( 100, (int) ( $data['rate_limit'] ?? 5 ) ) );
        $clean['gdpr_text']          = sanitize_text_field( $data['gdpr_text'] ?? '' );
        $clean['success_message']    = sanitize_text_field( $data['success_message'] ?? '' );
        $clean['department_options'] = sanitize_textarea_field( $data['department_options'] ?? '' );

        return $clean;
    }
}
