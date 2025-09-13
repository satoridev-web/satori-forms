<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Satori_Forms_Shortcode {
    public static function register() {
        add_shortcode( 'satori_forms', [ __CLASS__, 'render' ] );
    }

    public static function render( $atts ) {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'satori_forms' );
        $form_id = intval( $atts['id'] );
        if ( ! $form_id ) {
            return '<p>No form ID specified.</p>';
        }
        ob_start();
        include SATORI_FORMS_PATH . 'templates/form.php';
        return ob_get_clean();
    }
}
