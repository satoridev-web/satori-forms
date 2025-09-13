<?php
/**
 * Plugin Name: SATORI Forms
 * Description: Lightweight and extensible form builder plugin for WordPress.
 * Version: 0.1.0
 * Author: Satori Graphics Pty Ltd
 * License: GPLv2 or later
 * Text Domain: satori-forms
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SATORI_FORMS_VERSION', '0.1.0' );
define( 'SATORI_FORMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SATORI_FORMS_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once SATORI_FORMS_PATH . 'includes/class-satori-forms-cpt.php';
require_once SATORI_FORMS_PATH . 'includes/class-satori-forms-shortcode.php';

// Activation hook
register_activation_hook( __FILE__, function() {
    Satori_Forms_CPT::register();
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
});

// Init plugin
add_action( 'init', function() {
    Satori_Forms_CPT::register();
    Satori_Forms_Shortcode::register();
});
