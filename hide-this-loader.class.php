<?php
/**
 * Hide_This_Loader
 *
 * Loads Hide_This and add shortcodes from its content.
 *
 * @package Hide_This
 * @since   1.1
 */
class Hide_This_Loader {

	function __construct() {
		// Main shortcode.
		add_shortcode( 'hide', array( $this, 'shortcode' ) );
		// Alternative shortcode, in case you have to deal with compatibility issues.
		add_shortcode( 'hidethis', array( $this, 'shortcode' ) );
	}

	function shortcode( $atts, $content ) {
		$hide_this = new Hide_This( $atts, $content );
		return $hide_this->__get( 'content' );
	}

}
