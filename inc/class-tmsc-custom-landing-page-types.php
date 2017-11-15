<?php
/**
 * Create our own nav sysyem for child posts and custom landing pages.
 *
 * @package  TNSConnect
 */
class TMSC_Custom_Landing_Page_Types {
	use \TMSC\Singleton;

	/**
	 * The custom landing page types.
	 */
	public $types;

	/**
	 * The custom landing page post_type => taxonomy relationship.
	 */
	public $linked_types = array();

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
	 * Current views for a custom landing page.
	 */
	public $current_views = array();

	/**
	 * Store the current menu parent_file.
	 */
	public $parent_file = '';

	/**
	 * Store the current submenu_file.
	 */
	public $submenu_file = '';

	/**
	 * Initialize our class
	 * @return void.
	 */
	public function setup() {
		$this->types = apply_filters( 'tmsc_define_custom_landing_pages', array(
			'exhibitions' => array(
				'labels' => array(
					'name' => __( 'Exhibitions', 'tmsc' ),
					'child_name' => __( 'Exhibition Subpages', 'tmsc' ),
					'singular_name' => __( 'Exhibition', 'tmsc' ),
					'add_new' => __( 'Add New Exhibition', 'tmsc' ),
					'add_new_item' => __( 'Add New Exhibition', 'tmsc' ),
					'add_new_child' => __( 'Add New Exhibition Subpage', 'tmsc' ),
					'edit_item' => __( 'Edit Exhibition', 'tmsc' ),
					'edit_child' => __( 'Edit Exhibition Subpage', 'tmsc' ),
					'new_item' => __( 'New Exhibition', 'tmsc' ),
					'view_item' => __( 'View Exhibition', 'tmsc' ),
					'search_items' => __( 'Search Exhibitions', 'tmsc' ),
					'not_found' => __( 'No Exhibitions found', 'tmsc' ),
					'not_found_in_trash' => __( 'No Exhibitions found in Trash', 'tmsc' ),
					'parent_item_colon' => __( 'Parent Exhibition:', 'tmsc' ),
					'menu_name' => __( 'Exhibitions', 'tmsc' ),
				),
				'menu_icon' => 'dashicons-format-gallery',
				'menu_order' => 20,
				'post_type' => 'exhibition',
			),
		) );
		$this->taxonomies = array_keys( $this->types );

		foreach ( $this->types as $taxonomy => $config ) {
			if ( ! empty( $config['post_type'] ) ) {
				if ( empty( $this->linked_types[ $config['post_type'] ] ) ) {
					$this->linked_types[ $config['post_type'] ] = $taxonomy;
				}
			}
		}
		$this->linked_types = apply_filters( 'tmsc_define_landing_page_linked_types', $this->linked_types );

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
		add_action( 'admin_enqueue_scripts', array( $this, 'highlight_custom_landing_page_types_menus' ) );
		add_action( 'admin_menu', array( $this, 'add_custom_landing_page_types_menus' ) );

		// If we are using a parent terms to link, the default is to set the term based on a drop down select.
		//Set the metabox and the conditions on using the parent term on save.
		add_filter( 'tmsc_linked_parent_terms', array( $this, 'set_parent_terms' ), 20, 4 );

		// Set up child pages to have a standard post editor and author etc.
		add_action( 'init', array( $this, 'add_post_type_supports' ) );

		add_action( 'init', array( $this, 'set_subpage_rewrites' ) );

		// Set our status filter urls properly
		foreach ( $this->linked_types as $post_type => $taxonomies ) {
			add_filter( "views_edit-{$post_type}", array( $this, 'set_status_filter_urls' ), 50 );
		}

		// Set it up so our custom landing pages display the correct posts in the admin area.
		if ( is_admin() && ! wp_doing_ajax() ) {

			// Set our post types as heirarchical for the parent pages.
			add_action( 'admin_init', array( $this, 'set_as_hierarchical' ) );

			add_filter( 'admin_url', array( $this, 'add_new_custom_landing_page_url' ), 20, 3 );

			// Add in a link to subpages on parent page.
			add_filter( 'post_row_actions', array( $this, 'add_view_subpage_link_action' ), 20, 2 );
			// Add in link to view/parent subpage when editing single items
			add_action( 'edit_form_after_title', array( $this, 'add_after_permalink' ) );

			// Make sure we redirect with the correct query vars.
			apply_filters( 'redirect_post_location', array( $this, 'set_redirect_query_args' ), 20, 2 );

			add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
			add_filter( 'parse_query', array( $this, 'parse_query' ), 50 );
			add_action( 'post_submitbox_misc_actions', array( $this, 'add_landing_type_meta_box_info' ) );

			// We use this filter hook to set the labels appropriately after menu is built so labels of current page are correct.
			add_filter( 'add_menu_classes', array( $this, 'set_current_labels' ) );
			add_action( 'load-edit.php', array( $this, 'set_admin_page_labels' ) );
			add_action( 'load-post-new.php', array( $this, 'set_admin_page_labels' ) );
			add_action( 'load-post.php', array( $this, 'set_admin_page_labels' ) );

			add_action( 'restrict_manage_posts', array( $this, 'add_taxonomy_search_field' ) );
		} // End if().
	}

