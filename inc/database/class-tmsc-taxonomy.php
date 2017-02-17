<?php

namespace TMSC\Database;

/**
 * Base class for any imported post
 */
abstract class TMSC_Taxonomy extends \TMSC\Database\Migrateable {

	/**
	 * Terms for the current taxonomy.
	 * Implementations of get_terms can store here to avoid
	 * having to re-query if other functions need this data.
	 * @var array
	 */
	protected $terms = null;

	/**
	 * The type of migrateable taxonomy. Must be set by all implementing classes.
	 * @var string
	 */
	public $taxonomy = '';

	/**
	 * Constructor. Set this as a term migrateable.
	 */
	public function __construct() {
		parent::__construct( 'term' );
	}

	/**
	 * Get terms
	 * @return associative array, like array( 'category' => array( 'News', 'Sports' ), 'post_tag' => array( 'Football', 'Jets' ) )
	 */
	abstract public function get_terms();

	/**
	 * Get term slug
	 * @return string post slug
	 */
	public function get_term_name() {}

	/**
	 * Get term parent
	 * @return integer parent term id
	 */
	public function get_term_parent() {}

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
	 * Get term object
	 * @return WP_Term
	 */
	public function get_term() {
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
	 * Update the term (used in after_save usually)
	 */
	public function update() {
		$args = array(
			'name' => $this->object->name,
			'description' => $this->object->description,
		);
		wp_update_term( $this->object->term_id, $this->object->taxonomy, $args );
	}

	/**
	 * Delete the term
	 */
	public function delete() {
		if ( ! empty( $this->object->term_id ) ) {
			wp_delete_term( $this->object->term_id, $this->object->taxonomy );
		}
	}

	/**
	 * Load an existing post if it exists.
	 */
	public function load_existing_post() {
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
