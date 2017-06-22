<?php

namespace TMSC\Database;

/**
 * Base class for any imported post
 */
class TMSC_Object extends \TMSC\Database\Migrateable {

	/**
	 * Terms for the current post.
	 * Implementations of get_terms can store here to avoid
	 * having to re-query if other functions need this data.
	 * @var array
	 */
	protected $terms = null;

	/**
	 * Authors for the current post.
	 * Implementations of get_authors can store here to avoid
	 * having to re-query if other functions need this data.
	 * @var array
	 */
	protected $authors = null;

	/**
	 * The type of migrateable object. Must be set by all implementing classes.
	 * @var string
	 */
	public $type = 'post';

	/**
	 * Constructor. Set this as a post migrateable.
	 */
	public function __construct() {
		parent::__construct( 'post' );
	}

	/**
	 * Get legacy ID
	 * @return int
	 *
	 */
	public function get_legacy_id() {
		if ( ! empty( $this->raw->TermID ) ) {
			return $this->raw->TermID;
		}
	}

	/**
	 * Get the last updated data hash.
	 * @return mixed string|false
	 */
	public function get_last_updated_hash() {
		if ( ! empty( $this->object ) ) {
			return $this->get_meta( 'tmsc_last_updated', true );
		}
		return false;
	}

	/**
	 * Set the last updated data hash.
	 */
	public function set_last_updated_hash() {
		if ( ! empty( $this->raw ) ) {
			$this->update_meta( 'tmsc_last_updated', tmsc_hash_data( $this->raw ) );
		}
	}

	/**
	 * Get excerpt
	 * @return html
	 */
	public function get_excerpt(){}

	/**
	 * Get title
	 * @return string
	 */
	public function get_title(){}

	/**
	 * Get post author.
	 * @return int ID of author user.
	 */
	public function get_post_author() {
		// Use the admin user by default
		return 1;
	}

	/**
	 * Get guest authors.
	 * @return array of names. Leave empty if Co-Authors Plus isn't used.
	 */
	public function get_authors(){}

	/**
	 * Get terms
	 * @return associative array, like array( 'category' => array( 'News', 'Sports' ), 'post_tag' => array( 'Football', 'Jets' ) )
	 */
	public function get_terms(){}

	/**
	 * Get date of publication
	 * @return int unix timestamp
	 */
	public function get_pubdate(){}

	/**
	 * Get body
	 * @return HTML
	 */
	public function get_body(){}

	/**
	 * Get post slug
	 * @return string post slug
	 */
	public function get_post_name() {}

	/**
	 * Get post parent
	 * @return integer parent post id
	 */
	public function get_post_parent() {}

	/**
	 * Get post type
	 * @return string
	 */
	public function get_post_type() {
		return 'post';
	}

	/**
	 * Get post status
	 * @return string
	 */
	public function get_post_status() {
		return 'publish';
	}

	/**
	 * Get comment status
	 * @return string 'open' or 'closed'
	 */
	public function get_comment_status() {}

	/**
	 * Save the final post status
	 * @return string
	 */
	public function save_final_object_status() {
		\TMSC\Util\Map::get( 'legacy_post_ids' )->map( $this->get_legacy_id(), $this->object->ID );
		$this->object->post_status = $this->get_post_status();
		wp_update_post( $this->object );
	}

	/**
	 * Get post object
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->get_object();
	}

	/**
	 * Get the post permalink
	 * @return string
	 */
	public function get_url() {
		if ( !empty( $this->object->ID ) ) {
			return get_permalink( $this->object->ID );
		}
		return '';
	}

	/**
	 * Update the post (used in after_save usually)
	 */
	public function update() {
		wp_update_post( $this->object );
	}

	/**
	 * Delete the post
	 */
	public function delete() {
		if ( ! empty( $this->object->ID ) ) {
			wp_delete_post( $this->object->ID, true );
		}
	}

