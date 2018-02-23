<?php
/**
 * Allow a post and taxonomy to be linked.
 *
 * @package  TMSConnect
 */

class TMSC_Linked_Taxonomy_Posts {
	use \TMSC\Singleton;

	/**
	 * The linked post types and taxonomies.
	 *
	 * @var array. array( 'post_type_slug' => 'taxonomy_slug' )
	 */
	public $linked_types = array( 'constituent' => 'constituents' );

	/**
	 * The group name used for our object cache.
	 *
	 * @var string.
	 */
	public $linked_cache_group = 'linked_taxonomy_posts';

	/**
	 * The post meta key used to store the linked term id.
	 *
	 * @var string.
	 */
	public $linked_post_meta_key = 'linked_term_id';

	/**
	 * The term meta key used to store the linked post id.
	 *
	 * @var string.
	 */
	public $linked_term_meta_key = 'linked_post_id';

	/**
	 * The post meta key used to store the linked taxonomy.
	 * While term ids should be unique, this will save an additional lookup if we need to grab landing page specific config data.
	 *
	 * @var string.
	 */
	public $linked_taxonomy_meta_key = 'linked_taxonomy';


	/**
	 * If we are linking to parent terms instead of a taxonomy, we track the terms here.
	 *
	 * @var object. A WP Term Objects.
	 */
	public $parent_term = null;

	/**
	 * Initialize our class
	 * @return void.
	 */
	public function setup() {
		// Allow us to set our linked items with a filter instead of using the config.
		$this->linked_types = apply_filters( 'tmsc_linked_taxonomy_posts',  $this->linked_types );

		// Setup save post hooks.
		$this->set_hooks();

	}

