<?php
if ( ! class_exists( 'TMSConnect' ) ) {

	class TMSConnect {

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
				self::$instance = new TMSConnect();
				self::$instance->setup();
			}
			return self::$instance;
		}

		public function setup() {

		}
	}
}

function TMSConnect() {
	return TMSConnect::instance();
}
add_action( 'after_setup_theme', 'TMSConnect' );
