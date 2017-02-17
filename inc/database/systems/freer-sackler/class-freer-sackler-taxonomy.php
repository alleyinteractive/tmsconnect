<?php

namespace TMSC\Database\Systems\Freer_Sackler;

/**
 * Base class for importing posts from Joomla
 */
class Freer_Sackler_Taxonomy extends \TMSC\Database\TMSC_Taxonomy {

	/**
	 * Constructor
	 * @param stdClass $content
	 * @param \TMSC\Database\Systems\Freer_Sackler $processor
	 */
	public function __construct( $content, $processor ) {
		$this->raw = $content;
		$this->processor = $processor;
		parent::__construct();
	}


	/**
	 * Get excerpt
	 * @access public
	 * @return html
	 */
	public function get_excerpt() {
		return $this->raw->introtext;
	}

	/**
	 * Get title
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return $this->raw->title;
	}

	/**
	 * Get post author.
	 * @return int ID of author user.
	 */
	public function get_post_author() {
		return 1;
	}

	/**
	 * Get authors.
	 * @access public
	 * @return array of names.
	 */
	public function get_authors() {
		return;
	}

	/**
	 * Get terms.
	 * Categories and tags have exact analogs in WordPress so migrate both by default.
	 * @access public
	 * @return associative array, like array( 'category' => array( 'News', 'Sports' ), 'post_tag' => array( 'Football', 'Jets' ) )
	 */
	public function get_terms() {
		// If this was called earlier and terms were already set, just use those.
		// The $terms variable defaults to null, so even an empty array indicates processing.
		if ( is_array( $this->terms ) ) {
			return $this->terms;
		}

		// Array to hold terms
		$terms = array();

		// Get the category (there should only be one)
		if ( ! empty( $this->raw->catid ) ) {
			$category_query = $this->processor->query( 'content_categories', array( ':id' => $this->raw->catid ) );
			$categories = $category_query->fetchAll();
			$category_query->closeCursor();

			if ( ! empty( $categories ) ) {
				$terms['category'] = array();
				foreach ( $categories as $category ) {
					$terms['category'][] = $category->title;
				}
			}
		}

		// Get the tags (there should only be one)
		if ( ! empty( $this->raw->id ) ) {
			$tag_query = $this->processor->query( 'content_tags', array( ':content_id' => $this->raw->id ) );
			$tags = $tag_query->fetchAll();
			$tag_query->closeCursor();

			if ( ! empty( $tags ) ) {
				$terms['post_tag'] = array();
				foreach ( $tags as $tag ) {
					$terms['post_tag'][] = $tag->description;
				}
			}
		}

		// Store the authors so other functions can access this data without re-querying
		$this->terms = $terms;

		return $terms;
	}

	/**
	 * Get date of publication
	 * @access public
	 * @return int unix timestamp
	 */
	public function get_pubdate() {
		return strtotime( $this->raw->publish_up );
	}

	/**
	 * Get body
	 * @access public
	 * @return HTML
	 */
	public function get_body() {
		return $this->raw->fulltext;
	}

	/**
	 * Get legacy URL
	 * @access public
	 * @return string
	 */
	public function get_legacy_url() {
		// Default to using site URL with the content alias since most Joomla sites handle this gracefully.
		// Any further URL customizations should fall to site-specific subclasses.
		return trailingslashit( trailingslashit( $this->processor->url ) . sanitize_title( $this->raw->alias ) );
	}

	/**
	 * Get legacy ID - must be GUID
	 * @access public
	 * @return string
	 */
	public function get_legacy_id() {
		return $this->raw->id;
	}

	/**
	 * Get post slug
	 * @access public
	 * @return string post slug
	 */
	function get_post_name() {
		return $this->raw->alias;
	}

	/**
	 * Get post parent
	 * @access public
	 * @return integer parent post id
	 */
	public function get_post_parent() {
		return $this->raw->parentid;
	}

	/**
	 * Get post type
	 * @access public
	 * @return string
	 */
	public function get_post_type() {
		return 'post';
	}

	/**
	 * Get post status
	 * @access public
	 * @return string
	 */
	public function get_post_status() {
		if ( 1 === intval( $this->raw->state ) ) {
			return 'publish';
		} else {
			return 'draft';
		}
	}

}
