<?php
/**
 * Plugin Name: aGo Contact
 * Plugin URI:  https://ago.cl/herramientas/
 * Description: Simple contact form with spam protection, email notifications, and submission management.
 * Version:     1.0.2
 * Author:      aGo Lab
 * Author URI:  https://ago.cl/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ago-contact
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

defined( 'ABSPATH' ) || exit;

define( 'AGOCONTACT_VERSION', '1.0.2' );
define( 'AGOCONTACT_FILE', __FILE__ );
define( 'AGOCONTACT_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGOCONTACT_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( string $class ) {
    $prefix = 'AgoLab\\Contact\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
        return;
    }
    $relative = substr( $class, strlen( $prefix ) );
    $file     = AGOCONTACT_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
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
