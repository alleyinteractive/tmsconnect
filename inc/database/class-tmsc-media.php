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
		return apply_filters( 'tmsc_set_object_title', $title, $this->raw );
	}

	/**
	 * Get date of publication
	 * @return int unix timestamp
	 */
	public function get_pubdate(){
		return apply_filters( 'tmsc_set_object_pubdate', time(), $this->raw );
	}

	/**
	 * Get body
	 * @return HTML
	 */
	public function get_body(){
		return apply_filters( 'tmsc_set_object_body', '', $this->raw );
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
		return ( empty( $this->post_parent ) ) ? 0 : $this->post_parent;
	}

	/**
	 * Get post type
	 * @return string
	 */
	public function get_post_type() {
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
			/*
		$this->before_save();

		$this->load_existing();
		if ( $this->requires_update() ) {

			$this->object = $this->add_attachment();

			if ( empty( $this->object->ID ) ) {
				return false;
			}

			// Update queue with post meta.
			$this->save_meta_data();

			// Save Media Attachments
			$this->save_media_attachments();

			// Update status.
			$this->after_save();

			return true;
		}
		*/
		return false;
	}

	/**
	 * Save post
	 * @return WP_Object Object
	 */
	public function save_post() {

		// $filename should be the path to a file in the upload directory.
		$filename = $;

		// The ID of the post this attachment is for.
		$parent_post_id = 37;

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );


		$date = date( 'Y-m-d H:i:s', $this->get_pubdate() );
		$post = array(
			'ID' => empty( $this->object->ID ) ? 0 : $this->object->ID,
			'post_title' => $this->get_title(),
			'post_status' => 'migrating',
			'post_author' => $this->get_post_author(),
			'post_date' => $date,
			'post_date_gmt' => get_gmt_from_date( $date ),
			'post_type' => $this->get_post_type(),
			'post_content' => $this->get_body(),
			'post_excerpt' => $this->get_excerpt(),
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
		return apply_filters( 'tmsc_media_meta_keys', array() );
	}

	/**
	 * Save media attachments.
	 */
	public function save_media_attachments() {
		if ( ! empty( $this->object->ID ) && ! empty( $this->raw->ObjectID ) ) {
			$attachments = $this->processor->get_object_attachments( $this->raw->ObjectID );
			foreach ( $attachments as $attachment_raw_data ) {
				$this->add_attachment( $attachment_raw_data, $this->object->ID );
			}
		}
	}


	/**
	 * Create or update an attachment
	 * @return object. WP_Attachment
	 */
	public function add_attachment( $data, $object_id ) {

		// $filename should be the path to a file in the upload directory.
		$filename = '/path/to/uploads/2013/03/filename.jpg';

		// The ID of the post this attachment is for.
		$parent_post_id = 37;

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

	}
}
