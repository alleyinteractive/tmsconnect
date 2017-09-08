<?php
/**
 * Create our own nav sysyem for child posts and custom landing pages.
 *
 * @package  TNSConnect
 */
class TMSC_Custom_Landing_Page_Types {
	private static $instance;

	/**
	 * The custom landing page types.
	 */
	public $types;

	/**
	 * The custom landing page post_type => taxonomy relationship.
	 */
	public $linked_types;

	/**
	 * List of custom lp taxonomy slugs.
	 */
	public $taxonomies = array();

	/**
	 * Current custom landing page taxonomy as passed by a query var.
	 */
	public $current_taxonomy = '';

	/**
	 * Current custom landing page post type as passed by a query var.
	 */
	public $current_post_type = '';

	/**
	 * Current custom landing page post type as passed by a query var.
	 */
	public $current_landing_type = '';

	/**
	 * The current term passed to a subpage.
	 */
	public $current_term = null;

	/**
	 * The current post.
	 */
	public $current_post = null;

	/**
	 * Store the current menu parent_file.
	 */
	public $parent_file = '';

	/**
	 * Store the current submenu_file.
	 */
	public $submenu_file = '';

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}

	public function __clone() {
		wp_die( "Please don't __clone " . __CLASS__ );
	}

	public function __wakeup() {
		wp_die( "Please don't __wakeup " . __CLASS__ );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TMSC_Custom_Landing_Page_Types();
			self::$instance->setup();
		}
		return self::$instance;
	}

	/**
	 * Initialize our class
	 * @return void.
	 */
	public function setup() {
		$this->types = apply_filters( 'tmsc_define_custom_landing_pages', array(
			'exhbitions' => array(
				'labels' => array(
					'name' => __( 'Events', 'ai' ),
					'child_name' => __( 'Event Subpages', 'ai' ),
					'singular_name' => __( 'Event', 'ai' ),
					'add_new' => __( 'Add New Event', 'ai' ),
					'add_new_item' => __( 'Add New Event', 'ai' ),
					'add_new_child' => __( 'Add New Event Subpage', 'ai' ),
					'edit_item' => __( 'Edit Event', 'ai' ),
					'edit_child' => __( 'Edit Event Subpage', 'ai' ),
					'new_item' => __( 'New Event', 'ai' ),
					'view_item' => __( 'View Event', 'ai' ),
					'search_items' => __( 'Search Events', 'ai' ),
					'not_found' => __( 'No Events found', 'ai' ),
					'not_found_in_trash' => __( 'No Events found in Trash', 'ai' ),
					'parent_item_colon' => __( 'Parent Event:', 'ai' ),
					'menu_name' => __( 'Events', 'ai' ),
				),
				'menu_icon' => 'dashicons-calendar-alt',
				'menu_order' => 5,
			),
			'podcasts' => array(
				'labels' => array(
					'name' => __( 'Podcasts', 'ai' ),
					'child_name' => __( 'Podcast Episodes', 'ai' ),
					'singular_name' => __( 'Podcast', 'ai' ),
					'add_new' => __( 'Add New Podcast', 'ai' ),
					'add_new_item' => __( 'Add New Podcast', 'ai' ),
					'add_new_child' => __( 'Add New Podcast Episode', 'ai' ),
					'edit_item' => __( 'Edit Podcast', 'ai' ),
					'edit_child' => __( 'Edit Episode', 'ai' ),
					'new_item' => __( 'New Podcast', 'ai' ),
					'view_item' => __( 'View Podcast', 'ai' ),
					'search_items' => __( 'Search Podcasts', 'ai' ),
					'not_found' => __( 'No Podcasts found', 'ai' ),
					'not_found_in_trash' => __( 'No Podcasts found in Trash', 'ai' ),
					'parent_item_colon' => __( 'Parent Podcast:', 'ai' ),
					'menu_name' => __( 'Podcasts', 'ai' ),
				),
				'menu_icon' => 'dashicons-format-audio',
				'menu_order' => 7,
			),
		) );
		$this->taxonomies = array_keys( $this->types );

		$this->linked_types = apply_filters( 'tmsc_define_landing_page_linked_types', array(
			'exhibition' => 'exhibitions',
			'podcast' => 'podcasts',
		) );

		// Setup hooks.
		$this->set_hooks();
	}

	/**
	 * Setup any hooks or filters required for linking.
	 * @return void.
	 */
	public function set_hooks() {
		// Add landingpages and taxonomies to the linked type config.
		add_filter( 'admin_init', array( $this, 'set_current_landing_page_meta' ) );

		// Add landingpages and taxonomies to the linked type config.
		add_filter( 'tmsc_linked_taxonomy_posts', array( $this, 'add_linked_types' ) );


		// Do this as early as possible in the admin menu header
		add_action( 'admin_enqueue_scripts', array( $this, 'add_custom_landing_page_types_menus' ) );

		// If we are using a parent terms to link, the default is to set the term based on a drop down select.
		//Set the metabox and the conditions on using the parent term on save.
		add_filter( 'tmsc_landing_page_child_meta_boxes', array( $this, 'add_custom_types' ) );
		add_filter( 'tmsc_linked_parent_terms', array( $this, 'set_parent_terms' ), 20, 4 );

		// Set up child pages to have a standard post editor and author etc.
		add_action( 'init', array( $this, 'add_post_type_supports' ) );

		// Set it up so our custom landing pages display the correct posts in the admin area.
		if ( is_admin() && ! wp_doing_ajax() ) {
			add_filter( 'admin_url', array( $this, 'add_new_custom_landing_page_url' ), 20, 3 );
			// Set our status filter urls properly
			foreach ( array_keys( $this->linked_types ) as $post_type ) {
				add_filter( "views_edit-{$post_type}", array( $this, 'set_status_filter_urls' ) );
			}
			// Make sure counst on filter links are correct.
			add_filter( 'wp_count_posts', array( $this, 'get_custom_landing_page_post_counts'), 20, 3 );
			// Add in a link to subpages on parent page.
			add_filter( 'post_row_actions', array( $this, 'add_view_subpage_link_action' ), 20, 2 );
			// Add in link to view/parent subpage when editing single items
			add_action( 'edit_form_after_title', array( $this, 'add_after_permalink' ) );

			// By default we kill the landing page info visibility on child pages. If you want child pages to have visibility, use this hook.
			if ( apply_filters( 'tmsc_hide_child_custom_landing_pages', true ) ) {
				add_filter( 'hidden_meta_boxes', array( $this, 'hide_landing_page_info_metabox'), 20, 3 );
			}

			// Make sure we redirect with the correct query vars.
			apply_filters( 'redirect_post_location', array( $this, 'set_redirect_query_args' ), 20, 2 );

			add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
			add_filter( 'parse_query', array( $this, 'parse_query' ) );
			add_action( 'post_submitbox_misc_actions', array( $this, 'add_landing_type_meta_box_info' ) );

			// We use this filter hook to set the labels appropriately after menu is built so labels of current page are correct.
			add_filter( 'add_menu_classes', array( $this, 'set_current_labels' ) );
			add_action( 'load-edit.php', array( $this, 'set_admin_page_labels' ) );
			add_action( 'load-post-new.php', array( $this, 'set_admin_page_labels' ) );
			add_action( 'load-post.php', array( $this, 'set_admin_page_labels' ) );
		}
	}

	/**
	 * Set the meta data of the current admin area
	 */
	public function set_current_landing_page_meta( $post = null ) {
		// Try and grab the post if possible.
		if ( empty( $post ) ) {
			$post_id = ( ! empty( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) ? absint( $_GET['post'] ) : null;
			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
			}
		}

		$this->current_post = $post;

		if ( empty( $post ) ) {
			// In some cases like labels query vars are set at a later hook, so we fall back on the get variable if it not available.
			$this->current_taxonomy = get_query_var( 'taxonomy', ( ( ! empty( $_GET['taxonomy'] ) ) ? sanitize_text_field( $_GET['taxonomy'] ) : $this->current_taxonomy ) );
			$this->current_post_type = get_query_var( 'post_type', ( ( ! empty( $_GET['post_type'] ) ) ? sanitize_text_field( $_GET['post_type'] ) : $this->current_post_type ) );

			$this->current_landing_type = get_query_var( 'landing_type', ( ( ! empty( $_GET['landing_type'] ) ) ? sanitize_text_field( $_GET['landing_type'] ) : $this->current_landing_type ) );


			// Set the current term if it exists.
			if ( ! empty( $this->current_taxonomy ) ) {
				$current_term_id = get_query_var( $this->current_taxonomy, ( ( ! empty( $_GET[ $this->current_taxonomy ] ) ) ? absint( $_GET[ $this->current_taxonomy ] ) : null ) );
				if ( ! empty( $current_term_id ) ) {
					$this->current_term = get_term( $current_term_id, $this->current_taxonomy );
					$this->current_landing_type = 'single';
				} elseif ( empty( $this->current_landing_type ) ) {
					$this->current_landing_type = 'archive';
				}
			} else {
				// If the current taxonomy is still empty, make sure we aren't on a subpage.
				foreach ( $this->taxonomies as $custom_tax ) {
					$current_term_id = get_query_var( $custom_tax, ( ( ! empty( $_GET[ $custom_tax ] ) ) ? absint( $_GET[ $custom_tax ] ) : null ) );
					if ( ! empty( $current_term_id ) ) {
						$this->current_taxonomy = $custom_tax;
						$this->current_term = get_term( $current_term_id, $this->current_taxonomy );
						$this->current_landing_type = 'single';
						break;
					}
				}
			}
		} elseif ( $this->is_custom_landing_page_post( $post ) ) {
			$this->current_post_type = $post->post_type;
			$this->current_taxonomy = $this->get_current_taxonomy( $post );
			if ( $this->is_parent_landing_page( $post ) ) {
				$this->current_term = tmsc_get_linked_term( $post->ID );
				$this->current_landing_type = 'archive';
			} else {
				$this->current_term = tmsc_get_linked_term( $post->post_parent );
				$this->current_landing_type = 'single';
			}
		}
	}

	/**
	 * If we are on a child page, make sure it supports a standard metabox feature set.
	 */
	public function add_post_type_supports() {
		if ( $this->on_custom_landing_admin_page() && ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) {
			$custom_landing_page_supports = apply_filters( 'tmsc_child_custom_landing_page_supports', array( 'editor', 'author', 'revisions', 'comments' ) );
			add_post_type_support( $this->current_post_type, $custom_landing_page_supports );
		}
	}

	/**
	 * Add our linked types into our linked post type config.
	 * @param array $linked_types. array( 'post_type_slug' => 'taxonomy_slug' )
	 */
	public function add_linked_types( $linked_types = array() ) {
		return array_merge( $linked_types, $this->linked_types );
	}

	/**
	 * Set up our admin menus and labels
	 * @param string $hook_suffix.
	 * @return string.
	 */
	public function add_custom_landing_page_types_menus( $hook_suffix ) {
		$this->set_current_landing_page_meta();
		global $_wp_real_parent_file, $submenu_file, $submenu, $pagenow, $typenow, $taxnow, $parent_file;

		$typenow = ( empty( $typenow ) && ! empty( $this->current_post_type ) ) ? $this->current_post_type : $typenow;
		$taxnow = ( empty( $taxnow ) && ! empty( $this->current_taxonomy ) ) ? $this->current_taxonomy : $taxnow;

		// This will set parent file aliases for our menu urls so that the styles of the active menu work correctly.
		if ( ! empty( $typenow ) && $this->current_post_type === $typenow &&
			! empty( $taxnow ) && in_array( $taxnow, $this->taxonomies, true )
		) {
			// Make sure we are using proper parent menu.
			$current_menu_slug = add_query_arg( array( 'post_type' => $this->current_post_type, 'taxonomy' => $taxnow ), $pagenow );
			$current_edit_slug = add_query_arg( array( 'post_type' => $this->current_post_type, 'taxonomy' => $taxnow ), 'edit.php' );
			$current_new_slug = add_query_arg( array( 'post_type' => $this->current_post_type, 'taxonomy' => $taxnow ), 'post-new.php' );

			// Check our type query variable
			// We check the url param here becasue we need to match on the URL and the landing type can get set before we get here.
			if ( ! empty( $_GET['landing_type'] ) ) {
				$current_new_slug = add_query_arg( array( 'landing_type' => $this->current_landing_type  ), $current_new_slug );
				$current_menu_slug = add_query_arg( array( 'landing_type' => $this->current_landing_type  ), $current_menu_slug );
			}

			$_wp_real_parent_file["edit.php?post_type=${typenow}"] = $current_edit_slug;
			$_wp_real_parent_file["post-new.php?post_type=${typenow}"] = $current_new_slug;

			if ( ! empty( $this->current_post ) ) {
				$parent_file = $current_edit_slug;
				$submenu_file = $current_edit_slug;

			} else {
				// We are not on the original landing page so let's trick it so that we can properly set the submenu highlights.
				$submenu_file = $current_menu_slug;
			}
		}

		foreach ( $this->taxonomies as $taxonomy ) {

			$menu_order = ! empty( $this->types[ $taxonomy ]['menu_order'] ) ? absint( $this->types[ $taxonomy ]['menu_order'] ) : 5;

			// Our menu slugs and alias
			$edit_slug = add_query_arg( array( 'post_type' => $this->current_post_type, 'taxonomy' => $taxonomy ), 'edit.php' );
			$new_slug = add_query_arg( array( 'post_type' => $this->current_post_type, 'taxonomy' => $taxonomy ), 'post-new.php' );
			add_filter( "post_type_labels_{$this->current_post_type}", array( $this, 'set_menu_labels' ) );
				add_menu_page(
					$this->types[ $taxonomy ]['labels']['name'],
					$this->types[ $taxonomy ]['labels']['menu_name'],
					'edit_posts',
					$edit_slug,
					'',
					$this->types[ $taxonomy ]['menu_icon'],
					$menu_order
				);
				add_submenu_page(
					$edit_slug,
					$this->types[ $taxonomy ]['labels']['add_new'],
					$this->types[ $taxonomy ]['labels']['add_new'],
					'edit_posts',
					add_query_arg( array( 'landing_type' => 'archive' ), $new_slug )
				);
				$child_label = ( empty( $this->types[ $taxonomy ]['labels']['add_new_child'] ) ) ? $this->types[ $taxonomy ]['labels']['add_new'] : $this->types[ $taxonomy ]['labels']['add_new_child'];
				add_submenu_page(
					$edit_slug,
					$child_label,
					$child_label,
					'edit_posts',
					add_query_arg( array( 'landing_type' => 'single' ), $new_slug )
				);
			remove_filter( "post_type_labels_{$this->current_post_type}", array( $this, 'set_menu_labels' ) );
		}

	}

	/**
	 * Manage the dropdown to filter child posts.
	 */
	public function restrict_manage_posts() {
		global $typenow;
		if ( $this->current_post_type === $typenow && ! empty( $this->current_taxonomy ) ) {
			$label = $this->types[ $this->current_taxonomy ]['labels']['name'];
			$selected = ( ! empty( $this->current_term->term_id ) ) ? $this->current_term->term_id : 0;

			wp_dropdown_categories( array(
				'show_option_all' =>  __( sprintf( 'Show All %s', $label ), 'ai' ),
				'taxonomy' =>  $this->current_taxonomy,
				'name' =>  $this->current_taxonomy,
				'orderby' =>  'name',
				'hierarchical' =>  true,
				'depth' =>  1,
				'show_count' =>  false,
				'hide_empty' =>  false,
				'hide_if_empty' => true,
				'selected' => $selected,
			) );
		}
	}

	/**
	 * This is used to display the children of a post when the edit.php is passed with a filter action.
	 *
	 */
	public function parse_query( $query ) {
		$this->set_current_landing_page_meta();
		global $pagenow, $typenow;
		// AAM: class-ai-custom-landing-page-types.php
		// TODO: SET UP PARSE QUERY FILTERS FOR LIST TABLE TO USE CUSTOM QUERY VARS.
		if (
			'edit.php' === $pagenow &&
			$this->current_post_type === $typenow
		) {
			// Display all archives or singles in trash.
			if ( ! empty( $query->query['post_status'] ) && 'trash' === $query->query['post_status'] && ! empty( $this->current_taxonomy ) ) {

				$key = 'linked_taxonomy';
				if ( 'single' === $this->current_landing_type ) {
					$key = 'landing_page_type';
					$query->set( 'post_parent__not_in', array( 0 ) );
				} else {
					$query->set( 'post_parent', 0 );
				}
				$query->set( 'meta_query', array(
					array(
						'key' => $key,
						'value' => $this->current_taxonomy,
					),
				) );
				unset( $query->query_vars[ $this->current_taxonomy ] );
				unset( $query->query[ $this->current_taxonomy ] );
				$query->tax_query = null;
			} else {

				if ( ! empty( $this->current_taxonomy ) && in_array( $this->current_taxonomy, $this->taxonomies, true ) ) {
					if ( ! empty( $this->current_term ) ) {
						$parent = tmsc_get_linked_post( $this->current_term->term_id );
						$query->set( 'post_parent', $parent->ID );
						unset( $query->query_vars[ $this->current_taxonomy ] );
						unset( $query->query[ $this->current_taxonomy ] );
						$query->tax_query = null;
					} else {
						$query->set( 'meta_query', array(
							array(
								'key' => 'linked_taxonomy',
								'value' => $this->current_taxonomy,
							),
						) );
						$query->set( 'post_parent', 0 );
					}
				} else {

					$query->set( 'post_parent', 0 );
					$query->set( 'meta_query', array(
						'relation' => 'OR',
						array(
							'key' => 'linked_taxonomy',
							'value' => $this->taxonomies,
							'compare' => 'NOT IN',
						),
						array(
							'key' => 'linked_taxonomy',
							'compare' => 'NOT EXISTS',
						),
					) );
				}
			}
		}

		return $query;
	}

	/**
	 * Make sure we are redirecteds with the correct landing page query args
	 */
	public function set_redirect_query_args( $location, $post_id ) {
		$post = get_post( $post_id );
		if ( $this->is_custom_landing_page_post( $post ) ) {
			$meta = get_post_meta( $post_id );
			$location = add_query_arg( 'taxonomy', $this->current_taxonomy, $location );
			if ( ! empty( $post->post_parent ) ) {
				$location = add_query_arg( 'landing_type', 'single', $location );
			} else {
				$location = add_query_arg( 'landing_type', 'archive', $location );
			}
		}

		return $location;
	}

	/**
	 * Make sure when we are on the top level Custom Landing Page list, that the new link passes the taxonomy query var.
	 */
	public function add_new_custom_landing_page_url( $url, $path, $blog_id ) {
		global $pagenow, $typenow, $taxnow;
		if (
			( 'edit.php' === $pagenow || 'post.php' === $pagenow ) &&
			$this->current_post_type === $typenow &&
			! empty( $this->current_taxonomy ) &&
			"post-new.php?post_type={$this->current_post_type}" === $path
		) {
			$this->set_current_landing_page_meta();
			$url = add_query_arg( 'taxonomy', $this->current_taxonomy, $url );
			if ( ! empty( $this->current_landing_type  ) ) {
				$url = add_query_arg( array( 'landing_type' => $this->current_landing_type ), $url );
			}

			if ( ! empty( $this->current_term->term_id ) ) {
				$url = add_query_arg( array( $this->current_taxonomy => $this->current_term->term_id ), $url );
			}
		}
		return $url;
	}

	/**
	 * Set the urls for the status filter links on the edit pages.
	 * @param array $views. Associative array of status keys with corresponding links.
	 * @return array;
	 */
	public function set_status_filter_urls( $views ) {
		if ( $this->on_custom_landing_admin_page() ) {
			foreach ( $views as $status => $link ) {
				preg_match( '/<a href="(.*)">(.+)<span class="count">\s*(\(\d+\))\s*<\/span>\s*<\/a>/', $link, $matches );

				$new_url = add_query_arg( array( 'post_type' => $this->current_post_type, 'taxonomy' => $this->current_taxonomy ), html_entity_decode( $matches[1] ) );
				if ( 'all' === $status ) {
					$html = str_replace( 'All ', esc_html( $this->types[ $this->current_taxonomy ]['labels']['name'] ) . ' ', $matches[0] );
				} else {
					$new_url = add_query_arg( array( 'landing_type' => $this->current_landing_type ), html_entity_decode( $new_url ) );
					$html = $matches[0];
				}

				$views[ $status ] = str_replace( $matches[1], esc_url( $new_url ), $html );
			}
		}
		return $views;
	}

	/**
	 * Add in an action to see subpages on parent page.
	 * @param array $actions. array( 'class' => 'HTML' ).
	 * @param object $post. WP Post Object.
	 * @return array.
	 */
	public function add_view_subpage_link_action( $actions, $post ) {
		if ( $this->on_custom_landing_admin_page( $post ) && $this->is_parent_landing_page( $post ) ) {
			$term = tmsc_get_linked_term( $post->ID );

			$link = '<a href="' . esc_url( add_query_arg( array( 'post_type' => $this->current_post_type, $this->current_taxonomy => $term->term_id ), 'edit.php' ) ) . '" aria-label="' . esc_html__( 'View ', 'ai' ) . esc_attr( $post->post_title ) . ' ' . esc_html( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ) . '">' . esc_html( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ) . '</a>';
			$actions = array_merge( array( 'subpages' => $link ), $actions );

		}
		return $actions;
	}

	/**
	 * Add in an link to view children/parent when editing.
	 * @param object $post. WP Post Object.
	 * @return void.
	 */
	public function add_after_permalink( $post ) {
		global $pagenow;
		if ( 'post.php' === $pagenow && $this->on_custom_landing_admin_page( $post ) ) { ?>
			<div class="inside">
				<div id="edit-slug-box" class="hide-if-no-js">
					<?php if ( $this->is_parent_landing_page( $post ) ) {
						$term = tmsc_get_linked_term( $post->ID ); ?>
						<strong><?php echo esc_html( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ); ?>:</strong>
						<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => $this->current_post_type, $this->current_taxonomy => $term->term_id ), 'edit.php' ) ); ?>"><?php esc_html_e( 'View', 'ai' ); ?></a>
					<?php } else { ?>
						<strong><?php echo esc_html_e( 'Parent ', 'ai'); echo esc_html( $this->types[ $this->current_taxonomy ]['labels']['singular_name'] ); ?>:</strong>
						<a href="<?php echo esc_url( add_query_arg( array( 'post' => $post->post_parent, 'action' => 'edit' ), 'post.php' ) );?>"><?php echo esc_html( get_the_title( $post->post_parent ) ); ?></a>
					<?php } ?>
				</div>
			</div>
		<?php }
	}

	/**
	 * Make sure the counts are the correct
	 * @param array $counts. Arry of type counts.
	 * @param string $type. Post type.
	 * @param string $perm. User permissions used for cache key.
	 */
	public function get_custom_landing_page_post_counts( $counts, $type, $perm ) {
		if ( 'readable' === $perm && $this->current_post_type === $type ) {
			if ( $this->on_custom_landing_admin_page() ) {
				foreach ( $counts as $status => $count ) {
					// TODO: SET COUNT VALUES. THIS WILL NEED CACHING AND TO BE PRE PRIMED WITH A SAVE POST HOOK.
				}
			}
		}

		return $counts;
	}

	/**
	 * We use this to set the menu labels that we add.
	 *
	 */
	public function set_menu_labels( $labels ) {
		if ( $this->on_custom_landing_admin_page() ) {
			$labels = $this->types[ $this->current_taxonomy ]['labels'];
		}
		return $labels;
	}

	// We do this here to edit the labels on the edit page of our custon types.
	// Set after the menu has been built.
	public function set_current_labels( $menu ) {
		if ( $this->on_custom_landing_admin_page() ) {
			global $wp_post_types;

			$wp_post_types[ $this->current_post_type ]->labels = (object) array_merge( (array) $wp_post_types[ $this->current_post_type ]->labels,(array) $this->types[ $this->current_taxonomy ]['labels'] );
			if ( ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) {
				$wp_post_types[ $this->current_post_type ]->labels->add_new_item = ( ! empty( $wp_post_types[ $this->current_post_type ]->labels->add_new_child ) ) ? $wp_post_types[ $this->current_post_type ]->labels->add_new_child : $wp_post_types[ $this->current_post_type ]->labels->add_new_item;
				$wp_post_types[ $this->current_post_type ]->labels->edit_item = ( ! empty( $wp_post_types[ $this->current_post_type ]->labels->edit_child ) ) ? $wp_post_types[ $this->current_post_type ]->labels->edit_child : $wp_post_types[ $this->current_post_type ]->labels->edit_item;
			}
		}
		return $menu;
	}

	/**
	 * For specific admin post edit pages, set the label appropriately.
	 *
	 */
	public function set_admin_page_labels() {
		global $wp_post_types;
		if ( $this->on_custom_landing_admin_page() ) {
			$wp_post_types[ $this->current_post_type ]->labels = (object) array_merge( (array) $wp_post_types[ $this->current_post_type ]->labels, $this->types[ $this->current_taxonomy ]['labels'] );
			$wp_post_types[ $this->current_post_type ]->label = $this->types[ $this->current_taxonomy ]['labels']['name'];

			// If we haven't come in with a associated taxonony, use generic child labels.
			if ( 'single' === $this->current_landing_type || ! empty( $this->current_term->term_id ) ) {
				$wp_post_types[ $this->current_post_type ]->labels->add_new = $this->types[ $this->current_taxonomy ]['labels']['add_new_child'];
				$wp_post_types[ $this->current_post_type ]->labels->add_new_item = $this->types[ $this->current_taxonomy ]['labels']['add_new_child'];
				$wp_post_types[ $this->current_post_type ]->labels->edt_item = $this->types[ $this->current_taxonomy ]['labels']['edit_child'];
				$wp_post_types[ $this->current_post_type ]->label = $this->types[ $this->current_taxonomy ]['labels']['child_name'];

				// Override the parent labels with the child labels.
				if ( ! empty( $this->current_term->term_id ) ) {
					// Edit the title to use the term name for child pages
					$edit_title = str_replace( $this->types[ $this->current_taxonomy ]['labels']['singular_name'], $this->current_term->name, $this->types[ $this->current_taxonomy ]['labels']['child_name'] );

					$wp_post_types[ $this->current_post_type ]->labels->name = $edit_title;
				}
			}
		}
	}


	/**
	 * Add in landing page types to the select box and alter the FM data.
	 * @param array $meta_boxes. Array of FM Field Objects.
	 * @return array.
	 */
	public function add_custom_types( $meta_boxes ) {
		if ( $this->on_custom_landing_admin_page() ) {

			$type_label = $this->types[ $this->current_taxonomy ]['labels']['singular_name'];

			// Parent items use the taxonomy type
			$fm_fields = array(
				'type' => 'taxonomy',
				'taxonomy' => $this->current_taxonomy,
				'term_id' => ( ! empty( $this->current_term ) ) ? $this->current_term->term_id : null,
				'post_type' => null,
			);

			if ( ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) {
				$type_label = ( ! empty( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ) ) ? $this->types[ $this->current_taxonomy ]['labels']['child_name'] : $type_label . __( 'Child', 'ai' );
				$fm_fields['type'] = $this->current_taxonomy;
			}

			$fm_fields['term_label'] = $type_label;


			foreach( $fm_fields as $index => $field ) {
				$meta_boxes[ $index ] = new Fieldmanager_Hidden( $type_label , array(
					'index' => "landing_page_{$index}",
					'save_empty' => true,
					'default_value' => $field,
				) );
			}
		}
		return $meta_boxes;
	}

	/**
	 * Hide the landing page info metabox on child posts. This can be disabled using the `tmsc_hide_child_custom_landing_pages` filter hook.
	 * @param array $hidden. Array of element ids to be hidden.
	 * @param object $screen. Screen to be hidden on.
	 * @param boolean $use_defaults.
	 * @return array.
	 */
	public function hide_landing_page_info_metabox( $hidden, $screen, $use_defaults ) {
		$custom_hidden = array();
		if ( $this->on_custom_landing_admin_page() && ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) {

			$custom_hidden = apply_filters( 'tmsc_hidden_child_meta_boxes', $custom_hidden, $this->current_landing_type, $this->current_taxonomy, $this->current_post_type, $this->current_term );
		}
		return array_merge( $hidden, $custom_hidden );
	}

	/**
	 * Add in an icon to display the current landing page type.
	 */
	public function add_landing_type_meta_box_info( $post ){

		// If the post has been created, we can grab the saved info.
		if ( $this->on_custom_landing_admin_page( $post ) && ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) {
			$name = $this->types[ $this->current_taxonomy ]['labels']['singular_name'];
			$terms = get_terms( array( 'taxonomy' => $this->current_taxonomy, 'hide_empty' => false ) ); ?>
			<div class="misc-pub-section misc-pub-lptype">
				<span id="custom-landing-page-pub-section">
					<span class="dashicons <?php echo esc_attr( $this->types[ $this->current_taxonomy ]['menu_icon']); ?>"></span>
				</span>
				&nbsp;<?php echo esc_html( $name ); ?>: <b><span class="ai-target-text"><?php echo ( ! empty( $this->current_term ) ) ? esc_html( $this->current_term->name ) : ''; ?></span></b>
				<a href="#custom-landing-page-type" class="ai-edit-button edit-custom-landing-page-type hide-if-no-js" role="button">
					<span aria-hidden="true"><?php _e( 'Edit', 'ai' ); ?></span>
					<span class="screen-reader-text">
						<?php _e( 'Edit ', 'ai' );
						echo esc_html( $name ); ?>
					</span>
				</a>
				<div id="custom-landing-page-type-select" class="ai-edit-button-target hide-if-js">
					<label for="custom-landing-page-type" class="screen-reader-text"><?php _e( 'Set ', 'ai' );
						echo esc_html( $name ); ?></label>
					<select name="post_parent" id="post_parent">
						<?php foreach ( $terms as $term ) {
							$parent = tmsc_get_linked_post( $term->term_id ); ?>
							<option value="<?php echo esc_attr( $parent->ID ); ?>"<?php echo ( $post->post_parent === $parent->ID ) ? ' selected="selected"' : ''; ?>><?php echo esc_html( $term->name ); ?></option>
						<?php } ?>
					</select>
					<a href="#custom-landing-page-type" class="ai-save-button save-custom-landing-page-type hide-if-no-js button">OK</a>
					<a href="#custom-landing-page-type" class="ai-cancel-button cancel-custom-landing-page-type hide-if-no-js button-cancel">Cancel</a>

				</div>
			</div>
			<?php
		}
	}

	/**
	 * Instantiate any meta values and check if we are on the a custom landing page.
	 * @param object $post. WP Post object (optional).
	 * @return boolean.
	 */
	public function on_custom_landing_admin_page( $post = null ) {
		$this->set_current_landing_page_meta( $post );

		if ( ! empty( $this->current_post_type ) && in_array( $this->current_post_type, array_keys( $this->linked_types ), true ) &&
			! empty( $this->current_taxonomy ) && in_array( $this->current_taxonomy, $this->taxonomies, true )
		) {
			return true;
		}
		return false;
	}

	/**
	 * Are we on a landing page post that is custom.
	 */
	public function is_custom_landing_page_post( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( $this->current_post_type !== $post->post_type ) {
			return false;
		}

		$landing_page_type = $this->get_current_taxonomy( $post );
		if ( in_array( $landing_page_type, $this->taxonomies, true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if post is a parent or child custom landing page.
	 */
	public function is_parent_landing_page( $post ) {
		if ( 0 === (int) $post->post_parent ) {
			return true;
		}
		return false;
	}

	/**
	 * Return the custom landing page taxonomy slugs.
	 */
	public function get_custom_landing_page_types() {
		return $this->taxonomies;
	}

	/**
	 * Make an attempt to get the current taxonomy of an custom landing admin page.
	 */
	public function get_current_taxonomy( $post = null ) {
		$taxonomy = $this->current_taxonomy;
		if ( empty( $post ) ) {
			$post = $this->current_post;
		} else {
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );
			}
		}
		if ( ! empty( $post ) ) {
			if ( $this->is_parent_landing_page( $post ) ) {
				$key = 'linked_taxonomy';
			} else {
				$key = 'landing_page_taxonomy';
			}

			$taxonomy = get_post_meta( $post->ID, $key, true );
		}
		return $taxonomy;
	}
}

// Initial call to setup instance
add_action( 'after_setup_theme', array( 'TMSC_Custom_Landing_Page_Types', 'instance' ), 10 );
