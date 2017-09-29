<?php

namespace TMSC\Database;

/**
 * Base class for any post attachment
 */
class TMSC_Media extends \TMSC\Database\Migrateable {

	/**
	 * The type of migrateable object. Must be set by all implementing classes.
	 * @var string
	 */
	public $type = 'post';

	/**
	 * Constructor. Set this as a post migrateable.
	 */
	public function __construct() {
		parent::__construct( $this->type );
	}

	/**
	 * Get legacy ID
	 * @return int
	 *
	 */
	public function get_legacy_id() {
		if ( ! empty( $this->raw->MediaMasterID ) ) {
			return $this->raw->MediaMasterID;
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
	 * Get title
	 * @return string
	 */
	public function get_title(){
		$title = ( empty( $this->raw->Title ) ) ? $this->raw->RawTitle : $this->raw->Title;
		return apply_filters( "tmsc_set_{$this->name}_title", $title, $this->raw );
	}

	/**
	 * Get date of publication
	 * @return int unix timestamp
	 */
	public function get_pubdate(){
		return apply_filters( "tmsc_set_{$this->name}_pubdate", time(), $this->raw );
	}

	/**
	 * Get body
	 * @return HTML
	 */
	public function get_body(){
		return apply_filters( "tmsc_set_{$this->name}_body", '', $this->raw );
	}

	/**
	 * Get post slug
	 * @return string post slug
	 */
	public function get_post_name() {
		return sanitize_title_with_dashes( $this->get_title() );
	}

	/**
	 * Get post parent
	 * @return integer parent post id
	 */
	public function get_post_parent() {
		return ( empty( $this->raw->WPParentID ) ) ? 0 : $this->raw->WPParentID;
	}

	/**
	 * Get post type
	 * @return string
	 */
	public function get_post_type() {
		// No hook here as this much be an attachment.
		return 'attachment';
	}

	/**
	 * Get post status
	 * @return string
	 */
	public function get_post_status() {
		return 'inherit';
	}

	/**
	 * Save the final post status
	 * @return string
	 */
	public function save_final_object_status() {
		return true;
	}

	/**
	 * Get post object
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->get_object();
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
	public function load_existing() {
		$this->object = null;
		// Check for existing post by legacy ID
		$legacy_id = $this->get_legacy_id();
		if ( ! empty( $legacy_id ) ) {
			$existing_post = tmsc_get_object_by_legacy_id( $legacy_id, $this->get_post_type() );
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

		$this->before_save();

		$this->load_existing();
		if ( $this->requires_update() ) {

			$this->object = $this->save_post();

			if ( empty( $this->object->ID ) ) {
				return false;
			}

			// Update queue with post meta.
			$this->save_meta_data();

			// Save term relationships
			$this->save_term_relationships();

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

		// $filename should be the path to a file in the upload directory.
		$filename = $this->raw->FileName;

		// The ID of the post this attachment is for.
		$parent_post_id = $this->raw->WPParentID;

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = 'image/jpeg/';

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();
		$guid_url = trailingslashit( get_option( 'tmsc-ids-image-url', $wp_upload_dir['baseurl'] ) );

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid' => add_query_arg( array( 'id' => $filename ), $guid_url ),
			'post_mime_type' => $filetype,
			'post_title' => $this->raw->Title,
			'post_content' => $this->raw->PublicCaption,
			'post_excerpt' => $this->raw->Description,
			'post_status' => 'inherit',
			'menu_order' => absint( $this->raw->Rank ),
		);

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
		if ( ! empty( $attach_id ) ) {
			if ( 1 === absint( $this->raw->PrimaryDisplay )  ) {
				set_post_thumbnail( $parent_post_id, $attach_id );
			}
			return get_post( $attach_id );
		}
		return false;
	}

	/**
	 * Save post meta data
	 * @return void
	 */
	public function save_meta_data() {
		if ( ! empty( $this->object->ID ) ) {
			// Get our meta data mapping and iterate through it.
			foreach ( $this->get_meta_keys() as $key => $db_field ) {
				$this->update_meta( $key, $this->raw->$db_field );
			}
		}
		return;
	}

	/**
	 * Map our raw data keys to our meta keys
	 * @return array. An array of post meta keys and corresponding db fields in our raw data.
	 */
	public function get_meta_keys() {
		return apply_filters( "tmsc_{$this->name}_meta_keys", array() );
	}

	/**
	 * Save object terms
	 * @return void
	 */
	public function save_term_relationships() {
		if ( ! empty( $this->object->ID ) && ! empty( $this->raw->ObjectID ) ) {
			$terms = $this->processor->get_related_terms( $this->raw->ObjectID );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $taxonomy => $term_ids ) {
					wp_set_object_terms( $this->object->ID, $term_ids, $taxonomy );
				}
			}
		}
	}
}
