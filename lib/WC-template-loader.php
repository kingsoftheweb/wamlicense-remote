<?php
/**
 * @author     Kings Of The Web
 * @year       2022
 * @package    wavereseller.kingsoftheweb.xyz
 * @subpackage wamlicense
 */

namespace wamlicense;

class WCTemplateLoader {
	/**
	 * This adds a new column to the downloads table : "Download License"
	 * @return void
	 */

	public function load_custom_wc_template() {
		add_filter( 'woocommerce_locate_template', array( $this, 'csp_locate_template' ), 10, 3 );
		add_filter( 'wp_head', array( $this, 'single_subscription_endpoint' ), 10, 3 );

	}

	function csp_locate_template( $template, $template_name, $template_path ) {
		$downloads_endpoint = is_wc_endpoint_url( 'downloads' );
		if ( is_user_logged_in() && $downloads_endpoint ) {
			$template = trailingslashit( WP_PLUGIN_DIR ) . 'wamlicense/templates/my-downloads.php';
		}
		return $template;
	}

    function single_subscription_endpoint(){
        $subscription_detail_endpoint = is_wc_endpoint_url( 'view-subscription' );
        if(is_user_logged_in() && $subscription_detail_endpoint){
            echo '<style>
.woocommerce-order-downloads{
display:none;
}

</style>';
        }
    }


}
