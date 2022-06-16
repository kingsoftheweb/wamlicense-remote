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


            add_action( 'wp_enqueue_scripts', array($this,'generate_license') );
            add_action( 'wp_footer', array($this,'add_ajax_url') );

            add_action("wp_ajax_generate_xml_on_request", array($this,'generate_xml_on_request'));}

	}

	/**
	 * This should check if woocommerce subscriptions is active or no
	 * @return bool
	 */
	public function is_woocommerce_subscriptions_active() {

        if (  is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
            return true;
        }
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

        if (  empty( $_GET['user_id'] ) || empty( $_GET['order_id'] ) ) {
            return false;
        }

        $user_id=(int)$_GET['user_id'];
        $order_id=(int)$_GET['order_id'];



		// Check if the incoming user_id is the same as the current user id.
		$current_user = wp_get_current_user();
		if ( $user_id !== $current_user->ID ) {
			return false;
		}
		// Generate the XML.
		$this->generate_xml( $order_id, $user_id );
        die();
	}

	/**
	 * This should return an array of product information that will be
	 * used for the XML export.
	 *
	 * @param $product_id
	 *
	 * @return array
	 */
	public function get_product_information( $order_id, $user_id ): array {
		$product_info = array(
                'order_id' => $order_id,
        );

		// Grab the info here.

		return $product_info;
	}

	/**
	 * This should generate an XML with the correct info for the specific product id and user.
	 * @return void
	 */
	public function generate_xml( $product_id, $user_id ) {
		$product_info = $this->get_product_information( $product_id, $user_id );

		// Start Generating an XML file on the fly without adding it to the server. Use "header()"
		$filename = 'license';
		$file     = fopen( $filename, 'w' );
	}

    /**
     * Enqueuing JS scripts to generate License
     */
    public function generate_license() {
        wp_enqueue_script( 'generate_license', plugin_dir_url( __FILE__ ) . '/assets/js/generate-license.js', array( 'jquery' ) );
    }
    public function add_ajax_url() {
        ?>
        <script type="text/javascript">
            var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
        </script>
        <?php
    }
}
