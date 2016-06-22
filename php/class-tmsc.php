<?php
if ( ! class_exists( 'TMSC' ) ) {

	class TMSC {

		private static $instance;

		/**
		 * Constructor
		 *
		 * @params string $name
		 * @params url $name optional
		 * @return void
		 */
		public function __construct() {
			/* Don't do anything, needs to be initialized via instance() method */
		}

		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new TMSC();
				self::$instance->setup();
			}
			return self::$instance;
		}

		public function setup() {

		}
	}
}

function TMSC() {
	return TMSC::instance();
}
add_action( 'after_setup_theme', 'TMSC' );
