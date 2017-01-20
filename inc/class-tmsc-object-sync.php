<?php
/**
 * Use the event scheduler to set up our object sync.
 * Can also be fired manually via the admin ui.
 *
 */
class TMSC_Object_Sync {
	private static $instance;

	/**
	 * The capability required to manage object sync. Defaults to 'manage_options'.
	 * @var string
	 */
	public static $capability = 'manage_options';

	// The url prefix we use to grab images from I.D.S.
	// Built using: http://ids.si.edu/ids/deliveryService
	public static $image_url = 'http://ids.si.edu/ids/deliveryService';

	// TMS DB Server address.
	public static $tms_db_host = '';

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}

	public function __clone() {
		wp_die( "Please don't __clone TMSC_2016_Cards_Sync" );
	}

	public function __wakeup() {
		wp_die( "Please don't __wakeup TMSC_2016_Cards_Sync" );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TMSC_Object_Sync;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		// Our defaults
		self::$tms_db_host = get_option( 'tmsc-db-host', self::$tms_db_host );
		self::$image_url = get_option( 'tmsc-ids-image-url', self::$image_url );

		if ( ! empty( self::$tms_db_host ) ) {
			// Our Cron Setup
			add_filter( 'cron_schedules', array( self::$instance, 'add_intervals' ) );
			add_action( 'tmsc_cron_events', array( self::$instance, 'cron_events' ), 10, 1 );
			add_action( 'wp', array( self::$instance, 'cron_events_activation' ) );
		}

		if ( current_user_can( self::$capability ) ) {
			// Our Admin Area Menu
			add_action( 'admin_menu', array( self::$instance, 'add_menu_pages' ) );
			add_action( 'wp_ajax_sync_objects', array( self::$instance, 'sync_objects' ) );
		}
	}

	/**
	 * Add feed-card CPT and related taxonomies to primary card menu
	 */
	public static function add_menu_pages() {
		add_submenu_page( 'edit.php?post_type=tms_object', __( 'TMSC Sync', 'tmsc' ), __( 'Sync TMS Objects', 'tmsc' ), 'manage_options', 'card-sync', array( self::$instance, 'render_object_sync_submenu_page' ) );
	}

	/**
	 * Render our admin card sync template part
	 */
	public function render_object_sync_submenu_page() {
		load_template( TMSCONNECT_PATH . '/templates/object-sync-admin.php' );
	}

	/**
	 * Our ajax handler for syncing cards manually from the wp-admin area submenu.
	 */
	public function sync_objects() {
		if ( current_user_can( self::$capability ) ) {
			check_ajax_referer( 'tmsc_object_sync', 'tmsc_nonce' );
			if ( ! empty( $_POST['tmsc-db-host'] ) ) {
				$key = sanitize_text_field( wp_unslash( $_POST['tmsc-db-host'] ) );
				update_option( 'tmsc-db-host', $key, false );
				self::$api_key = $key;
			}
			if ( ! empty( $_POST['tmsc-ids-image-url'] ) ) {
				$url = esc_url_raw( wp_unslash( $_POST['tmsc-ids-image-url'] ) );
				update_option( 'tmsc-ids-image-url', $url, false );
				self::$image_url = $url;
			}
			$full_sync = empty( $_POST['tmsc-full-sync'] ) ? false : true;
			wp_schedule_single_event( time(), 'tmsc_cron_events', array( array( 'full_sync' => $full_sync ) ) );
			echo 1;
		} else {
			echo 0;
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'tmsc' ) );
		}
		exit();
	}

	/**
	 * Add custom intervals
	 */
	public function add_intervals( $schedules ) {
		$schedules['halfhour'] = array(
			'interval' => 1800,
			'display' => __( 'Every Half Hour', 'tmsc' ),
		);
		return $schedules;
	}

	public function cron_events_activation() {
		// Run our Cron every 30 mins.
		if ( ! wp_next_scheduled( 'tmsc_cron_events' ) ) {
			wp_schedule_event( time(), 'halfhour', 'tmsc_cron_events' );
		}
	}

	/**
	 * Add in any cron events that need to be fired every 30 mins here.
	 * @param array. $args. An array of args to pass to this event scheduler hook.
	 */
	public function cron_events( $args = array() ) {
		// Make sure sync is not currently running
		if ( 'Syncing Cards' !== get_option( 'tmsc-last-sync-date' ) ) {
			// Update our card custom post type.
			$full_sync = ( ! empty( $args['full_sync'] ) ) ? true : false;
			self::$instance->object_sync( $full_sync );
		}
	}

	// Connect to the feed and update our card post types with the latest data.
	public function object_sync( $full_sync = false ) {

		$message = __( 'Syncing TMS Objects', 'tmsc' );
		update_option( 'tmsc-last-sync-date', $message, false );

		// Cron events are fired before pluggable and media_sideload_image are loaded.
		// Ensure the function exists.
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}

		$url = self::$feed_url . '?api_key=' . self::$api_key;

		// VIP safe remote get hard enforces a 3 sec timeout which won't fly with this feed. Use the core call and we will manage the errors.
		$remote_data = wp_safe_remote_get( $url, array( 'headers' => array( 'Content-Type: application/json' ), 'timeout' => 60 ) );

		if ( is_wp_error( $remote_data ) ) {
			$message = $remote_data->get_error_message();
		} elseif ( ! empty( $remote_data ) ) {

			$code = wp_remote_retrieve_response_code( $remote_data );

			if ( 200 === $code ) {
				$body = wp_remote_retrieve_body( $remote_data );

				if ( ! empty( $body ) ) {
					$body = json_decode( $body, true );
				}

				if ( 200 === $body['status_code'] && ! empty( $body['data']['products'] ) ) {

					// Set all currently active cards to archived. Never should be more than 350 cards active at a time.
					// @codingStandardsIgnoreStart
					$existing_cards = get_posts( array( 'post_type' => 'feed-card', 'post_status' => 'publish', 'posts_per_page' => 350 ) );
					// @codingStandardsIgnoreEnd

					// If archiveless plugin is installed use that status, otherwise set to draft.
					$archive_status = class_exists( 'Archiveless' ) ? 'archiveless' : 'draft';
					foreach ( $existing_cards as $existing_card ) {
						$existing_card->post_status = $archive_status;
						wp_update_post( $existing_card );
					}

					foreach ( $body['data']['products'] as $key => $card ) {
						$issuer = '';
						$network = '';
						// Product type id 15 are credit cards.
						if ( ! empty( $card['product_type']['id'] ) && 15 === $card['product_type']['id'] ) {
							/**
							 * Lets check our taxonomies first
							 */
							// Issuer Terms with Advertiser Data
							if ( ! empty( $card['issuer_id']['name'] ) ) {
								$issuer = wpcom_vip_term_exists( $card['issuer_id']['name'], 'card-issuer' );

								if ( empty( $issuer ) && ! is_wp_error( $issuer ) ) {
									$issuer = wp_insert_term( "{$card['issuer_id']['name']}", 'card-issuer', array() );
									if ( ! empty( $issuer ) && ! is_wp_error( $issuer ) ) {
										add_term_meta( $issuer['term_id'], 'issuer-id', $card['issuer_id']['id'], true );
										add_term_meta( $issuer['term_id'], 'advertiser-id', $card['advertiser']['advertiser_id'], true );
										add_term_meta( $issuer['term_id'], 'advertiser-name', $card['advertiser']['name'], true );
									}
									// Get our term object so we can flush the cache.
									$issuer_object = get_term( absint( $issuer['term_id'] ), 'card-issuer' );
									wp_flush_term_exists( $issuer_object, $issuer_object->term_taxonomy_id, 'card-issuer', $issuer_object );
								}
							}
							// Network Terms
							if ( ! empty( $card['card_data']['network'] ) ) {
								$network = wpcom_vip_term_exists( $card['card_data']['network'], 'card-network' );
								if ( empty( $network ) && ! is_wp_error( $network ) ) {
									$network = wp_insert_term( "{$card['card_data']['network']}", 'card-network', array() );
								}
								// Get our term object so we can flush the cache.
								$network_object = get_term( absint( $network['term_id'] ), 'card-network' );
								wp_flush_term_exists( $network_object, $network_object->term_taxonomy_id, 'card-network', $network_object );
							}

							$update_post = true;
							$post_id = 0;
							$current_card = get_posts( array( 'post_type' => 'feed-card', 'meta_key' => 'card-id', 'meta_value' => $card['id'], 'post_status' => $archive_status, 'posts_per_page' => 1 ) );

							if ( ! empty( $current_card ) ) {
								$current_card = reset( $current_card );
								$post_id = $current_card->ID;
								if ( get_post_meta( $post_id, 'last-updated', true ) === $card['last_updated'] ) {
									// Feed hasn't changes so don't do anything to existing post.
									$update_post = false;
								}
							}

							// We need to ensure the post status is not archiveless so we always run the update.
							$post_id = wp_insert_post( array(
								'ID' => $post_id,
								'post_type' => 'feed-card',
								'post_title' => sprintf(
									'%1$s (%2$s)',
									$card['name'],
									$card['id']
								),
								'post_content' => $card['bullets'],
								'post_status' => 'publish',
							), false );

							if ( ( ! empty( $post_id ) && $update_post ) || $full_sync ) {
								// Get our feed data mapping
								$data = self::$instance->get_card_feed_data_mapping( $card );
								foreach ( $data as $meta_key => $meta_value ) {
									update_post_meta( $post_id, $meta_key, $meta_value );
								}

								// Set our taxonomies
								if ( ! empty( $issuer ) && ! is_wp_error( $issuer ) ) {
									wp_set_object_terms( $post_id, absint( $issuer['term_id'] ), 'card-issuer', false );
								}

								if ( ! empty( $network ) && ! is_wp_error( $network ) ) {
									wp_set_object_terms( $post_id, absint( $network['term_id'] ), 'card-network', false );
								}

								// Process our featured image.
								$image = trailingslashit( self::$image_url ) . $card['image'];

								$current_card_image = get_posts( array( 'post_type' => 'attachment', 'meta_key' => 'original_url', 'meta_value' => $image, 'posts_per_page' => 1 ) );

								// If there isn't an image in the media library with the url then upload it and set as featured.
								if ( empty( $current_card_image ) ) {
									add_action( 'add_attachment', array( self::$instance, 'set_sideloaded_image_as_featured' ) );
									media_sideload_image( $image, $post_id, $card['name'] );
									remove_action( 'add_attachment', array( self::$instance, 'set_sideloaded_image_as_featured' ) );
									$attachment_id = get_post_thumbnail_id( $post_id );
									update_post_meta( $attachment_id, 'original_url', $image );
								} else {
									$current_card_image = reset( $current_card_image );
									update_post_meta( $post_id, '_thumbnail_id', $current_card_image->ID );
								}
							}
						}
					}
					$message = date( 'Y-m-d H:i:s' );
				} else {
					$message = sprintf( __( 'Sync Error. Feed data is empty: %s', 'tmsc' ), date( 'Y-m-d H:i:s' ) );
				}
			} else {
				$message = sprintf( __( 'Sync Error. Error Code %1$s: %2$s', 'tmsc' ), $code, date( 'Y-m-d H:i:s' ) );
			}
		} else {
			$message = sprintf( __( 'Sync Error. Remote get failed: %s', 'tmsc' ), date( 'Y-m-d H:i:s' ) );
		}
		// Set sync status and clear our message cache.
		update_option( 'tmsc-last-sync-date', $message, false );
		wp_cache_delete( 'tmsc-last-sync-date', 'options' );
	}

	/**
	 * Hook into adding an attachment as a featured image when we sideload our images
	 * @param int. $att_id. Attachment id.
	 */
	public function set_sideloaded_image_as_featured( $att_id ) {
		// The post this was sideloaded into is the attachments parent.
		$attachment = get_post( $att_id );
		update_post_meta( $attachment->post_parent, '_thumbnail_id', $att_id );
	}

	/**
	 * Set the incoming card feed data to match our FM data architecture.
	 *
	 * @param array. $card. JSON card data in array form.
	 * @return array. $mapped_card. An array of post meta keys with data to be serialized.
	 */
	public function get_card_feed_data_mapping( $card ) {
		// Keys are FM post meta keys.
		// $card feed data has fixed keys so no need to empty check. Makes things more readable.
		$mapped_card = array(
			'card-id' => $card['id'],
			'card-sku' => $card['sku'],
			'card-link' => $card['link_url'],
			'purchases' => array(
				'intro' => array(
					'display' => $card['card_data']['purchases']['intro_apr']['display'],
					'rate' => $card['card_data']['purchases']['intro_apr']['value'],
					'period' => $card['card_data']['purchases']['intro_apr']['period']['value'],
					'period-end-date' => $card['card_data']['purchases']['intro_apr']['period']['end_date'],
				),
				'regular' => array(
					'display' => $card['card_data']['purchases']['regular_apr']['display'],
					'rate' => $card['card_data']['purchases']['regular_apr']['value'],
					'type' => $card['card_data']['purchases']['regular_apr']['type'],
					'min' => $card['card_data']['purchases']['regular_apr']['min'],
					'max' => $card['card_data']['purchases']['regular_apr']['max'],
				),
				'penalty' => $card['card_data']['purchases']['penalty_apr']['display'],
			),
			'balance-transfer' => array(
				'intro' => array(
					'display' => $card['card_data']['balance_transfers']['intro_apr']['display'],
					'rate' => $card['card_data']['balance_transfers']['intro_apr']['value'],
					'period' => $card['card_data']['balance_transfers']['intro_apr']['period']['value'],
					'period-end-date' => $card['card_data']['balance_transfers']['intro_apr']['period']['end_date'],
					'fee' => $card['card_data']['fees']['intro_balance_transfer']['display'],
				),
				'regular' => array(
					'value' => ( empty( $card['card_data']['balance_transfers']['regular_apr']['value'] ) ) ? 'false' : 'true',
					'display' => $card['card_data']['balance_transfers']['regular_apr']['display'],
					'fee' => $card['card_data']['fees']['balance_transfer']['display'],
				),
			),
			'fees' => array(
				'intro' => array(
					'value' => $card['card_data']['fees']['intro_annual']['value'],
				),
				'annual' => array(
					'display' => $card['card_data']['fees']['annual']['display'],
					'value' => $card['card_data']['fees']['annual']['value'],
				),
				'balance-transfer' => array(
					'display' => $card['card_data']['fees']['balance_transfer']['display'],
				),
				'intro-balance-transfer' => array(
					'display' => $card['card_data']['fees']['intro_balance_transfer']['display'],
				),
				'cash-advance' => array(
					'display' => $card['card_data']['fees']['cash_advance']['display'],
					'value' => $card['card_data']['fees']['cash_advance']['value'],
				),
				'foreign-transaction' => array(
					'display' => $card['card_data']['fees']['foreign_transaction']['display'],
					'value' => $card['card_data']['fees']['foreign_transaction']['value'],
				),
			),
			'rewards' => array(
				'long-description' => $card['card_data']['reward_descriptions']['rewards_description_long'],
				'short-description' => $card['card_data']['reward_descriptions']['rewards_description_short'],
				'premium-long-description' => $card['card_data']['reward_descriptions']['premium_description_long'],
				'premium-short-description' => $card['card_data']['reward_descriptions']['premium_description_short'],
			),
			'credit-needed' => $card['card_data']['credit_needed']['value'],
			'comission' => $card['commission'],
			'phone-number' => $card['apply_by_phone_number'],
			'last-updated' => $card['last_updated'],
		);

		// Pull key_stat from the first li in the post_content
		if ( ! empty( $card['bullets'] ) ) {
			$bullets = new DOMDocument();

			// Ignore errors for this situation
			libxml_use_internal_errors( true );

			// If we succesfully loaded the HTML
			if ( $bullets->loadHTML( $card['bullets'] ) ) {

				// Try to do the rest
				$li_list = $bullets->getElementsByTagName( 'li' );
				if ( ! empty( $li_list[0]->nodeValue ) ) {
					$mapped_card['key-stat'] = $li_list[0]->nodeValue;
				}
			}
		}

		return $mapped_card;
	}
}

function tmsc_object_sync() {
	return TMSC_Object_Sync::instance();
}
tmsc_object_sync();