	/**
	 * Load an existing post if it exists.
	 */
	public function load_existing_object() {
		// Check for existing post by legacy GUID
		$legacy_id = $this->get_legacy_id();

		// It's possible the object could have been previously loaded in a before_save
		// function to validate a migration conditon, so make sure it wasn't set.
		if ( ! empty( $legacy_id ) && empty( $this->object->ID ) ) {
			$existing_post_id = tmsc_post_id_by_legacy_id( $legacy_id );
			if ( $existing_post_id ) {
				$this->object = get_post( $existing_post_id );
			}
		}
	}

	/**
	 * Save this post
	 * @return boolean true if successfully saved
	 */
	public function save() {
		if ( $this->before_save() ) {
			return;
		}
		$this->set_current_object();
		$this->load_existing_object();

		if ( $this->requires_update() ) {
			$this->object = $this->save_post();

			if ( empty( $this->object->ID ) ) {
				return false;
			} else {
				if ( ! empty( $this->raw->Children ) && ! empty( $this->raw->CN ) ) {
					$this->parents[ $this->raw->CN ] = $this->object->term_id;
				}
			}

			$this->after_save();

			return true;
		}
		return false;

		if ( $this->before_save() ) {
			return;
		}

		$this->load_existing_post();

		$post = array(
			'post_title' => $this->get_title(),
			'post_status' => 'migrating',
			'post_author' => $this->get_post_author(),
			'post_date' => date( 'Y-m-d H:i:s', $this->get_pubdate() ),
			'post_date_gmt' => get_gmt_from_date( date( 'Y-m-d H:i:s', $this->get_pubdate() ) ),
			'post_type' => $this->get_post_type(),
			'post_content' => $this->get_body(),
			'post_excerpt' => $this->get_excerpt(),
			'post_parent' => $this->get_post_parent(),
			'post_name' => $this->get_post_name(),
			'comment_status' => $this->get_comment_status(),
		);

		if ( ! $this->save_override( $post ) ) {
			if ( ! empty( $this->object->ID ) ) {
				$post['ID'] = $this->object->ID;
			} elseif ( 'attachment' == $this->get_post_type() ) {
				throw new \Exception( 'You should subclass Attachment, not Migrateable.' );
			}

			$post_id = wp_insert_post( $post );
			// Load the new post
			$this->object = get_post( $post_id );
		} elseif ( 'attachment' != $this->get_post_type() ) {
			// We skip this notice on attachments since they're always skipped
		}

		if ( empty( $this->object->ID ) ) {
			return false;
		}

		delete_post_meta( $this->object->ID, 'tmsc_stub' );

		$this->save_terms( $this->get_terms() );

		// if you don't use CAP, you're own your own with authors.
		if ( function_exists( 'coauthors' ) ) {
			$this->save_authors( $this->get_authors() );
		}

		$this->flush_meta_queue();

		$this->after_save();

		return true;
	}

	/**
	 * Only save mappings for this post
	 * @return boolean true if successfully saved
	 */
	public function mapping_only_save() {

		// check for existing post by legacy GUID
		$legacy_id = $this->get_legacy_id();
		if ( ! empty( $legacy_id ) ) {
			$existing_post = tmsc_post_by_legacy_id( $legacy_id );
			if ( $existing_post  ) {
				$this->object = $existing_post;
			}
		}

		if ( empty( $this->object->ID ) ) {
			return false;
		}

		$this->save_terms( $this->get_terms() );

		// if you don't use CAP, you're own your own with authors.
		if ( function_exists( 'coauthors' ) ) {
			$this->save_authors( $this->get_authors() );
		}

		$this->flush_meta_queue();

		return true;
	}

