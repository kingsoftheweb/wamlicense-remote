<?php
/**
 * @author     Kings Of The Web
 * @year       2022
 * @package    wavereseller.kingsoftheweb.xyz
 * @subpackage wamlicense
 */

namespace wamlicense;

class DownloadsTemplate {
	/**
	 * This adds a new column to the downloads table : "Download License"
	 * @return void
	 */

    public function update_templates(){
        add_filter('woocommerce_account_downloads_columns', array($this,'add_download_licence_row'), 1, 99);
        add_action('woocommerce_account_downloads_column_download-license', array($this,'add_license_to_downloads_rows'), 1, 99);
    }

    // Add new column "Download License"
    public function add_download_licence_row( $columns ){
        $columns['download-license'] = __( 'Download License', 'woocommerce' );
        return $columns;
    }

    // Add new <a> with $_GET "license_generate=true&product_id=true&user_id=true;
    public function add_license_to_downloads_rows( $download ){

        $licence_url = $download['product_id'];
        // if not, then use global $product;

        echo "<a href='$licence_url'>Download License</a>";
    }

}
