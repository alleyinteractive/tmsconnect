<?php
/**
 * This class contains all logic related to filtering media asset source URLs.
 *
 * @package TMSC
 */

/**
 * Need a function to hook into `wp_attachment_image_src` or `image_downsize`
 * to check that if a media asset has a meta field of `tms_media_filename` to use
 * that field as the image URL.
 */

namespace TMSC;

class Media_URLs {
	use Singleton;

	/**
	 * Setup the class.
	 */
	public function setup() {
		add_filter( 'wp_get_attachment_image_src', [ $this, 'filter_image_source' ], 10, 4 );
		add_filter( 'image_downsize', [ $this, 'filter_image_downsize' ], 10, 3 );
		add_filter( 'load_image_to_edit_path', [ $this, 'filter_edit_image_path' ], 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'filter_attachment_js' ], 10, 3 );
	}

	/**
	 * Get a custom image source based on whether or not the attachment has
	 * `tms_media_filename` set.
	 *
	 * @param  int         $attachment_id The attachment ID.
	 * @return string|bool                False if there is an error, otherwise the new URL.
	 */
	public function get_custom_image_src( int $attachment_id ) {
		$new_url = false;

		// See if this attachment has a filename attribute.
		$filename = get_post_meta( $attachment_id, 'tms_media_filename', true );
		if ( ! empty( $filename ) ) {
			// Get the Image Delivery System URL.
			$image_delivery_system_url = get_option( 'tmsc-image-url' );

			// Make sure we have a valid source URL.
			if ( ! empty( $image_delivery_system_url ) && filter_var( $image_delivery_system_url, FILTER_VALIDATE_URL ) ) {
				// Create the new image source URL.
				$new_url = add_query_arg( 'id', $filename, $image_delivery_system_url );
			}
		}

		return $new_url;
	}

	/**
	 * Filter the media source URL.
	 *
	 * @param  array|false  $image         Either array with src, width & height, icon src, or false.
	 * @param  int          $attachment_id Image attachment ID.
	 * @param  string|array $size          Size of image. Image size or array of width and height values
	 *                                     (in that order). Default 'thumbnail'.
	 * @param  bool         $icon          Whether the image should be treated as an icon. Default false.
	 * @return array|false  $image         Either array with src, width & height, icon src, or false.
	 */
	public function filter_image_source( $image, $attachment_id, $size, $icon ) {
		// Attempt to get the custom URL.
		$new_url = $this->get_custom_image_src( $attachment_id );

		// Add the new image src to the existing image.
		if (
			! empty( $new_url )
			&& is_array( $image )
			&& ! empty( $image[0] )
			&& is_string( $image[0] )
		) {
			$image[0] = $new_url;
		}

		return $image;
	}

	/**
	 * Filter the image downsize array to return custom image source URLs.
	 *
	 * @param  bool         $downsize Whether to short-circuit the image downsize. Default false.
	 * @param  int          $id       Attachment ID for image.
	 * @param  array|string $size     Size of image. Image size or array of width and height values (in that order).
	 *                                Default 'medium'.
	 * @return array|bool   $image    The custom image.
	 */
	public function filter_image_downsize( $downsize, $id, $size ) {
		// Attempt to get the custom URL.
		$new_url = $this->get_custom_image_src( $id );

		/**
		 * If we have a custom URL then get all of the other attributes from
		 * `image_downsize` and just alter the URL.
		 */
		if ( ! empty( $new_url ) ) {

			remove_filter( 'image_downsize', [ $this, 'filter_image_downsize' ], 10, 3 );

			$image = image_downsize( $id, $size );

			// Add the new image src to the existing image.
			if (
				! empty( $new_url )
				&& is_array( $image )
				&& ! empty( $image[0] )
				&& is_string( $image[0] )
			) {
				$image[0] = $new_url;
			}

			add_filter( 'image_downsize', [ $this, 'filter_image_downsize' ], 10, 3 );

			return $image;
		}

		return $downsize;
	}

	/**
	 * Filter the image edit path.
	 *
	 * @param  string|bool $filepath      File path or URL to current image, or false.
	 * @param  string      $attachment_id Attachment ID.
	 * @param  string      $size          Size of the image.
	 * @return string|bool $filepath      File path or URL to current image, or false.
	 */
	public function filter_edit_image_path( $filepath, $attachment_id, $size ) {
		// Attempt to get the custom URL.
		$new_url = $this->get_custom_image_src( $attachment_id );

		// Add the new image src to the existing image.
		if ( ! empty( $new_url ) ) {
			$filepath = $new_url;
		}

		return $filepath;
	}

	/**
	 * Filter the attachment JS sizes array to only return a single size if using
	 * a custom URL.
	 *
	 * @param  array      $response   Array of prepared attachment data.
	 * @param  int|object $attachment Attachment ID or object.
	 * @param  array      $meta       Array of attachment meta data.
	 * @return array      $response   Array of prepared attachment data.
	 */
	public function filter_attachment_js( $response, $attachment, $meta ) {
		if ( $attachment instanceof \WP_Post ) {
			$attachment_id = $attachment->ID;
		}

		if ( ! empty( $attachment_id ) && is_int( $attachment_id ) ) {
			// Attempt to get the custom URL.
			$new_url = $this->get_custom_image_src( $attachment_id );

			// Add the new image src to the existing image.
			if ( ! empty( $new_url ) && ! empty( $response['sizes'] ) ) {
				// Get the full size array.
				$full = $response['sizes']['full'];

				// Update the URL.
				$full['url'] = $new_url;

				// Make sure to only return this full size array to ensure the
				// custom image size is used.
				$response['sizes'] = [];
				$response['sizes']['full'] = $full;
			}
		}

		return $response;
	}
}

Media_URLs::get_instance();