	/**
	 * Save terms
	 * @see get_terms()
	 * @param array $term_struct
	 */
	public function save_terms( $term_struct, $do_map = true ) {
		foreach ( $term_struct as $tax => $terms ) {
			foreach ( $terms as $i => $term ) {
				$mappings = tmsc_get_mapping( $tax, $term );
				if ( !empty( $mappings ) ) {
					unset( $term_struct[$tax][$i] ); // prepare for overwrite
					foreach ( $mappings as $mapping ) {
						if ( $mapping['action'] == 'remove' ) {
							unset( $term_struct[$tax][$i] ); // make sure it's gone
							break;
						}
						if ( empty( $term_struct[ $mapping['dest_type'] ] ) ) {
							$term_struct[ $mapping['dest_type'] ] = array();
						}
						$term_struct[ $mapping['dest_type'] ][] = $mapping['dest'];
					}
				}
			}
		}
		foreach ( $term_struct as $tax => $terms ) {
			$term_ids = array();
			foreach ( $terms as $term ) {
				$tid = (int) static::get_or_create_term_by_name( $term, $tax );
				if ( $tid ) $term_ids[] = $tid;
			}
			wp_set_object_terms( $this->object->ID, $term_ids, $tax );
		}
	}

	/**
	 * Get default authors if none are defined
	 * @return array
	 */
	public function get_default_authors() {
		return array();
	}

	/**
	 * Save authors along with custom metadata
	 * @see get_authors()
	 * @uses co-authors plus
	 * @param array $authors
	 */
	public function save_authors( $authors ) {
		global $coauthors_plus;
		if ( false === $authors ) return;
		$coauthors = array();
		$mapped_authors = array();

		// Check if there is an author mapping for each author name
		foreach ( $authors as $author_name => $author_meta ) {
			$mappings = tmsc_get_mapping( 'author', trim( $author_name ) );
			if ( empty( $mappings ) ) {
				$mapped_authors[ $author_name ] = $author_meta;
				continue;
			}
			foreach ( $mappings as $mapping ) {
				if ( $mapping['action'] == 'remove' ) {
					break;
				}

				// If there is a mapping, update the name and the associated meta key if one exists
				if ( $mapping['dest_type'] == 'author' ) {
					$mapped_authors[ $mapping['dest'] ] = $author_meta;
				}
			}
		}

		if ( empty( $mapped_authors ) ) {
			$mapped_authors = $this->get_default_authors();
		}

		$this->authors = array();
		foreach ( $mapped_authors as $author_name => $author_meta ) {
			$author = static::get_or_create_author_by_name( $author_name, $author_meta, 'all' );
			$coauthors[] = intval( $author->term_id );
			$this->authors[ $author_name ] = $author;
		}

		// Add the authors to the post
		if( !empty( $coauthors ) ) {
			wp_set_object_terms( $this->object->ID, $coauthors, $coauthors_plus->coauthor_taxonomy, false );
		}
	}

