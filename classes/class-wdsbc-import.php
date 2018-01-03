<?php


/**
 * Class WDS_BC_Importer
 */
class WDS_BC_Importer {

	public $options = array();

	public $store_name = '';

	/**
	 * BigCartel object.
	 * @since 1.0.0
	 * @var mixed
	 */
	public $bc_object = array();

	protected $importer = null;

	/**
	 * WDS_BC_Importer constructor.
	 */
	public function __construct( $args = array() ) {

		if ( isset( $args['importer'] ) ) {
			$this->importer = $args['importer'];
		}

		// Setup all our necessary variables.

		$this->options = get_option( 'big_cartel_importer_plugin_options', array() );

		if ( isset( $this->options['store_name'] ) ) {
			$this->store_name = $this->options['store_name'];
		}

		$this->set_bigcartel_results();
	}

	/**
	 * Sets our results array for import later.
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
	 * Output the post data and create our posts.
	 */
	public function import_products() {

		$products = $this->bc_object;
		foreach ( $products as $item ) {
			$this->importer->push_to_queue( $item );
		}

		$this->importer->save()->dispatch();
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

	public function has_data() {
		if ( empty( $this->bc_object ) ) {
			return false;
		}
		return true;
	}

	public function get_shop_count() {
		return count( $this->bc_object );
	}
}
