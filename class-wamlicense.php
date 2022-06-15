<?php

/**
 * @author     Kings Of The Web
 * @year       2022
 * @package    wavereseller.kingsoftheweb.xyz
 * @subpackage ${NAMESPACE}
 */

namespace wamlicense;

class WAMLicense {

	public function __construct() {
		// Only initiate the plugin if woocommerce subscriptions is active.
		if ( $this->is_woocommerce_subscriptions_active() ) {
			$this->update_templates();

			// Find an appropiate hook other that init that only runs on my-account page.
			add_action( 'init', array( $this, 'generate_xml_on_request' ) );
		}
	}

	/**
	 * This should check if woocommerce subscriptions is active or no
	 * @return void
	 */
	public function is_woocommerce_subscriptions_active() {
	}

	/**
	 * This should hook into the downloads template, to add the correct action of the Download URL.
	 *
	 * @return void
	 */
	public function update_templates() {

		// Update the downloads' template using this function to add new column.
		$downloads_template = new DownloadsTemplate();
		$downloads_template->update_templates();


	}


	/**
	 * This license to $_GET parameters, and if it has license_generate,
	 * it will check if appropriate request before actually generating the XML.
	 *
	 *
	 * @return false|void
	 */
	public function generate_xml_on_request() {

		if ( ! isset( $_GET['license_generate'] ) || ! isset( $_GET['user_id'] ) || isset( $_GET['product_id'] ) ) {
			return false;
		}

		// Check if the incoming user_id is the same as the current user id.
		$current_user = wp_get_current_user();
		if ( $_GET['user_id'] !== $current_user->ID ) {
			return false;
		}

		// Generate the XML.
		$this->generate_xml( $_GET['product_id'], $_GET['user_id'] );

	}

	/**
	 * This should return an array of product information that will be
	 * used for the XML export.
	 *
	 * @param $product_id
	 *
	 * @return array
	 */
	public function get_product_information( $product_id, $user_id ): array {
		$product_info = array();

		// Grab the info here.

		return $product_info;
	}

	/**
	 * This should generate an XML with the correct info for the specific product id and user.
	 * @return void
	 */
	public function generate_xml( $product_info, $user_id ) {
		$product_info = $this->get_product_information( $product_id, $user_id );

		// Start Generating an XML file on the fly without adding it to the server. Use "header()"
		$filename = 'license';
		$file     = fopen( $filename, 'w' );
	}


}
