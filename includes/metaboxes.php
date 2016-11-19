<?php

function wdsbc_ge_metabox_config() {
	return array(
		'id'       => 'big-cartel-metabox',
		'title'    => esc_html__( 'Product Information', 'big-cartel-product-importer' ),
		'page'     => 'bc_import_products',
		'context'  => 'normal',
		'priority' => 'high',
		'fields'   => array(
			array(
				'name' => esc_html__( 'ID', 'big-cartel-product-importer' ),
				'desc' => esc_html__( 'Big Cartel product ID number.', 'big-cartel-product-importer' ),
				'id'   => 'big_cartel_importer_id',
				'type' => 'text',
				'std'  => '',
			),
			array(
				'name' => esc_html__( 'Price', 'big-cartel-product-importer' ),
				'desc' => esc_html__( 'Enter the price of the product without a dollar sign.', 'big-cartel-product-importer' ),
				'id'   => 'big_cartel_importer_price',
				'type' => 'text',
				'std'  => '',
			),
			array(
				'name' => esc_html__( 'Big Cartel URL', 'big-cartel-product-importer' ),
				'desc' => esc_html__( 'The URL for the product in your Big Cartel store.', 'big-cartel-product-importer' ),
				'id'   => 'big_cartel_importer_link',
				'type' => 'text',
				'std'  => '',
			),
		),
	);
}

/**
 * Add the meta box.
 */
function wdsbc_add_meta_box() {
	$metabox_settings = wdsbc_ge_metabox_config();
	add_meta_box(
		$metabox_settings['id'],
		$metabox_settings['title'],
		'wdsbc_metabox_fields',
		$metabox_settings['page'],
		$metabox_settings['context'],
		$metabox_settings['priority']
	);
}
add_action( 'admin_menu', 'wdsbc_add_meta_box' );

function wdsbc_metabox_fields() {
	global $post;

	wp_nonce_field( 'big_cartel_importer_nonce', 'big-cartel-importer' );

	$metabox_settings = wdsbc_ge_metabox_config();
	// Display it all!
	echo '<table class="form-table">';
	$row_template = '<tr><th style="width:20%%"><label for="%s">%s</label></th><td><input type="text" name="%s" id="%s" value="%s" size="30" style="width:97%%" /><br />%s</td></tr>';
	foreach ( $metabox_settings['fields'] as $field ) {
		$meta = get_post_meta( $post->ID, $field['id'], true );
		$meta = ( $meta ) ? $meta : $field['std'];
		printf(
			$row_template,
			$field['id'],
			$field['name'],
			$field['id'],
			$field['id'],
			$meta,
			$field['desc']
		);
	}
	echo '</table>';
}

function wdsbc_save_post( $post_id ) {

	if ( ! isset( $_POST['big-cartel-importer'] ) || ! wp_verify_nonce( $_POST['big-cartel-importer'], 'big_cartel_importer_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'page' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$metabox_settings = wdsbc_ge_metabox_config();

	foreach ( $metabox_settings['fields'] as $field ) {
		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new !== $old ) {
			update_post_meta( $post_id, $field['id'], $new );
		} elseif ( '' === $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
add_action( 'save_post', 'wdsbc_save_post' );
