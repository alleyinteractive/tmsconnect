<?php

namespace TMSC\Database;

/**
 * Base class for any generically imported taxonomy
 */
class TMSC_Taxonomy extends \TMSC\Database\Migrateable {

	/**
	 * Parent Terms for the current taxonomy.
	 * Implementations of get_terms can store here to avoid
	 * having to re-query if other functions need this data.
	 * @var array
	 *
	 */
	protected $parents = array();

	/**
	 * The type of migrateable taxonomy.
	 * @var string
	 *
	 */
	public $taxonomy;

	/**
	 * The type of migrateable object. Must be set by all implementing classes.
	 * @var string
	 */
	public $type = 'term';

	/**
	 * Constructor. Set this as a term migrateable.
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
		if ( ! empty( $this->raw->TermID ) || 0 === $this->raw->TermID || '0' === $this->raw->TermID ) {
			return ( 0 === $this->raw->TermID || '0' === $this->raw->TermID ) ? '0' : $this->raw->TermID;
		}
		return false;
	}

	/**
	 * Set our current taxonomy.
	 * @return void
	 *
	 */
	public function set_current_taxonomy() {
		if ( ! empty( $this->raw->taxonomy ) ) {
			$this->taxonomy = $this->raw->taxonomy;
		} else {
			$this->taxonomy = '';
		}
	}

	/**
	 * Load an existing taxonomy if it exists.
	 */
	public function load_existing() {
		$this->object = null;
		// Check for existing post by legacy ID
		$legacy_id = $this->get_legacy_id();
		if ( ( ! empty( $legacy_id ) || '0' === $legacy_id ) && ! empty( $this->taxonomy ) ) {
			$existing_term = tmsc_get_term_by_legacy_id( $legacy_id, $this->taxonomy );
			if ( ! empty( $existing_term ) ) {
				$this->object = $existing_term;
				if ( ! empty( $this->raw->Children ) && ! empty( $this->raw->CN ) ) {
					$this->parents[ $this->raw->CN ] = $this->object->term_id;
				}
			}
		}
	}

	/**
	 * Save this term
	 * @return boolean true if successfully saved
	 */
	public function save() {
		if ( $this->before_save() ) {
			return;
		}
		$this->set_current_taxonomy();
		$this->load_existing();

		if ( $this->requires_update() ) {
			$this->object = $this->save_term();

			if ( empty( $this->object->term_id ) ) {
				return false;
			} else {
				if ( ! empty( $this->raw->Children ) && ! empty( $this->raw->CN ) ) {
					$this->parents[ $this->raw->CN ] = $this->object->term_id;
				}
			}

			// Save term relationships
			$this->save_related_terms();

			$this->after_save();

			return true;
		}
		return false;
	}

	/**
	 * Save term
	 * @return WP_Term Object
	 */
	public function save_term() {

		$args = array(
			'parent' => $this->get_term_parent(),
		);

		if ( ! empty( $this->object->term_id ) ) {
			$term_id = $this->object->term_id;
			// Pass this the parameters of wp_update_term.
			// This will stash it in the stmt queue for bulk processing.
			$this->update( $this->object->term_id, $this->taxonomy, $args );
		} else {
			// Since we require a return value, we call the WP insert stmt here directly.
			$return = wp_insert_term( $this->raw->Term, $this->taxonomy, $args );
			if ( is_wp_error( $return ) ) {
				// This should not really fire once data has been properly loaded.
				// Adding it in to rewrite incorrect data during testing phase.
				if ( ! empty( $return->error_data['term_exists'] ) ) {
					$term_id = $return->error_data['term_exists'];
					$this->update( $term_id, $this->taxonomy, $args );
				} else {
					return false;
				}
			} else {
				$term_id = $return['term_id'];
			}
		}

		// Return a pseudo term object
		return (object) array( 'taxonomy' => $this->taxonomy, 'term_id' => $term_id );
	}


	/**
	 * Save additional terms that might be related to the associated terms such as search term equivalents.
	 * @return void
	 */
	public function save_related_terms() {
		if ( ! empty( $this->object->term_id ) && ! empty( $this->raw->CN ) ) {
			$terms = $this->processor->get_related_terms( $this->raw->CN );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $related_term ) {
					$return = wp_insert_term( $related_term->Term, $related_term->taxonomy );
					if ( is_wp_error( $return ) ) {
						// This should not really fire once data has been properly loaded.
						// Adding it in to rewrite incorrect data during testing phase.
						if ( ! empty( $return->error_data['term_exists'] ) ) {
							$term_id = $return->error_data['term_exists'];
							$this->update( $term_id, $this->taxonomy, $args );
						} else {
							return false;
						}
					} else {
						$term_id = $return['term_id'];
					}
					$this->stmt_queue[] = array( 'update_term_meta', array( $term_id, 'tmsc_legacy_CN', $this->raw->CN ) );
					$this->stmt_queue[] = array( 'update_term_meta', array( $term_id, 'tmsc_legacy_id', $related_term->TermID ) );
					$this->stmt_queue[] = array( 'update_term_meta', array( $term_id, 'tmsc_related_term_id', $this->object->term_id ) );
				}
			}
		}
	}

	/**
	 * Get the parent CN and term ID
	 * @return int
	 */
	public function get_term_parent() {
		$parent_cn = $this->get_parent_cn();
		if ( array_key_exists( $parent_cn, $this->parents ) ) {
			return $this->parents[ $parent_cn ];
		}
		return 0;
	}

	/**
	 * Get the parent CN of the current object.
	 * @return string
	 */
	public function get_parent_cn() {
		if ( ! empty( $this->raw->CN ) ) {
			$array_cn = explode( '.',  $this->raw->CN );
			array_pop( $array_cn );
			return implode( '.', $array_cn );
		}
		return '';
	}
}
