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
		// See if this attachment has a filename attribute.
		$filename = get_post_meta( $attachment_id, 'tms_media_filename', true );
		if ( ! empty( $filename ) ) {
			// Get the Image Delivery System URL.
			$image_delivery_system_url = get_option( 'tmsc-image-url' );

			// Create the new image source URL.
			$image_src = $image_delivery_system_url . '/' . $filename;

			// Add the new image src to the existing image.
			if ( is_array( $image ) && ! empty( $image[0] ) && is_string( $image[0] ) ) {
				$image[0] = $image_src;
			}
		}

		return $image;
	}
}

Media_URLs::get_instance();
