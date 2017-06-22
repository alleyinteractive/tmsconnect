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
		if ( ! empty( $this->raw->ObjectID ) ) {
			return $this->raw->ObjectID;
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
		return 'tms_object';
	}

	/**
	 * Get post status
	 * @return string
	 */
	public function get_post_status() {
		return 'publish';
	}

	/**
	 * Save the final post status
	 * @return string
	 */
	public function save_final_object_status() {
		\TMSC\Util\Map::get( 'legacy_post_ids' )->map( $this->get_legacy_id(), $this->object->ID );
		$this->object->post_status = $this->get_post_status();
		$this->update( $this->object );
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
	 * Load an existing post if it exists.
	 */
	public function load_existing_object() {
		$this->object = null;
		// Check for existing post by legacy ID
		$legacy_id = $this->get_legacy_id();
		if ( ! empty( $legacy_id ) ) {
			$existing_post = tmsc_get_object_by_legacy_id( $legacy_id );
			if ( ! empty( $existing_post ) ) {
				$this->object = $existing_post;
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

		$this->load_existing_object();

		if ( $this->requires_update() ) {
			$this->object = $this->save_post();

			if ( empty( $this->object->ID ) ) {
				return false;
			}

			// Save term relationships

			// Save Media Attachments

			// Update all post meta with queue flush.

			// Update status.

			$this->after_save();

			return true;
		}
		return false;
	}

	/**
	 * Save post
	 * @return WP_Object Object
	 */
	public function save_post() {
		
		$post = array(
			'ID' => empty( $this->object->ID ) ? 0 : $this->object->ID,
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
			'comment_status' => 'closed',
		);
		$post_id = wp_insert_post( $post );
		if ( ! empty( $post_id ) ) {
			return get_post( $post_id );
		}
		return false;
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
}
