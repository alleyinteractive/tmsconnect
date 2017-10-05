<?php

namespace TMSC\Database;

/**
 * Base class for any imported post
 */
class TMSC_Object extends \TMSC\Database\Migrateable {

	/**
	 * The type of migrateable object. Must be set by all implementing classes.
	 * @var string
	 */
	public $type = 'post';

	/**
	 * The post type used with this migratable.
	 */
	public $post_type = '';

	/**
	 * Constructor. Set this as a post migrateable.
	 */
	public function __construct() {
		$this->post_type = $this->get_post_type();
		parent::__construct( $this->type );
	}

	/**
	 * Get legacy ID
	 * @return int
	 *
	 */
	public function get_legacy_id() {
		if ( ! empty( $this->raw->ID ) || 0 === $this->raw->ID || '0' === $this->raw->ID ) {
			return ( 0 === $this->raw->ID || '0' === $this->raw->ID ) ? '0' : $this->raw->ID;
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
	public function get_excerpt(){
		return apply_filters( "tmsc_set_{$this->name}_excerpt", '', $this->raw );
	}

	/**
	 * Get title
	 * @return string
	 */
	public function get_title(){
		$title = ( ! empty( $this->raw->Title ) ) ? $this->raw->Title : '';
		return apply_filters( "tmsc_set_{$this->name}_title", $title, $this->raw );
	}

	/**
	 * Get post author.
	 * @return int ID of author user.
	 */
	public function get_post_author() {
		// Use the admin user by default
		return apply_filters( "tmsc_set_{$this->name}_author", 1, $this->raw );
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
		$content = apply_filters( "tmsc_set_{$this->name}_body", '', $this->raw );
		return ( empty( $content ) ) ? '' : $content;
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
		return 0;
	}

	/**
	 * Get post type
	 * @return string
	 */
	public function get_post_type() {
		return apply_filters( "tmsc_set_{$this->name}_post_type", $this->post_type );
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
		$this->object->post_status = $this->get_post_status();
		$this->update();
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
		$this->children = null;
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

			// Save related_objects
			$this->save_related_objects();

			// Save Media Attachments
			$this->save_media_attachments();

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

	public function after_save() {
		$this->migrate_children();
		parent::after_save();
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
		if ( ! empty( $this->object->ID ) && ! empty( $this->raw->ID ) ) {
			$terms = $this->processor->get_related_terms( $this->raw->ID );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $taxonomy => $term_ids ) {
					wp_set_object_terms( $this->object->ID, $term_ids, $taxonomy );
				}
			}
		}
	}

	/**
	 * Save related objects.
	 */
	public function save_related_objects() {
		if ( ! empty( $this->object->ID ) && ! empty( $this->raw->ID ) ) {
			// Store with migratable type as key.
			$related_ids = $this->processor->get_related_objects( $this->raw->ID );
			if ( ! empty( $related_ids ) ) {
				$this->update_meta( 'tmsc_post_processing', $related_ids );
			}
		}
	}

	/**
	 * Save media attachments.
	 */
	public function save_media_attachments() {
		$this->children['Media'] = null;
		if ( ! empty( $this->object->ID ) && ! empty( $this->raw->ID ) ) {
			$this->raw->wp_parent_id = $this->object->ID;
			// Store with migratable type as key.
			$this->children['Media'] = $this->raw;
		}
	}

	/**
	 * Save children migratables
	 * This migratable expects objects and media as children.
	 */
	public function migrate_children(){
		if ( ! empty( $this->children ) ) {
			foreach( $this->children as $migratable_type => $raw_data ) {
				if ( ! empty( $raw_data ) ) {
					$child_processor = \TMSC\TMSC::instance()->get_processor( $migratable_type );
					$child_processor->set_parent_object( $raw_data );

					if ( ! empty( $child_processor->get_object_query_stmt() ) ) {
						$child_processor->run();
					}
				}
			}
			return true;
		}
		return false;
	}
}
