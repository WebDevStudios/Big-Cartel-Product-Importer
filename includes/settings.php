<?php

/**
 * Add our menu item.
 *
 * @since 1.1.0
 */
function wdsbc_add_settings_menu() {
	add_options_page(
		__( 'Big Cartel Importer', 'big-cartel-product-importer' ),
		__( 'Big Cartel Importer', 'big-cartel-product-importer' ),
		'manage_options',
		'big-cartel-importer',
		'wdsbc_settings_admin_page'
	);
}
add_action( 'admin_menu', 'wdsbc_add_settings_menu' );

/**
 * Register our settings.
 *
 * @since 1.1.0
 */
function register_admin_settings() {
	register_setting(
		'big_cartel_importer_plugin_options',
		'big_cartel_importer_plugin_options',
		'wdsbc_validate_settings'
	);
	add_settings_section( 'big_cartel_importer_main_options', '', '', 'big-cartel-importer' );
	add_settings_field(
		'store_name',
		__( 'Big Cartel Store Name: ', 'big-cartel-product-importer' ),
		'wdsbc_settings_store_name',
		'big-cartel-importer',
		'big_cartel_importer_main_options'
	);

	add_settings_field(
		'offset',
		__( 'Product offset to start at: ', 'big-cartel-product-importer' ),
		'wdsbc_settings_offset',
		'big-cartel-importer',
		'big_cartel_importer_main_options'
	);
}
add_action( 'admin_init', 'register_admin_settings' );

function wdsbc_settings_offset() {
	?>
	<div class="input-wrap" >
		<div class="left" >
			<label ><input name="big_cartel_importer_plugin_options[offset]" style="width:30%;" type="text" value="" /></label>
		</div>
		<div class='right'>
			<?php
			printf(
				esc_html__( 'Use this offset where the import begins. Useful to prevent re-importing existing products, if desired.', 'big-cartel-product-importer' )
			);
			?>
		</div>
	</div>
<?php
}

/**
 * Render our settings page main content.
 *
 * @since 1.1.0
 */
function wdsbc_settings_store_name() {
	// Get the total post count.
	$count_posts = array_map( 'absint', (array) wp_count_posts( 'bc_import_products' ) );
	$total_posts = $count_posts['private'] + $count_posts['publish'];

	// Get the total term count.
	$count_terms = wp_count_terms( 'product-categories' );

	$options = get_option( 'big_cartel_importer_plugin_options' );
	?>
	<div class="input-wrap">
		<div class="left">
			<label><input name="big_cartel_importer_plugin_options[store_name]" style="width:30%;" type="text" value="<?php esc_attr_e( $options['store_name'] ); ?>" /></label>
		</div>
		<div class='right'>
			<?php
			printf(
				esc_html__( 'If your store URL is: %s, enter %s in the text field.', 'big-cartel-product-importer' ),
				'http://<strong>yourstorename</strong>.bigcartel.com',
				'<strong>yourstorename</strong>'
			);
			?>
		</div>
		<?php
		$wdsbc = new WDS_BC_Importer();

		if ( ! $wdsbc->has_data() ) {
			$message = esc_html__( 'Your store is currently in maintenance mode and can not have its products imported.', 'big-cartel-product-importer' );

		} else {
			$message = sprintf(
				esc_html__( 'You have imported %s of %s products in %s categories.', 'big-cartel-product-importer' ),
				'<strong>' . esc_html( $total_posts ) . '</strong>',
				'<strong>' . $wdsbc->get_shop_count() . '</strong>',
				'<strong>' . esc_html( $count_terms ) . '</strong>'
			);
		}
		?>
		<p><?php echo $message; // WPCS: XSS ok. ?></p>
	</div>
	<?php
}

/**
 * Render our settings page.
 *
 * @since 1.1.0
 */
function wdsbc_settings_admin_page() {
	?>
	<div id="theme-options-wrap">
		<div class="icon32" id="icon-tools"></div>
		<h2><?php esc_html_e( 'Big Cartel Importer Options', 'big-cartel-product-importer' ); ?></h2>
		<p><?php esc_html_e( 'Set the URL of your Big Cartel store to pull in your products.', 'big-cartel-product-importer' ); ?></p>
		<form id="options-form" method="post" action="options.php" enctype="multipart/form-data">
			<?php settings_fields( 'big_cartel_importer_plugin_options' ); ?>
			<?php do_settings_sections( 'big-cartel-importer' ); ?>
			<p class="submit">
				<input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save and run import', 'big-cartel-product-importer' ); ?>" />
			</p>
		<?php wp_nonce_field( 'big_cartel_importer_settings_nonce', 'big-cartel-importer-settings' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Vaidated and save our settings.
 *
 * @since 1.1.0
 *
 * @param array $big_cartel_importer_plugin_options Saved settings
 * @return mixed
 */
function wdsbc_validate_settings( $big_cartel_importer_plugin_options = array() ) {
	$big_cartel_importer_plugin_options['store_name'] = sanitize_text_field( wdsbc_parse_username( $big_cartel_importer_plugin_options['store_name'] ) );
	return $big_cartel_importer_plugin_options;
}

/**
 * Process the saving of our settings page, and trigger import.
 *
 * @since 1.1.0
 */
function wdsbc_process_settings_save() {
	$saved_store = '';

	if ( empty( $_POST ) ) {
		return;
	}

	if ( ! isset( $_POST['big-cartel-importer-settings'] ) || ! wp_verify_nonce( $_POST['big-cartel-importer-settings'], 'big_cartel_importer_settings_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if (
		isset( $_POST['big_cartel_importer_plugin_options']['store_name'] ) &&
		! empty( $_POST['big_cartel_importer_plugin_options']['store_name'] )
	) {
		$saved_store = sanitize_text_field( $_POST['big_cartel_importer_plugin_options']['store_name'] );
	}

	$wdsbc = new WDS_BC_Importer(
		array(
			'importer' => new wdsBC_Importer(
				array( 'store_name' => $saved_store )
			)
		)
	);
	if ( ( ! $wdsbc->has_data() ) || ( $saved_store !== $wdsbc->store_name ) ) {
		// Most likely to get here on initial save or store change.
		$wdsbc->store_name = $saved_store;
		$wdsbc->set_bigcartel_results();
	}

	$offset = ( isset( $_POST['big_cartel_importer_plugin_options']['offset'] ) ) ? absint( isset( $_POST['big_cartel_importer_plugin_options']['offset'] ) ) : 0;

	$wdsbc->add_terms();
	$wdsbc->import_products();
}
add_action( 'admin_init', 'wdsbc_process_settings_save' );

/**
 * Parse out the BigCartel username from url.
 *
 * @since 1.1.0
 *
 * @param string $url User's store URL.
 * @return mixed User's username.
 */
function wdsbc_parse_username( $url ) {
	$parsed_url = wp_parse_url( untrailingslashit( $url ) );
	$parts = '';

	if ( ! empty( $parsed_url['host'] ) && isset( $parsed_url['host'] ) ) {
		$parts = explode( '.', $parsed_url['host'] );
	}

	if ( ! empty( $parsed_url['path'] ) && isset( $parsed_url['path'] ) ) {
		$parts = explode( '.', $parsed_url['path'] );
	}

	return $parts[0];
}
