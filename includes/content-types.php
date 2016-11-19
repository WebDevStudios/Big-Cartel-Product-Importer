<?php
function wdsbc_register_content_types() {

	register_post_type( 'bc_import_products', array(
		'labels'             => array(
			'name'               => _x( 'Products', 'Post type general name', 'wdsbc' ),
			'singular_name'      => _x( 'Product', 'Post type singular name', 'wdsbc' ),
			'add_new'            => __( 'Add New', 'wdsbc' ),
			'add_new_item'       => __( 'Add New Product', 'wdsbc' ),
			'edit_item'          => __( 'Edit Product', 'wdsbc' ),
			'new_item'           => __( 'New Product', 'wdsbc' ),
			'all_items'          => __( 'All Products', 'wdsbc' ),
			'view_item'          => __( 'View Product', 'wdsbc' ),
			'search_items'       => __( 'Search Products', 'wdsbc' ),
			'not_found'          => __( 'No Products found', 'wdsbc' ),
			'not_found_in_trash' => __( 'No Products found in Trash', 'wdsbc' ),
			'menu_name'          => __( 'Products', 'wdsbc' ),
		),
		'hierarchical'       => false,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'products' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'supports'           => array( 'title', 'editor', 'thumbnail' ),
	) );

	register_taxonomy( 'product-categories', 'bc_import_products', array(
		'labels'            => array(
			'name'                       => _x( 'Product Categories', 'Taxonomy general name', 'wdsbc' ),
			'singular_name'              => _x( 'Product Category', 'Taxonomy singular name', 'wdsbc' ),
			'search_items'               => __( 'Search Product Categories', 'wdsbc' ),
			'popular_items'              => __( 'Common Product Categories', 'wdsbc' ),
			'all_items'                  => __( 'All Product Categories', 'wdsbc' ),
			'edit_item'                  => __( 'Edit Product Category', 'wdsbc' ),
			'update_item'                => __( 'Update Product Category', 'wdsbc' ),
			'add_new_item'               => __( 'Add New Product Category', 'wdsbc' ),
			'new_item_name'              => __( 'New Product Category Name', 'wdsbc' ),
			'separate_items_with_commas' => __( 'Separate Product Categories with commas', 'wdsbc' ),
			'add_or_remove_items'        => __( 'Add or remove Product Categories', 'wdsbc' ),
			'choose_from_most_used'      => __( 'Choose from the most used Product Categories', 'wdsbc' ),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'product-categories' ),
	) );
}
add_action( 'init', 'wdsbc_register_content_types' );