	/**
	 * Setup any hooks or filters required for linking.
	 * @return void.
	 */
	public function set_hooks() {
		add_action( 'init', array( $this, 'manage_linked_taxonomy_caps' ), 50 );
		add_filter( 'tmsc_register_taxonomy_object_types', array( $this, 'register_linked_taxonomies' ), 20, 2 );


		if ( is_admin() || defined( 'DOING_CRON' ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			// Save our linked taxonomies when a post is created/edited.
			foreach ( $this->linked_types as $post_type => $tax ) {
				// Our link save post logic
				add_action( "save_post_{$post_type}", array( $this, 'save_linked_taxonomy' ), 20,3 );
			}
		}

		// Ensure we delete the term data when we delete a post.
		add_action( 'before_delete_post', array( $this, 'delete_linked_taxonomy' ) );
		add_action( 'before_delete_post', array( $this, 'delete_child_posts' ) );
	}

	// Register linked post types to corresponding taxonomies.
	public function register_linked_taxonomies( $object_types, $name ) {
		foreach ( $this->linked_types as $post_type => $tax ) {
			if ( $tax === $name ) {
				$object_types[] = $post_type;
			}
		}
		return $object_types;
	}

	/**
	 * We don't want users modifying the taxonomies without updating the post, so lets set some custom capablities for the taxonmies.
	 * @return void.
	 */
	public function manage_linked_taxonomy_caps() {
		foreach ( $this->linked_types as $post_type => $tax ) {
			if ( ! empty( $tax ) ) {
				if ( is_array( $tax ) ) {
					foreach ( $tax as $tax_slug ) {
						$this->set_capabilities( $tax_slug );
					}
				} else {
					$this->set_capabilities( $tax );
				}
			}
		}
	}

	/**
	 * Set the capabilities for each taxonomy so you can only create new terms by creating new posts.
	 * @param string $taxonomy.
	 * @return void.
	 */
	public function set_capabilities( $taxonomy ) {
		global $wp_taxonomies;
		if ( ! empty( $wp_taxonomies[ $taxonomy ] ) ) {
			$capability = "manage_linked_{$taxonomy}";
			$wp_taxonomies[ $taxonomy ]->cap->assign_terms = 'edit_posts';
			$wp_taxonomies[ $taxonomy ]->cap->delete_terms = $capability;
			$wp_taxonomies[ $taxonomy ]->cap->edit_terms = $capability;
			$wp_taxonomies[ $taxonomy ]->cap->manage_terms = $capability;
			do_action( 'tmsc_set_linked_taxonomy_caps', $taxonomy );
		}
	}

	/**
	 * Insert/Update a taxonomy term when it's linked post is saved.
	 * @param int $post_id.
	 * @param object $post. WP_Post object
	 * @param boolean $update.
	 * @return object. WP_Term object.
	 */
	public function save_linked_taxonomy( $post_id, $post = null, $update = true ) {
		// Short circut the write of data if needed.
		// This is useful when you need to map multiple taxonomies to a single post type as the realtionship must be 1 to 1.
		if ( apply_filters( 'tmsc_skip_linked_taxonomy_save', false ) ) {
			return;
		}

		$term = $this->get_linked_term( $post_id );

		if ( empty( $term ) ) {

			$post = ( empty( $post ) ) ? get_post( $post_id ) : $post;
			$post_type = ( 'revision' === $post->post_type ) ? get_post_type( $post->post_parent ) : $post->post_type;

			if ( ! empty( $this->linked_types[ $post_type ] ) ) {
				// If we want to link a post to terms instead of taxonomies, we calculate the parent terms here.
				if ( is_array( $this->linked_types[ $post_type ] ) ) {
					$taxonomy = $this->get_query_arg( 'taxonomy' );
					if ( empty( $taxonomy ) || ! in_array( $taxonomy, $this->linked_types[ $post_type ], true ) ) {
						return;
					}
				} elseif ( is_string( $this->linked_types[ $post_type ] ) ) {
					$taxonomy = $this->linked_types[ $post_type ];
				} else {
					// No need to save anything.
					return;
				}
				// Don't save our initial auto draft.
				if ( 'auto-draft' !== $post->post_status ) {
					// Often the top level posts will act as a container for child posts and we won't need terms for these.
					// Use the filter hook||config below to enable it for child posts.
					if ( empty( $post->post_parent ) || apply_filters( 'tmsc_link_child_posts', false, $post, $taxonomy ) ) {

						// If filter hook above has been applied, then write a child term.
						$parent_term = 0;
						if ( ! empty( $this->parent_term ) ) {
							$parent_term = $this->parent_term->term_id;
						}

						$args = array(
							'slug' => $post->post_name,
							'parent' => ( ! empty( $parent_term ) ) ? $parent_term : 0,
						);
						$term = wp_insert_term( $post->post_title, $taxonomy, $args );

						if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
							update_term_meta( $term['term_id'], $this->linked_term_meta_key, $post_id );
							update_post_meta( $post_id, $this->linked_post_meta_key, $term['term_id'] );
							update_post_meta( $post_id, $this->linked_taxonomy_meta_key, $taxonomy );
							$term = get_term( $term['term_id'], $taxonomy );

							// Overwrite associated term with post.
							wp_set_post_terms( $post_id, [ (int) $term->term_id ], $taxonomy, false );
						}
					} else {

						$this->clear_linked_cache( $post->post_parent );
						$term = $this->get_linked_term( $post->post_parent );

						// Set the meta data for the children.
						if ( $term instanceof \WP_Term ) {
							update_term_meta( $term->term_id, $this->linked_term_meta_key, $post->post_parent );
							update_post_meta( $post_id, $this->linked_post_meta_key, $term->term_id );
							update_post_meta( $post_id, $this->linked_taxonomy_meta_key, $taxonomy );

							wp_set_post_terms( $post->ID, [ (int) $term->term_id ], $taxonomy, false );
						}
					}
				}
			} // End if().
		} // End if().

		$this->clear_linked_cache( $post_id );
		if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
			return $term;
		}
	}


	/**
	 * Run through a series of checks to grab a query arg.
	 */
	public function get_query_arg( $name ) {
		global $taxnow, $typenow;
		$value = '';
		$url_args = wp_parse_args( wp_parse_url( wp_get_referer(), PHP_URL_QUERY ) );
		// Get the value of an arbitrary post argument.
		// @todo We are suppressing the need for a nonce check, which means this whole thing likely needs a rewrite.
		$post_arg_val = ! empty( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : null; // @codingStandardsIgnoreLine

		switch ( true ) {
			case ( ! empty( get_query_var( $name ) ) ):
				$value = get_query_var( $name );
				break;
			// If the query arg isn't set. Check POST and GET requests.
			case ( ! empty( $post_arg_val ) ):
				// Verify nonce here.
				$value = $post_arg_val;
				break;
			case ( ! empty( $_GET[ $name ] ) ):
				$value = sanitize_text_field( wp_unslash( $_GET[ $name ] ) );
				break;
			case ( 'post_type' === $name && ! empty( $typenow ) ):
				$value = $typenow;
				break;
			case ( 'taxonomy' === $name && ! empty( $taxnow ) ):
				$value = $taxnow;
				break;
			case ( ! empty( $url_args[ $name ] ) ):
				$value = $url_args[ $name ];
				break;
			default:
				$value = '';
		}
		return $value;
	}

	/**
	 * Delete a linked taxonomy when it's post is deleted.
	 * @param int $post_id.
	 * @return void.
	 */
	public function delete_linked_taxonomy( $post_id ) {
		$term = $this->get_linked_term( $post_id );
		if ( ! empty( $term ) ) {
			wp_delete_term( $term->term_id, $term->taxonomy );
		}
		$this->clear_linked_cache( $post_id );
	}

	/**
	 * Delete any associated child posts.
	 * @param int $post_id.
	 * @return void.
	 */
	public function delete_child_posts( $post_id ) {
		$post = get_post( $post_id );
		$children = get_posts( array(
			'post_type' => $post->post_type,
			'post_parent' => $post->ID,
		) );
		foreach ( $children as $child ) {
			wp_trash_post( $child->ID );
			clean_post_cache( $child );
			$this->clear_linked_cache( $child->ID );
		}
	}

	/**
	 * Get the corresponding linked term.
	 * @param int $post_id.
	 * @return object. WP_Term Object.
	 */
	public function get_linked_term( $post_id ) {
		$key = "taxonomy_linked_post_{$post_id}";
		$term = wp_cache_get( $key, $this->linked_cache_group );
		if ( false === $term ) {
			$post = get_post( $post_id );
			if ( ! empty( $post ) ) {
				$post_type = ( 'revision' === $post->post_type ) ? get_post_type( $post->post_parent ) : $post->post_type;

				if ( ! empty( $this->linked_types[ $post_type ] ) ) {
					$term_id = get_post_meta( $post_id, $this->linked_post_meta_key , true );
					if ( ! empty( $term_id ) ) {
						$term = get_term( $term_id );
						if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
							wp_cache_set( $key, $term, $this->linked_cache_group );
						} else {
							$term = false;
						}
					}
				}
			} else {
				$term = false;
			}
		}
		return $term;
	}

	/**
	 * Get the corresponding linked post.
	 * @param int $term_id.
	 * @return object. WP_Post Object.
	 */
	public function get_linked_post( $term_id ) {
		$key = "post_linked_term_{$term_id}";
		$post = wp_cache_get( $key, $this->linked_cache_group );
		if ( false === $post ) {
			$post_id = get_term_meta( $term_id, $this->linked_term_meta_key , true );
			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				if ( ! empty( $post ) && ! is_wp_error( $post ) ) {
					wp_cache_set( $key, $post, $this->linked_cache_group );
				} else {
					$post = false;
				}
			}
		}
		return $post;
	}

	/**
	 * Clear our linked cache.
	 * @param int $post_id.
	 * @return void.
	 */
	public function clear_linked_cache( $post_id ) {
		wp_cache_delete( "taxonomy_linked_post_{$post_id}", $this->linked_cache_group );
		$term = $this->get_linked_term( $post_id );
		if ( ! empty( $term->term_id ) ) {
			wp_cache_delete( "post_linked_term_{$term->term_id}", $this->linked_cache_group );
		}
	}
}

function TMSC_Linked_Taxonomy_Posts() {
	return TMSC_Linked_Taxonomy_Posts::get_instance();
}
// Initial call to setup instance
add_action( 'after_setup_theme', 'TMSC_Linked_Taxonomy_Posts', 15 );
