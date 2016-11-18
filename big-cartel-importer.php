<?php
/*
Plugin Name: Big Cartel Product Importer
Plugin URI: http://www.webdevstudios.com
Description: Import your products from Big Cartel to a Product custom post type in WordPress.
Version: 1.0.2
Author: WebDevStudios
Author URI: http://www.webdevstudios.com
License: GPLv2
*/

/**
 * Enqueue some styles.
 */
function big_cartel_importer_styles() {
	wp_enqueue_style( 'big_cartel_settings_styles', plugins_url( '/big-cartel-importer/css/big-cartel-styles.css', dirname( __FILE__ ) ) );
}
add_action( 'init', 'big_cartel_importer_styles' );

/**
 * Class WDS_BC_Importer
 */
class WDS_BC_Importer {

	public $plugin_dir_path = '';

	public $options = array();

	public $store_name = '';

	/**
	 * BigCartel object.
	 *
	 * @since 1.0.0
	 * @var mixed
	 */
	public $bc_object = array();

	/**
	 * WDS_BC_Importer constructor.
	 */
	public function __construct() {

		// Setup all our necessary variables.
		$this->plugin_dir_path  = dirname( __FILE__ );
		$this->options          = get_option( 'big_cartel_importer_plugin_options', array() );

		if ( $this->options['store_name'] ) {
			$this->store_name = $this->options['store_name'];
		}

		$this->set_bigcartel_results();

		$this->metabox_settings = array(
			'id'       => 'big-cartel-metabox',
			'title'    => esc_html__( 'Product Information', 'wdsbc' ),
			'page'     => 'bc_import_products',
			'context'  => 'normal',
			'priority' => 'high',
			'fields'   => array(
				array(
					'name' => esc_html__( 'ID', 'wdsbc' ),
					'desc' => esc_html__( 'Big Cartel product ID number.', 'wdsbc' ),
					'id'   => 'big_cartel_importer_id',
					'type' => 'text',
					'std'  => '',
				),
				array(
					'name' => esc_html__( 'Price', 'wdsbc' ),
					'desc' => esc_html__( 'Enter the price of the product without a dollar sign.', 'wdsbc' ),
					'id'   => 'big_cartel_importer_price',
					'type' => 'text',
					'std'  => '',
				),
				array(
					'name' => esc_html__( 'Big Cartel URL', 'wdsbc' ),
					'desc' => esc_html__( 'The URL for the product in your Big Cartel store.', 'wdsbc' ),
					'id'   => 'big_cartel_importer_link',
					'type' => 'text',
					'std'  => '',
				),
			),
		);
	}

	/**
	 * Runs all our needed hooks.
	 *
	 * @since 1.1.0
	 */
	public function do_hooks() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_admin_settings' ) );
		add_action( 'admin_init', array( $this, 'process_settings_save' ) );
		add_action( 'admin_menu', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Sets our results array for import later.
	 *
	 * @since 1.1.0
	 */
	public function set_bigcartel_results() {
		$response = '';
		if ( ! empty( $this->store_name ) ) {
			// Set a URL to check if the store is in maintenance mode.
			$response = wp_remote_get( 'http://api.bigcartel.com/' . $this->store_name . '/products.js' );
		}

		// If status is OK, proceed.
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$this->bc_object = json_decode( wp_remote_retrieve_body( $response ) );
		}
	}

	/**
	 * Register our custom post type.
	 */
	public function register_post_types() {

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
	}

