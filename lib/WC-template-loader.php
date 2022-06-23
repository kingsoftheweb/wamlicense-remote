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

	}

	function csp_locate_template( $template, $template_name, $template_path ) {
		$wc_endpoint = is_wc_endpoint_url( 'downloads' );
		if ( is_user_logged_in() && $wc_endpoint ) {
			$template = trailingslashit( WP_PLUGIN_DIR ) . 'wamlicense/templates/my-downloads.php';
		}
		return $template;
	}


}