	/**
	 * Helper to create a taxonomy term
	 * @param string $name
	 * @param string $taxonomy
	 * @param array $args
	 * @return int term id
	 */
	public static function get_or_create_term_by_name( $name, $taxonomy, $args = array() ) {
		$term = get_term_by( 'name', $name, $taxonomy );
		if ( is_object( $term ) && is_numeric( $term->term_id ) ) {
			return $term->term_id;
		}
		$inserted_term = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $inserted_term ) ) {
			if ( ! empty( $inserted_term->error_data[ 'term_exists' ] ) ) {
				return $inserted_term->error_data[ 'term_exists' ];
			}
			return false;
		}
		return $inserted_term['term_id'];
	}

	/**
	 * Helper to create an author
	 * @param string $author_name
	 * @param array $author_meta
	 * @param string $return_type either 'post_id' or 'term_id'
	 * @return int author post or term id
	 */
	public static function get_or_create_author_by_name( $author_name, $author_meta = array(), $return_type = 'term_id' ) {
		global $coauthors_plus;

		// Build the author name and slug from the import file
		$author_slug = sanitize_title( $author_name );
		$author_tax_slug = 'cap-' . $author_slug;
		$author_term_id = false;

		// Create the post name to be used for the guest author
		$post_name = $coauthors_plus->guest_authors->get_post_meta_key( $author_slug );

		// Check if this author already exists and add if necessary
		$author_key = "cap_" . md5( $post_name );
		if ( ( $author_term_id = get_transient( $author_key ) ) === false ) {
			// The author isn't in the cache. Check the database as well.
			$author_term = get_term_by( 'slug', $post_name, $coauthors_plus->coauthor_taxonomy );
			if ( $author_term !== false ) {
				$author_term_id = $author_term->term_id;
				set_transient( $author_key, $author_term_id, 60*60*24 );
			}
		}

		if ( $author_term_id === false ) {

			$user_post_id = static::insert_guest_author( $author_name, $post_name );

		} else {

			$guest_author_post = $coauthors_plus->guest_authors->get_guest_author_by( 'post_name', $post_name );

			if ( !empty( $guest_author_post->ID ) ) {
				$user_post_id = $guest_author_post->ID;
				// As an added bonus, we could update the existing author post here. I'm skipping it for now for speed reasons and since I don't need to do so to fix my problem,
				// but this is where we would do it.
			} else {
				$user_post_id = static::insert_guest_author( $author_name, $post_name );
			}

		}

		if ( $user_post_id ) {

			// Add or update the necessary meta values
			update_post_meta( $user_post_id, $coauthors_plus->guest_authors->get_post_meta_key( 'display_name' ), $author_name );
			update_post_meta( $user_post_id, $coauthors_plus->guest_authors->get_post_meta_key( 'user_login' ), $author_slug );

			// Add or update any custom meta values
			if ( ! empty( $author_meta ) && is_array( $author_meta ) ) {
				foreach ( $author_meta as $meta_key => $meta_value ) {
					update_post_meta( $user_post_id, $meta_key, $meta_value );
				}
			}

			// Ensure there's an 'author' term for this user/guest author
			if( !term_exists( $author_slug, $coauthors_plus->coauthor_taxonomy ) ) {
				$args = array(
					'slug' => $author_tax_slug
				);
				$author_term = wp_insert_term( $author_slug, $coauthors_plus->coauthor_taxonomy, $args );
				if ( !is_wp_error( $author_term ) ) {
					$author_term_id = $author_term['term_id'];
				} else {
					return;
				}
			}
			// Add the author as a post term
			wp_set_post_terms( $user_post_id, array( $author_slug ), $coauthors_plus->coauthor_taxonomy, false );

			// Update the taxonomy term to include details about the user for searching
			$search_values = array();
			$guest_author = $coauthors_plus->guest_authors->get_guest_author_by( 'id', $user_post_id );
			foreach( $coauthors_plus->ajax_search_fields as $search_field ) {
				$search_values[] = $guest_author->$search_field;
			}
			$args = array(
				'description' => implode( ' ', $search_values ),
			);
			wp_update_term( intval( $author_term_id ), $coauthors_plus->coauthor_taxonomy, $args );
		}

		if ( 'post_id' == $return_type ) {
			return $user_post_id;
		} elseif ( 'all' == $return_type ) {
			$author = $coauthors_plus->get_coauthor_by( 'id', $user_post_id );
			$author->term_id = $author_term_id;
			return $author;
		} {
			return $author_term_id;
		}
	}

	/**
	 * Insert a guest author post for use by CAP.
	 * @var string $author_name the display name of the author (e.g. Foo Bar)
	 * @var string $post_name the sanitized post slug (e.g. foo-bar)
	 * @return int $post_id
	 * @todo Use map class to preload all the authors into memory and check that vs. loading from the DB each time through.
	 */
	public static function insert_guest_author( $author_name, $post_name ) {
		global $coauthors_plus;
		// Create the user as a new guest
		$new_post = array(
			'post_title' => $author_name,
			'post_name' => $post_name,
			'post_type' => $coauthors_plus->guest_authors->post_type,
			'post_status' => 'publish',
		);
		$user_post_id = wp_insert_post( $new_post, true );

		if ( is_wp_error( $user_post_id ) ) {
			tmsc_notice( "Error creating author " . $author_name );
		} else {
			tmsc_notice( "Created author [" . $author_name . "]" );
		}

		return $user_post_id;
	}

	/**
	 * Set featured image by remote URL
	 * @return \TMSC\Attachment
	 */
	public function set_featured_image_by_url( $url, $title = '', $caption = '', $description = '' ) {
		$attachment = $this->create_attachment( $url, $title, $caption, $description );
		$attachment_id = $attachment->get_post()->ID;
		if ( ! empty( $attachment_id ) ) {
			$this->update_meta( '_thumbnail_id', $attachment_id );
		}
		return $attachment;
	}

	/**
	 * Create an attachment
	 * @return \TMSC\Attachment
	 */
	public function create_attachment( $url, $title, $caption, $description = '', $name = '', $bits = null ) {
		$settings = $this->get_image_settings();
		$attachment = new $settings['attachment_class']( $url, $title, $caption, $description, $this, $name, $bits );
		$attachment->save();
		$this->children[] = $attachment;
		return $attachment;
	}

	/**
	 * Migrates images included in the post content.
	 *
	 * @throws \Exception If this function is called before the post is saved.
	 *
	 * @access public
	 * @return void
	 */
	public function migrate_images_from_content() {

		// Ensure this post has already been saved before modifying post_content.
		if ( $this->is_new() ) {
			throw new \Exception( 'Cannot migrate images before post is saved.' );
		}

		// Ensure this post has images in post_content.
		if ( empty( $this->object->post_content )
		     || false === stripos( $this->object->post_content, '<img' )
		) {
			call_user_func(
				$this->get_image_setting( 'no_featured_image_callback' )
			);

			return;
		}

		// Convert the HTML fragment to a QueryPath object.
		$html = html5qp( $this->wrap_html_snippet( $this->object->post_content ) );

		// If the very first thing is an image, maybe it should be the featured image
		// rather than embedded in body copy.
		$first_image_is_featured = false;
		if ( $this->get_image_setting( 'make_first_image_featured' ) ) {
			// revert to plain text handling, since an image could be the first visible
			// element, but have various wrappers and whitespace around it.
			$html_with_images_only = trim(
				strip_tags( $this->object->post_content, '<img>' )
			);
			if ( 0 === stripos( $html_with_images_only, '<img' ) ) {
				$first_image_is_featured = true;
			} else {
				call_user_func( $this->get_image_setting( 'no_featured_image_callback' ) );
			}
		}

		foreach ( $html->branch( 'img' ) as $i => $img ) {
			$src = $this->rewrite_image_src( $img->attr( 'src' ) );
			if ( ! $this->image_is_from_allowed_domain( $src ) ) {
				continue;
			}

			$title = '';
			$title_src = $this->get_image_setting( 'title_src' );
			if ( ! empty( $title_src ) ) {
				$title = $img->attr( $title_src );
			}

			$caption = '';
			$caption_src = $this->get_image_setting( 'caption_src' );
			if ( ! empty( $caption_src ) ) {
				$caption = $img->attr( $caption_src );
			}

			$attachment = $this->create_attachment( $src, $title, $caption );
			$attachment->save();

			if ( 0 === $i && $first_image_is_featured ) {
				$attachment_id = $attachment->get_post()->ID;
				if ( ! empty( $attachment_id ) ) {
					$this->update_meta( '_thumbnail_id', $attachment_id );
				}
				$img->remove();
				call_user_func( $this->get_image_setting( 'featured_image_callback' ), $attachment );
				continue;
			}

			if ( $attachment->is_error ) {
				$attachment->delete();
				$img->remove();
				continue;
			}

			$width = $attachment->image_width();
			$preset = '';
			foreach ( $this->get_image_setting( 'sizes' ) as $preset_name => $size ) {
				if ( $width > $size ) {
					$preset = $preset_name;
				}
			}

			$attr = array();
			$class = $img->attr( 'class' );
			if ( $this->get_image_setting( 'preserve_image_classes' ) && ! empty( $class ) ) {
				$attr['class'] = "attachment-{$preset} {$img->attr( 'class' )}";
			}

			$alt_text = $img->attr( 'alt' );
			tmsc_notice( 'Alt text:' . $alt_text );
			if ( $this->get_image_setting( 'preserve_alt_text' ) && ! empty( $alt_text ) ) {
				update_post_meta( $attachment->get_post()->ID, '_wp_attachment_image_alt', $alt_text );
			}

			$new_img = wp_get_attachment_image( $attachment->get_post()->ID, $preset, false, $attr );
			if ( ! empty( $new_img ) ) {
				$img->replaceWith( $new_img );
			} else {
				$img->remove();
			}
		}

		$this->object->post_content = $html->branch( 'body' )->innerHTML5();
		wp_update_post( $this->object );
	}

	/**
	 * Wraps HTML snippets in the appropriate code and doctype.
	 *
	 * @param string $html The HTML fragment to wrap.
	 *
	 * @access protected
	 * @return string The HTML fragment as a full HTML5 document.
	 */
	protected function wrap_html_snippet( $html ) {
		return <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
	</head>
	<body>{$html}</body>
</html>
HTML;
	}

	/**
	 * Gets image settings
	 * @return mixed
	 */
	public function get_image_setting( $option ) {
		$settings = $this->get_image_settings();
		return $settings[ $option ];
	}

	/**
	 * Rewrites image URLs. Good for making relative images absolute, etc.
	 * @return string
	 */
	public function rewrite_image_src( $src ) {
		return $src;
	}

	/**
	 * Gets all image settings
	 * @return array
	 */
	public function get_image_settings() {
		return $this->modify_image_settings( array(
			'make_first_image_featured' => false,
			'featured_image_callback' => function( $attachment ) { },
			'no_featured_image_callback' => function() { },
			'strip_caption_shortcodes' => false,
			'image_domains' => array(),
			'sizes' => array(
				'full' => 0,
				'thumbnail' => 150,
				'medium' => 300,
				'large' => 640,
			),
			'remove_params_from_url' => false,
			'attachment_class' => '\\TMSC\\Attachment',
			'preserve_image_classes' => false,
			'preserve_alt_text' => false,
			'title_src' => '',
			'caption_src' => '',
		) );
	}

	/**
	 * Determine if an image should be imported from the given domain
	 * @return boolean
	 */
	public function image_is_from_allowed_domain( $src ) {
		$src_domain = parse_url( $src, PHP_URL_HOST );
		$domains = $this->get_image_setting( 'image_domains' );
		foreach ( $domains as $domain_pattern ) {
			if ( fnmatch( $domain_pattern, $src_domain ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Modifies image settings
	 * @return array
	 */
	public function modify_image_settings( $settings ) {
		return $settings;
	}

	/**
	 * Create a stub post with a legacy ID that can be migrated fully later
	 * @param string $type, post type
	 * @param int|string $legacy_id
	 * @param string $title, generated from ID
	 * @param array $extra, any other post properties to set
	 * @return post object
	 */
	function get_or_create_stub_post( $type, $legacy_id, $title = '', $extra = array() ) {
		$post = tmsc_post_by_legacy_id( $legacy_id );
		if ( ! empty( $post ) ) {
			return $post;
		}

		$title = $title ? $title : $type . ' stub ' . $legacy_id;

		$post = array(
			'post_title' => $title,
			'post_status' => 'publish',
			'post_date' => date( 'Y-m-d H:i:s', $this->get_pubdate() ),
			'post_date_gmt' => get_gmt_from_date( date( 'Y-m-d H:i:s', $this->get_pubdate() ) ),
			'post_type' => $type,
		);

		tmsc_notice( sprintf( 'Saving stub of type %s and title %s', $type, $title ) );

		$post = array_merge( $post, $extra );
		$post_id = wp_insert_post( $post );

		// Add this post ID to the map so we don't create multiple stubs
		\TMSC\Util\Map::get( 'legacy_post_ids' )->map( $legacy_id, $post_id );

		// Mark this stub with minimal postmeta so TMSC can grab it later, or delete it using the current processor
		update_post_meta( $post_id, 'tmsc_source', (string) $this->processor );
		update_post_meta( $post_id, 'tmsc_legacy_id', $legacy_id );
		update_post_meta( $post_id, 'tmsc_stub', '1' );
		return get_post( $post_id );
	}

}
