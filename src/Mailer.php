<?php

namespace AgoLab\Contact;

defined( 'ABSPATH' ) || exit;

class Mailer {

    /**
     * Send notification email to admin(s).
     */
    public static function send_notification( array $submission ): bool {
        $email_config = get_option( 'ago_contact_email', Plugin::default_email() );
        $to = $email_config['to'] ?: get_option( 'admin_email' );

        // Subject with placeholders
        $subject = str_replace(
            [ '{name}', '{email}', '{subject}', '{site_name}' ],
            [
                $submission['name'] ?? '',
                $submission['email'] ?? '',
                $submission['subject'] ?? '',
                get_bloginfo( 'name' ),
            ],
            $email_config['subject_template'] ?? 'New contact from {name}'
        );

        // Build body
        $body = __( 'New contact form submission:', 'ago-contact' ) . "\n\n";
        foreach ( [ 'name', 'email', 'phone', 'subject', 'company', 'department', 'message' ] as $field ) {
            if ( ! empty( $submission[ $field ] ) ) {
                $body .= ucfirst( $field ) . ': ' . $submission[ $field ] . "\n";
            }
        }
        $body .= "\n---\n" . __( 'Sent from:', 'ago-contact' ) . ' ' . home_url();

        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
        if ( ! empty( $email_config['cc'] ) ) {
            $headers[] = 'Cc: ' . $email_config['cc'];
        }
        if ( ! empty( $submission['email'] ) ) {
            $headers[] = 'Reply-To: ' . $submission['email'];
        }

        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Send auto-reply to the person who submitted the form.
     */
    public static function send_auto_reply( array $submission ): bool {
        $config = get_option( 'ago_contact_email', Plugin::default_email() );

        if ( empty( $config['auto_reply'] ) || empty( $submission['email'] ) ) {
            return false;
        }

        $subject = str_replace(
            [ '{name}', '{site_name}' ],
            [ $submission['name'] ?? '', get_bloginfo( 'name' ) ],
            $config['auto_reply_subject'] ?? 'We received your message'
        );

        $body = str_replace(
            [ '{name}', '{site_name}' ],
            [ $submission['name'] ?? '', get_bloginfo( 'name' ) ],
            $config['auto_reply_message'] ?? "Hi {name},\n\nThank you for contacting us.\n\nBest regards,\n{site_name}"
        );

        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

        return wp_mail( $submission['email'], $subject, $body, $headers );
    }
}
