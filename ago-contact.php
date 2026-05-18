<?php
/**
 * Plugin Name: aGo Contact
 * Plugin URI:  https://ago.cl/herramientas/
 * Description: Simple contact form with spam protection, email notifications, and submission management.
 * Version:     1.0.0
 * Author:      aGo Lab
 * Author URI:  https://github.com/agolab
 * License:     GPL-2.0-or-later
 * Text Domain: ago-contact
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

defined( 'ABSPATH' ) || exit;

define( 'AGO_CONTACT_VERSION', '1.0.0' );
define( 'AGO_CONTACT_FILE', __FILE__ );
define( 'AGO_CONTACT_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGO_CONTACT_URL', plugin_dir_url( __FILE__ ) );

/* PSR-4 Autoloader: AgoLab\Contact\ → src/ */
spl_autoload_register( function ( string $class ) {
    $prefix = 'AgoLab\\Contact\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
        return;
    }
    $relative = substr( $class, strlen( $prefix ) );
    $file     = AGO_CONTACT_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
    if ( file_exists( $file ) ) {
        require $file;
    }
});

/* Activation, create submissions table. */
register_activation_hook( __FILE__, function () {
    \AgoLab\Contact\Submission::create_table();
});

/* Boot. */
add_action( 'plugins_loaded', function () {
    \AgoLab\Contact\Plugin::instance();
});
