<?php

namespace AgoLab\Contact;

defined( 'ABSPATH' ) || exit;

class Spam {

    /* ───── Math Captcha ───── */

    public static function generate_math(): array {
        $ops = [ '+', '-' ];
        $op  = $ops[ array_rand( $ops ) ];

        if ( $op === '+' ) {
            $a = wp_rand( 1, 15 );
            $b = wp_rand( 1, 15 );
            $answer = $a + $b;
        } else {
            $a = wp_rand( 5, 20 );
            $b = wp_rand( 1, $a - 1 );
            $answer = $a - $b;
        }

        $question = sprintf( '%d %s %d', $a, $op, $b );
        $hash     = self::math_hash( $answer );

        return [
            'question' => $question,
            'hash'     => $hash,
        ];
    }

    public static function verify_math( string $answer, string $hash ): bool {
        if ( $answer === '' || $hash === '' ) {
            return false;
        }
        return hash_equals( $hash, self::math_hash( (int) $answer ) );
    }

    private static function math_hash( int $answer ): string {
        $salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'ago-contact-salt';
        return hash_hmac( 'sha256', (string) $answer, $salt . gmdate( 'Y-m-d-H' ) );
    }

    /* ───── Turnstile ───── */

    public static function verify_turnstile( string $token, string $secret ): bool {
        if ( empty( $token ) || empty( $secret ) ) {
            return false;
        }

        $response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ],
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return ! empty( $body['success'] );
    }
}