	/**
	 * Set the meta data of the current admin area
	 */
	public function set_current_landing_page_meta( $post = null ) {
		// Try and grab the post if possible.
		if ( empty( $post ) ) {
			$post_id = ( ! empty( $_GET['post'] ) ) ? absint( wp_unslash( $_GET['post'] ) ) : null;
			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
			}
		}

		$this->current_post = $post;

		if ( empty( $post ) ) {
			// In some cases like labels query vars are set at a later hook, so we fall back on the get variable if it not available.
			$this->current_taxonomy = get_query_var( 'taxonomy', ( ( ! empty( $_GET['taxonomy'] ) ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : $this->current_taxonomy ) );
			$this->current_post_type = get_query_var( 'post_type', ( ( ! empty( $_GET['post_type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : $this->current_post_type ) );

			$this->current_landing_type = get_query_var( 'landing_type', ( ( ! empty( $_GET['landing_type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['landing_type'] ) ) : $this->current_landing_type ) );

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
		} // End if().
	}

	/**
	 * If we are on a page that shows both parent and child, then temporarily set as hierarchical.
	 *
	 */
	public function set_as_hierarchical() {
		if ( $this->on_custom_landing_admin_page() && ( empty( $_GET['landing_type'] ) || 'single' === $_GET['landing_type'] || ! empty( $_GET['post_status'] ) || ! empty( $_GET[ $this->current_taxonomy ] ) ) ) {
			global $wp_post_types;
			$wp_post_types[ $this->current_post_type ]->hierarchical = true;
		}
	}

	/**
	 * If we are on a child page, make sure it supports a standard metabox feature set.
	 */
	public function add_post_type_supports() {
		$supports = array( 'editor', 'author', 'revisions', 'comments' );
		$custom_landing_page_supports = apply_filters( 'tmsc_child_custom_landing_page_supports', $supports, $this->current_landing_type, $this->current_post_type, $this->current_taxonomy );
		if ( ! empty( $custom_landing_page_supports ) ) {
			add_post_type_support( $this->current_post_type, $custom_landing_page_supports );
		}
	}

	/**
	 * Add our linked types into our linked post type config.
	 * @param array $linked_types. array( 'post_type_slug' => 'taxonomy_slug', 'post_type_slug' => array( 'taxonomy_slug', 'taxonomy_slug' ) ).
	 */
	public function add_linked_types( $linked_types = array() ) {
		return array_merge( $linked_types, $this->linked_types );
	}

	/**
	 * Make sure we are highlighting the correct item.
	 * @param string $hook_suffix.
	 * @return void.
	 */
	public function highlight_custom_landing_page_types_menus( $hook_suffix ) {
		$this->set_current_landing_page_meta();
		global $_wp_real_parent_file, $submenu_file, $menu, $submenu, $pagenow, $typenow, $taxnow, $parent_file;

		$typenow = ( empty( $typenow ) && ! empty( $this->current_post_type ) ) ? $this->current_post_type : $typenow;
		$taxnow = ( empty( $taxnow ) && ! empty( $this->current_taxonomy ) ) ? $this->current_taxonomy : $taxnow;

		// This will set parent file aliases for our menu urls so that the styles of the active menu work correctly.
		foreach ( $this->linked_types as $post_type => $taxonomy ) {
			if ( ! empty( $taxnow ) && in_array( $taxnow, $this->taxonomies, true ) ) {

				// Make sure we are using proper parent menu.
				$current_menu_slug = add_query_arg( array( 'post_type' => $post_type, 'taxonomy' => $taxnow ), $pagenow );
				$current_edit_slug = add_query_arg( array( 'post_type' => $post_type, 'taxonomy' => $taxnow ), 'edit.php' );
				$current_new_slug = add_query_arg( array( 'post_type' => $post_type, 'taxonomy' => $taxnow ), 'post-new.php' );

				// Check our type query variable
				// We check the url param here becasue we need to match on the URL and the landing type can get set before we get here.
				if ( ! empty( $_GET['landing_type'] ) ) {
					$current_new_slug = add_query_arg( array( 'landing_type' => $this->current_landing_type ), $current_new_slug );
					$current_menu_slug = add_query_arg( array( 'landing_type' => $this->current_landing_type ), $current_menu_slug );
				}
				$_wp_real_parent_file[ "edit.php?post_type={$post_type}" ] = $current_edit_slug;
				$_wp_real_parent_file[ "post-new.php?post_type={$post_type}" ] = $current_new_slug;

				if ( $post_type === $this->current_post_type ) {
					if ( ! empty( $this->current_post ) ) {
						$parent_file = $current_edit_slug;
						$submenu_file = $current_edit_slug;

					} elseif ( 'edit.php' === $pagenow && ! empty( $_GET['landing_type'] ) && 'archive' === $_GET['landing_type'] ) {
						// If we are viewing just the archive pages, keep the parent submenu highlighted
						$submenu_file = $current_edit_slug;
					} else {
						// We are not on the original landing page so let's trick it so that we can properly set the submenu highlights.
						$submenu_file = $current_menu_slug;
					}
				}
			}
		}
	}

	/**
	 * Set up our admin menus and labels
	 * @param string $hook_suffix.
	 * @return void.
	 */
	public function add_custom_landing_page_types_menus( $hook_suffix ) {
		$this->set_current_landing_page_meta();
		// This will set parent file aliases for our menu urls so that the styles of the active menu work correctly.
		foreach ( $this->linked_types as $post_type => $taxonomy ) {
			if ( apply_filters( 'tmsc_custom_landing_remove_original_menu', true, $post_type, $taxonomy ) ) {
				remove_menu_page( "edit.php?post_type={$post_type}" );
			}
			if ( is_array( $taxonomy ) ) {
				foreach ( $taxonomy as $tax ) {
					$this->add_custom_landing_menu( $post_type, $tax );
				}
			} else {
				$this->add_custom_landing_menu( $post_type, $taxonomy );
			}
		}
	}

	/**
	 * Add a menu for a custom landing.
	 * @param string $post_type.
	 * @param string $taxonomy.
	 * @return void.
	 */
	public function add_custom_landing_menu( $post_type, $taxonomy ) {
		$menu_order = ! empty( $this->types[ $taxonomy ]['menu_order'] ) ? absint( $this->types[ $taxonomy ]['menu_order'] ) : 20;

		// Our menu slugs and alias
		$edit_slug = add_query_arg( array( 'post_type' => $post_type, 'taxonomy' => $taxonomy ), 'edit.php' );
		$new_slug = add_query_arg( array( 'post_type' => $post_type, 'taxonomy' => $taxonomy ), 'post-new.php' );
		add_filter( "post_type_labels_{$post_type}", array( $this, 'set_menu_labels' ) );
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
				$this->types[ $taxonomy ]['labels']['child_name'],
				$this->types[ $taxonomy ]['labels']['child_name'],
				'edit_posts',
				add_query_arg( array( 'landing_type' => 'single' ), $edit_slug )
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
			do_action( 'tmsc_add_custom_landing_submenus', $post_type, $taxonomy, $edit_slug, $this->types[ $taxonomy ]['labels'] );
		remove_filter( "post_type_labels_{$post_type}", array( $this, 'set_menu_labels' ) );
	}

	/**
	 * Manage the dropdown to filter child posts.
	 */
	public function restrict_manage_posts() {
		global $typenow;
		if ( in_array( $typenow, array_keys( $this->linked_types ), true ) && ! empty( $this->current_taxonomy ) ) {
			$label = $this->types[ $this->current_taxonomy ]['labels']['name'];
			$selected = ( ! empty( $this->current_term->term_id ) ) ? $this->current_term->term_id : 0;

			if ( taxonomy_exists( $this->current_taxonomy ) ) {
				wp_dropdown_categories( array(
					/* translators: The label for a given taxonomy. */
					'show_option_all' => sprintf( 'Show All %s', $label ),
					'taxonomy' => $this->current_taxonomy,
					'name' => $this->current_taxonomy,
					'orderby' => 'name',
					'hierarchical' => true,
					'depth' => 1,
					'show_count' => false,
					'hide_empty' => false,
					'hide_if_empty' => true,
					'selected' => $selected,
				) );
			}
		}
	}

	/**
	 * This is used to display the children of a post when the edit.php is passed with a filter action.
	 *
	 */
	public function parse_query( $query ) {
		$this->set_current_landing_page_meta();
		global $pagenow, $typenow;
		if (
			'edit.php' === $pagenow &&
			in_array( $typenow, array_keys( $this->linked_types ), true )
		) {
			// These allows a post type that has been assigned to multiple taxonomies to return the proper results on the orginal menu link.
			if ( empty( $this->current_taxonomy ) ) {
				$query->set( 'post_parent', 0 );
				$meta_query_args = array(
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
				);
				if ( ! empty( $_GET['type'] ) ) {
					$meta_query_args = array(
						'relation' => 'AND',
						array(
							'key' => 'landing_page_type',
							'value' => sanitize_text_field( wp_unslash( $_GET['type'] ) ),
						),
						$meta_query_args,
					);
				}

				$query->set( 'meta_query', $meta_query_args );

			} elseif ( ! empty( $this->current_taxonomy ) && in_array( $this->current_taxonomy, $this->taxonomies, true ) ) {
				if ( ! empty( $query->query['post_status'] ) ) {
					$query->set( 'meta_query', array(
						array(
							'key' => 'linked_taxonomy',
							'value' => $this->current_taxonomy,
						),
					) );
					unset( $query->query_vars[ $this->current_taxonomy ] );
					unset( $query->query_vars['taxonomy'] );
					unset( $query->query[ $this->current_taxonomy ] );
					unset( $query->query['taxonomy'] );
					$query->set( 'orderby', 'menu_order title' );
					$query->tax_query = null;
				} elseif ( ! empty( $this->current_term ) ) {
					$parent = tmsc_get_linked_post( $this->current_term->term_id );
					$query->set( 'post_parent', $parent->ID );
					unset( $query->query_vars[ $this->current_taxonomy ] );
					unset( $query->query[ $this->current_taxonomy ] );
					$query->tax_query = null;
				} else {
					// Are we on the 'All' status page?
					if ( empty( $_GET['landing_type'] ) && 'review-date' !== $query->get( 'orderby' ) ) {
						if ( empty( $query->get( 'orderby' ) ) ) {
							$query->set( 'order', 'asc' );
							$query->set( 'orderby', 'menu_order title' );
						}
						$query->set( 'fields', 'id=>parent' );
					} elseif ( 'single' === $this->current_landing_type ) {
						$query->set( 'post_parent__not_in', array( 0 ) );
					} else {
						$query->set( 'post_parent', 0 );
					}
					$query->set( 'meta_query', array(
						array(
							'key' => 'linked_taxonomy',
							'value' => $this->current_taxonomy,
						),
					) );
				} // End if().
			} // End if().
		} // End if().

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
			in_array( $typenow, array_keys( $this->linked_types ), true ) &&
			! empty( $this->current_taxonomy ) &&
			"post-new.php?post_type={$typenow}" === $path
		) {
			$this->set_current_landing_page_meta();
			$url = add_query_arg( 'taxonomy', $this->current_taxonomy, $url );
			if ( ! empty( $this->current_landing_type ) ) {
				$url = add_query_arg( array( 'landing_type' => $this->current_landing_type ), $url );
			}

			if ( ! empty( $this->current_term->term_id ) ) {
				$url = add_query_arg( array( $this->current_taxonomy => $this->current_term->term_id ), $url );
			}
		}
		return $url;
	}

	/**
	 * We hard override our status views with our custom set.
	 */
	public function get_current_views() {
		$args = array(
			'post_type' => $this->current_post_type,
			'taxonomy' => $this->current_taxonomy,
		);

		$all_attrs = ( empty( $_GET[ $this->current_taxonomy ] ) && empty( $_GET['post_status'] ) && empty( $_GET['landing_type'] ) ) ? array( 'class' => 'current' ) : array();
		$parent_attrs = ( ! empty( $_GET['landing_type'] ) && ! empty( $this->current_landing_type ) && 'archive' === $this->current_landing_type ) ? array( 'class' => 'current' ) : array();
		$subpages_attrs = ( ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) ? array( 'class' => 'current' ) : array();
		$trash_attrs = ( ! empty( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] ) ? array( 'class' => 'current' ) : array();

		return array(
			'all' => $this->get_admin_link( 'edit.php', $args, __( 'All', 'tmsc' ), $all_attrs ),
			'parents' => $this->get_admin_link( 'edit.php', array_merge( $args, array( 'landing_type' => 'archive' ) ), $this->types[ $this->current_taxonomy ]['labels']['name'], $parent_attrs ),
			'subpages' => $this->get_admin_link( 'edit.php', array_merge( $args, array( 'landing_type' => 'single' ) ), $this->types[ $this->current_taxonomy ]['labels']['child_name'], $subpages_attrs ),
			'trash' => $this->get_admin_link( 'edit.php', array_merge( $args, array( 'post_status' => 'trash' ) ), __( 'Trash', 'tmsc' ), $trash_attrs ),
		);
	}

	/**
	 * Set the urls for the status filter links on the edit pages.
	 * @param array $views. Associative array of status keys with corresponding links.
	 * @return array;
	 */
	public function set_status_filter_urls( $views ) {
		if ( $this->on_custom_landing_admin_page() ) {
			$this->current_views = $this->get_current_views();
			return $this->current_views;
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

			$link = '<a href="' . esc_url( add_query_arg( array( 'post_type' => $this->current_post_type, $this->current_taxonomy => $term->term_id ), 'edit.php' ) ) . '" aria-label="' . esc_html__( 'View ', 'tmsc' ) . esc_attr( $post->post_title ) . ' ' . esc_html( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ) . '">' . esc_html( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ) . '</a>';
			$actions = array_merge( array( 'subpages' => $link ), $actions );

		}
		return $actions;
	}

	/**
	 * Append child slug to taxonomy permalink.
	 */
	public function get_subpage_permalink( $post_link, $post, $type, $term_id ) {
		if ( 'taxonomy' === $type && $this->is_custom_landing_page_post_type( $post->post_type ) && ! empty( $post->post_parent ) ) {
			$post_link = trailingslashit( $post_link ) . $post->post_name;
		}
		return $post_link;
	}

	/**
	 * Add in proper rewrite rules for our child subpages.
	 */
	public function set_subpage_rewrites() {
		foreach ( $this->types as $taxonomy => $config ) {
			$post_type = $this->types[ $taxonomy ]['post_type'];
			if ( ! empty( $post_type ) ) {
				$rule = '^' . $taxonomy . '/([^/]+)/([^/]+)/?$';
				$rewrite = 'index.php?' . $post_type . '=$matches[2]';
				add_rewrite_rule( $rule, $rewrite, 'top' );
			}
		}
	}

	/**
	 * If we are on a child landing-page, make sure we allow the post type in the query.
	 */
	public function set_subpage_post_type( $canonical_post_types = array(), $landing_page_type = null, $landing_page_slug = null, $landing_page_term = null ) {
		if ( is_single() ) {
			$post_type = get_query_var( 'post_type' );
			if ( ! empty( $post_type ) && $this->is_custom_landing_page_post_type( $post_type ) ) {
				$canonical_post_types[] = $post_type;
			}
		}
		return $canonical_post_types;
	}

	/**
	 * Add input fields to specfic post list tables to allow for proper searching.
	 */
	public function add_taxonomy_search_field( $post_type ) {
		if ( in_array( $post_type, array_keys( $this->linked_types ), true ) ) {
			echo '<input type="hidden" name="taxonomy" value="' . esc_attr( $this->linked_types[ $post_type ] ) .'" />';
		}
	}

	/**
	 * Helper to create links to edit.php with params.
	 *
	 * @param string $link. The admin url we want to use.
	 * @param array  $args. URL parameters for the link.
	 * @param string $label. Link text.
	 * @param array $attrs. Optional. Any optional attributes. array( 'class' => 'class-name etc' ).
	 * @return string. The formatted link string.
	 */
	public function get_admin_link( $link = 'edit.php', $args, $label, $attrs = array() ) {
		$url = add_query_arg( $args, $link );

		$attr_html = '';
		if ( ! empty( $attrs ) ) {
			foreach ( $attrs as $name => $value ) {
				 $attr_html = sprintf(
					' %s="%s"',
					esc_attr( $name ),
					esc_attr( $value )
				);
			}
		}

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$attr_html,
			$label
		);
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
					<?php
					if ( $this->is_parent_landing_page( $post ) ) {
						$term = tmsc_get_linked_term( $post->ID );
						?>
						<strong><?php echo esc_html( $this->types[ $this->current_taxonomy ]['labels']['child_name'] ); ?>:</strong>
						<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => $this->current_post_type, $this->current_taxonomy => $term->term_id ), 'edit.php' ) ); ?>"><?php esc_html_e( 'View', 'tmsc' ); ?></a>
					<?php } else { ?>
						<strong>
							<?php
							echo esc_html_e( 'Parent ', 'tmsc' );
							echo esc_html( $this->types[ $this->current_taxonomy ]['labels']['singular_name'] );
							?>
							:
						</strong>
						<a href="<?php echo esc_url( add_query_arg( array( 'post' => $post->post_parent, 'action' => 'edit' ), 'post.php' ) ); ?>">
							<?php echo esc_html( get_the_title( $post->post_parent ) ); ?>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php
		}
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

			$wp_post_types[ $this->current_post_type ]->labels = (object) array_merge( (array) $wp_post_types[ $this->current_post_type ]->labels, (array) $this->types[ $this->current_taxonomy ]['labels'] );
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
	 * Add in an icon to display the current landing page type.
	 */
	public function add_landing_type_meta_box_info( $post ) {

		// If the post has been created, we can grab the saved info.
		if ( $this->on_custom_landing_admin_page( $post ) && ! empty( $this->current_landing_type ) && 'single' === $this->current_landing_type ) {
			$name = $this->types[ $this->current_taxonomy ]['labels']['singular_name'];
			$terms = get_terms( array( 'taxonomy' => $this->current_taxonomy, 'hide_empty' => false ) );
			?>
			<div class="misc-pub-section misc-pub-lptype">
				<span id="custom-landing-page-pub-section">
					<span class="dashicons <?php echo esc_attr( $this->types[ $this->current_taxonomy ]['menu_icon'] ); ?>"></span>
				</span>
				&nbsp;<?php echo esc_html( $name ); ?>: <b><span class="ai-target-text"><?php echo ( ! empty( $this->current_term ) ) ? esc_html( $this->current_term->name ) : ''; ?></span></b>
				<a href="#custom-landing-page-type" class="ai-edit-button edit-custom-landing-page-type hide-if-no-js" role="button">
					<span aria-hidden="true"><?php esc_html_e( 'Edit', 'tmsc' ); ?></span>
					<span class="screen-reader-text">
						<?php
						esc_html_e( 'Edit ', 'tmsc' );
						echo esc_html( $name );
						?>
					</span>
				</a>
				<div id="custom-landing-page-type-select" class="ai-edit-button-target hide-if-js">
					<label for="custom-landing-page-type" class="screen-reader-text">
						<?php
						esc_html_e( 'Set ', 'tmsc' );
						echo esc_html( $name );
						?>
					</label>
					<select name="post_parent" id="post_parent">
						<?php foreach ( $terms as $term ) : ?>
							<?php $parent = tmsc_get_linked_post( $term->term_id ); ?>
							<option value="<?php echo esc_attr( $parent->ID ); ?>"<?php echo ( ! empty( $this->current_term->term_id ) && $term->term_id === $this->current_term->term_id ) ? ' selected="selected"' : ''; ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
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
		global $pagenow;
		if ( ! empty( $_POST['post_ID'] ) && ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['post_type'] ) ) {
			$post_id = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . absint( wp_unslash( $_POST['post_ID'] ) ) ) ? absint( wp_unslash( $_POST['post_ID'] ) ) : null;
			$posted_post_type = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
		}
		if ( ! empty( $this->current_post_type ) && in_array( $this->current_post_type, array_keys( $this->linked_types ), true ) &&
			! empty( $this->current_taxonomy ) && in_array( $this->current_taxonomy, $this->taxonomies, true )
		) {
			return true;
		} elseif ( 'post.php' === $pagenow && ! empty( $posted_post_type ) && in_array( $posted_post_type, array_keys( $this->linked_types ), true ) ) {
			// Handle saving and redirects of posts.
			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				if ( ! empty( $post ) ) {
					if ( empty( $post->post_parent ) ) {
						$term = tmsc_get_linked_term( $post_id );
					} else {
						$term = tmsc_get_linked_term( $post->post_parent );
					}
					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						$this->current_taxonomy = $term->taxonomy;
						$this->current_post_type = $post->post_type;
						$this->current_landing_type = empty( $post->post_parent ) ? 'archive' : 'single';
						return true;
					}
				}
			}
			// If we don't have a post id and it's a new post, let's grab the data from the referral.
			$url_args = wp_parse_args( wp_parse_url( wp_get_referer(), PHP_URL_QUERY ) );
			if ( ! empty( $url_args['taxonomy'] ) && in_array( $url_args['taxonomy'], $this->taxonomies ) ) {

				$this->current_taxonomy = $url_args['taxonomy'];
				$this->current_post_type = ( ! empty( $url_args['post_type'] ) ) ? $url_args['post_type'] : sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
				$this->current_landing_type = ( ! empty( $url_args['landing_type'] ) ) ? $url_args['landing_type'] : 'archive';
				return true;
			}
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
		if ( ! in_array( $post->post_type, array_keys( $this->linked_types ), true ) ) {
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
			$taxonomy = get_post_meta( $post->ID, 'linked_taxonomy', true );
		}
		return $taxonomy;
	}
}

function TMSC_Custom_Landing_Page_Types() {
	return TMSC_Custom_Landing_Page_Types::get_instance();
}
// Initial call to setup instance
add_action( 'after_setup_theme', 'TMSC_Custom_Landing_Page_Types', 10 );

