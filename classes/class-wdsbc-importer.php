<?php
/**
 * Created by PhpStorm.
 * User: tw2113
 * Date: 1/2/18
 * Time: 11:09 PM
 */

class wdsBC_Importer extends WP_Background_Process {

	protected $action = 'wdsbc_process';

	protected $store_details = array();

	public function __construct( $store_details = array() ) {
		parent::__construct();
		$this->store_details = $store_details;
	}

	protected function task( $item ) {
error_log('start thing');
		$product_name = $product_description = $product_id = $product_price = $product_link = $product_image = '';

		// Get the post status.
		$product_status = ( 'sold-out' === $item->status ) ? 'private' : 'publish';

		// Format the date so we can set the post date as the product creation date.
		$product_publish_date = date( 'Y-m-d H:i:s', strtotime( $item->created_at ) );

		// Set some other variables in place.
		if ( isset( $item->id ) ) {
			$product_id = intval( $item->id );
		}
		if ( isset( $item->name ) ) {
			$product_name = esc_html( $item->name );
		}
		if ( isset( $item->description ) ) {
			$product_description = wp_kses_post( $item->description );
		}
		if ( isset( $item->price ) ) {
			$product_price = intval( $item->price );
		}
		if ( isset( $item->permalink ) ) {
			$product_link = esc_url( 'http://' . $this->store_details['store_name'] . '.bigcartel.com/product/' . $item->permalink );
		}
		if ( isset( $item->images[0]->url ) ) {
			$product_image = esc_url( $item->images[0]->url );
		}

		// Get the category list.
		$product_category_list = array();
		foreach ( $item->categories as $item_category ) {
			// Build the array of attached product categories from BC.
			$product_category_list[] = $item_category->name;
		}
		$product_categories = implode( ', ', $product_category_list );

		// Setup the array for wp_insert_post.
		$my_post = array(
			'post_title'   => $product_name,
			'post_content' => $product_description,
			'post_status'  => $product_status,
			'post_author'  => 1,
			'post_date'    => $product_publish_date,
			'post_type'    => 'bc_import_products',
			'tax_input'    => array( 'product-categories' => array( $product_categories ) ),
		);

		$product_exists = get_page_by_title( $my_post['post_title'], 'OBJECT', 'bc_import_products' );

		if ( $product_exists instanceof WP_Post ) {
			$my_post['ID'] = intval( $product_exists->ID );
			$post_id       = wp_update_post( $my_post );
		} else {
			$post_id = wp_insert_post( $my_post );
		}

		$terms = array();
		foreach ( $item->categories as $item_category ) {
			$terms[] = $item_category->name;
		}

		// Attach the categories to the posts.
		wp_set_object_terms( $post_id, $terms, 'product-categories' );

		update_post_meta( $post_id, 'big_cartel_importer_id', $product_id );
		update_post_meta( $post_id, 'big_cartel_importer_price', $product_price );
		update_post_meta( $post_id, 'big_cartel_importer_link', $product_link );

		if ( isset( $product_image ) ) {
			$tmp = download_url( $product_image );

			$file_array['name']     = basename( $product_image );
			$file_array['tmp_name'] = $tmp;

			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			$attachments     = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment' ) );
			$existing_images = wp_list_pluck( $attachments, 'post_title' );
			$files = explode( '.', $file_array['name'] );
			$new_image       = array_shift( $files );

			if ( ! in_array( $new_image, $existing_images, true ) ) {
				$thumbnail_id = media_handle_sideload( $file_array, $post_id );

				set_post_thumbnail( $post_id, $thumbnail_id );
			}
		}
		error_log( 'thing' );
		return false;
	}

	protected function complete() {
		parent::complete();

		error_log('what?');
	}

}