	/**
	 * Register our taxonomy.
	 */
	public function register_taxonomies() {

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
			)
		);

	}

	/**
	 * Add our menu items.
	 */
	public function admin_menu() {
		add_options_page( __( 'Big Cartel Importer', 'wdsbc' ), __( 'Big Cartel Importer', 'wdsbc' ), 'administrator', 'big-cartel-importer', array( $this, 'admin_page' ) );
	}

	/**
	 * Register settings and fields.
	 */
	public function register_admin_settings() {
		register_setting( 'big_cartel_importer_plugin_options', 'big_cartel_importer_plugin_options', array( $this, 'validate_settings' ) );
		add_settings_section( 'big_cartel_importer_main_options', '', '', 'big-cartel-importer' );
		add_settings_field( 'store_name', __( 'Big Cartel Store Name: ', 'wdsbc' ), array( $this, 'settings_store_name' ), 'big-cartel-importer', 'big_cartel_importer_main_options' );
	}

	/**
	 * Build the form fields.
	 */
	public function settings_store_name() {
		// Get the total post count.
		$count_posts = (array) wp_count_posts( 'bc_import_products' );
		$total_posts = array_sum( $count_posts );

		// Get the total term count.
		$count_terms = wp_count_terms( 'product-categories' );

		$options = get_option( 'big_cartel_importer_plugin_options' );
		?>
		<div class="input-wrap">
			<div class="left">
				<input name="big_cartel_importer_plugin_options[store_name]" style="width:30%;" type="text" value="<?php esc_attr_e( $options['store_name'] ); ?>" />
			</div>
			<div class='right'>
				<?php
				printf(
					esc_html__( 'If your store URL is: %s, enter %s in the text field.', 'wdsbc' ),
					'http://<strong>yourstorename</strong>.bigcartel.com',
					'<strong>yourstorename</strong>'
				);
				?>
			</div>
			<?php
			if ( empty( $this->bc_object ) ) {
				$message = esc_html__( 'Your store is currently in maintenance mode and can not have its products imported.', 'wdsbc' );

			} else {
				$message = sprintf(
					esc_html__( 'You have imported %s products in %s categories.', 'wdsbc' ),
					'<strong>' . esc_html( $total_posts ) . '</strong>',
					'<strong>' . esc_html( $count_terms ) . '</strong>'
				);
			}
			?>
			<span><?php echo $message; ?></span>
		</div>
	<?php
	}

	/**
	 * Sanitize the value.
	 *
	 * @todo Actually sanitize our return values.
	 *
	 * @param array $big_cartel_importer_plugin_options Array of options.
	 * @return array
	 */
	public function validate_settings( $big_cartel_importer_plugin_options ) {
		$big_cartel_importer_plugin_options['store_name'] = $this->parse_username( $big_cartel_importer_plugin_options['store_name'] );
		return $big_cartel_importer_plugin_options;
	}

	/**
	 * Build the admin page.
	 */
	public function admin_page() {
	?>
		<div id="theme-options-wrap">
			<div class="icon32" id="icon-tools"></div>
			<h2><?php esc_html_e( 'Big Cartel Importer Options', 'wdsbc' ); ?></h2>
			<p><?php esc_html_e( 'Set the URL of your Big Cartel store to pull in your products.', 'wdsbc' ); ?></p>
			<form id="options-form" method="post" action="options.php" enctype="multipart/form-data">
				<?php settings_fields( 'big_cartel_importer_plugin_options' ); ?>
				<?php do_settings_sections( 'big-cartel-importer' ); ?>
				<p class="submit"><input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Run Import', 'wdsbc' ); ?>" /></p>
			</form>
		</div>
	<?php
	}

	/**
	 * Output the post data and create our posts.
	 */
	public function import_products() {

		// Grab the JSON feed as an array.
		if ( isset( $this->bc_object ) && ! empty( $this->bc_object ) ) {
			// Get our store name.
			$this->store_name = sanitize_text_field( $_POST['big_cartel_importer_plugin_options']['store_name'] );

			foreach ( $this->bc_object as $item ) {

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
					$product_link = esc_url( 'http://'. $this->store_name .'.bigcartel.com/product/'. $item->permalink );
				}
				if ( isset( $item->images[0]->url ) ) {
					$product_image = esc_url( $item->images[0]->url );
				}

				// Get the category list.
				$product_category_list = array();
				foreach ( $item->categories as $item_category ) {
					// Build the array of attached product categories from BC.
					$product_category_list[] = $item_category->name;
					$category_name = $item_category->name;
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

				if ( ! get_page_by_title( $my_post['post_title'], 'OBJECT', 'bc_import_products' ) ) {

					// Insert the post into the database and set the post and term ID.
					$post_id = wp_insert_post( $my_post );

					// Get the list of categories attached to a product.
					$terms = array();
					foreach ( $item->categories as $item_category ) {
						$terms[] = $item_category->name;
					}

					// Attach the categories to the posts.
					wp_set_object_terms( $post_id, $terms, 'product-categories' );

					update_post_meta( $post_id, 'big_cartel_importer_id', $product_id );
					update_post_meta( $post_id, 'big_cartel_importer_price', $product_price );
					update_post_meta( $post_id, 'big_cartel_importer_link', $product_link );

					// This will import the images to the media library.
					if ( isset( $item->images[0]->url ) ) {
						$image_url  = esc_url( $item->images[0]->url );
						$upload_dir = wp_upload_dir();
						$image_data = file_get_contents( $image_url );
						$filename   = basename( $image_url );

						$file = $upload_dir['basedir'] . '/' . $filename;
						if ( wp_mkdir_p( $upload_dir['path'] ) ) {
						    $file = $upload_dir['path'] . '/' . $filename;
						}

						file_put_contents( $file, $image_data );

						// Now let's assign the image to the corresponding post.
						$wp_filetype = wp_check_filetype( $filename, null );

						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name( $filename ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);

						$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

						wp_update_attachment_metadata( $attach_id, $attach_data );

						set_post_thumbnail( $post_id, $attach_id );
					}
				} elseif ( $existing_post = get_page_by_title( $my_post['post_title'], 'OBJECT', 'bc_import_products' ) ) {

					// Insert the post into the database and set the post and term ID.
					$my_post['ID'] = intval( $existing_post->ID );
					$post_id = wp_update_post( $my_post );

					// Get the list of categories attached to a product.
					$terms = array();
					foreach ( $item->categories as $item_category ) {
						$terms[] = $item_category->name;
					}

					// Attach the categories to the posts.
					wp_set_object_terms( $post_id, $terms, 'product-categories' );

					update_post_meta( $post_id, 'big_cartel_importer_id', $product_id );
					update_post_meta( $post_id, 'big_cartel_importer_price', $product_price );
					update_post_meta( $post_id, 'big_cartel_importer_link', $product_link );

					// This will import the images to the media library.
					if ( isset( $item->images[0]->url ) ) {
						$image_url  = esc_url( $item->images[0]->url );
						$upload_dir = wp_upload_dir();
						$image_data = file_get_contents( $image_url );
						$filename   = basename( $image_url );

						$file = $upload_dir['basedir'] . '/' . $filename;
						if ( wp_mkdir_p( $upload_dir['path'] ) ) {
							$file = $upload_dir['path'] . '/' . $filename;
						}

						file_put_contents( $file, $image_data );

						// Now let's assign the image to the corresponding post.
						$wp_filetype = wp_check_filetype( $filename, null );

						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name( $filename ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);

						$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

						wp_update_attachment_metadata( $attach_id, $attach_data );

						set_post_thumbnail( $post_id, $attach_id );
					}
				}
			}
		}
	}

	/**
	 * Add terms for each of our imported products.
	 */
	public function add_terms() {

		// Grab each category listed in the BC array and make it a taxonomy term.
		if ( isset( $this->bc_object ) && ! empty( $this->bc_object ) ) {
			foreach ( $this->bc_object[1]->categories as $category ) {
				$term_name = $category->name;
				wp_insert_term( $term_name, 'product-categories' );
			}
		}

	}

	/**
	 * Import our products and add our new taxonomy terms on settings save.
	 */
	public function process_settings_save() {

		if ( empty( $_POST ) ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( empty( $this->bc_object ) ) {
			// Most likely to get here on initial save.
			if (
				isset( $_POST['big_cartel_importer_plugin_options']['store_name'] ) &&
				! empty( $_POST['big_cartel_importer_plugin_options']['store_name'] )
			) {
				$this->store_name = sanitize_text_field( $_POST['big_cartel_importer_plugin_options']['store_name'] );
				$this->set_bigcartel_results();
			}
		}

		$this->add_terms();
		$this->import_products();
	}

	/**
	 * Add the meta box.
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->metabox_settings['id'],
			$this->metabox_settings['title'],
			array( $this, 'metabox_fields' ),
			$this->metabox_settings['page'],
			$this->metabox_settings['context'],
			$this->metabox_settings['priority']
		);
	}

	/**
	 * Display the box on the post edit page.
	 */
	public function metabox_fields() {
		global $post;

		wp_nonce_field( 'big_cartel_importer_nonce', 'big-cartel-importer' );

		// Display it all!
		echo '<table class="form-table">';
		foreach ( $this->metabox_settings['fields'] as $field ) {
			$meta = get_post_meta( $post->ID, $field['id'], true );
			echo '<tr><th style="width: 20%"><label for="'. $field['id'] .'">'. $field['name'] .'</label></th><td>';
			switch ( $field['type'] ) {
				case 'text':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br />'. $field['desc'];
					break;
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	/**
	 * Save our meta data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_post( $post_id ) {

		if ( ! isset( $_POST['big_cartel_importer_nonce'] ) || ! wp_verify_nonce( $_POST['big_cartel_importer_nonce'], 'big-cartel-importer' ) ) {
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

		foreach ( $this->metabox_settings['fields'] as $field ) {
			$old = get_post_meta( $post_id, $field['id'], true );
			$new = $_POST[ $field['id'] ];

			if ( $new && $new != $old ) {
				update_post_meta( $post_id, $field['id'], $new );
			} elseif ( '' == $new && $old ) {
				delete_post_meta( $post_id, $field['id'], $old );
			}
		}
	}

	/**
	 * Parse out the BigCartel username from url.
	 *
	 * @since 1.1.0
	 *
	 * @param string $url User's store URL.
	 * @return mixed User's username.
	 */
	public function parse_username( $url ) {
		$parsed_url = wp_parse_url( untrailingslashit( $url ) );

		if ( ! empty( $parsed_url['host'] ) && isset( $parsed_url['host'] ) ) {
			$parts = explode( '.', $parsed_url['host'] );
		}

		if ( ! empty( $parsed_url['path'] ) && isset( $parsed_url['path'] ) ) {
			$parts = explode( '.', $parsed_url['path'] );
		}

		return $parts[0];
	}
}
$WDS_BC_Importer = new WDS_BC_Importer;
$WDS_BC_Importer->do_hooks();
