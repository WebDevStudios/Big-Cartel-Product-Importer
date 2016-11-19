<?php
function wdsbc_register_content_types() {

	register_post_type( 'bc_import_products', array(
		'labels'             => array(
			'name'               => _x( 'Products', 'Post type general name', 'big-cartel-product-importer' ),
			'singular_name'      => _x( 'Product', 'Post type singular name', 'big-cartel-product-importer' ),
			'add_new'            => __( 'Add New', 'big-cartel-product-importer' ),
			'add_new_item'       => __( 'Add New Product', 'big-cartel-product-importer' ),
			'edit_item'          => __( 'Edit Product', 'big-cartel-product-importer' ),
			'new_item'           => __( 'New Product', 'big-cartel-product-importer' ),
			'all_items'          => __( 'All Products', 'big-cartel-product-importer' ),
			'view_item'          => __( 'View Product', 'big-cartel-product-importer' ),
			'search_items'       => __( 'Search Products', 'big-cartel-product-importer' ),
			'not_found'          => __( 'No Products found', 'big-cartel-product-importer' ),
			'not_found_in_trash' => __( 'No Products found in Trash', 'big-cartel-product-importer' ),
			'menu_name'          => __( 'Products', 'big-cartel-product-importer' ),
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
			'name'                       => _x( 'Product Categories', 'Taxonomy general name', 'big-cartel-product-importer' ),
			'singular_name'              => _x( 'Product Category', 'Taxonomy singular name', 'big-cartel-product-importer' ),
			'search_items'               => __( 'Search Product Categories', 'big-cartel-product-importer' ),
			'popular_items'              => __( 'Common Product Categories', 'big-cartel-product-importer' ),
			'all_items'                  => __( 'All Product Categories', 'big-cartel-product-importer' ),
			'edit_item'                  => __( 'Edit Product Category', 'big-cartel-product-importer' ),
			'update_item'                => __( 'Update Product Category', 'big-cartel-product-importer' ),
			'add_new_item'               => __( 'Add New Product Category', 'big-cartel-product-importer' ),
			'new_item_name'              => __( 'New Product Category Name', 'big-cartel-product-importer' ),
			'separate_items_with_commas' => __( 'Separate Product Categories with commas', 'big-cartel-product-importer' ),
			'add_or_remove_items'        => __( 'Add or remove Product Categories', 'big-cartel-product-importer' ),
			'choose_from_most_used'      => __( 'Choose from the most used Product Categories', 'big-cartel-product-importer' ),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'product-categories' ),
	) );
}
add_action( 'init', 'wdsbc_register_content_types' );
