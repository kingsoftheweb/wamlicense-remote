<?php

/**
 * @author     Kings Of The Web
 * @year       2022
 * @package    wavereseller.kingsoftheweb.xyz
 * @subpackage ${NAMESPACE}
 */

namespace wamlicense;

use DateTime;

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
	 * @return
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
        $product_info=$this->get_product_information($order_id,$user_id);
        echo $product_info;
        die();
	}

    /**
     * This should return an array of product information that will be
     * used for the XML export.
     *
     * @param $product_id
     *
     * @return array
     * @throws \Exception
     */
	public function get_product_information( $order_id, $user_id ) {
        $order=wc_get_order($order_id);
        $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) );
        $order_products_info=array();
        $user_final_subscriptions=array();
        foreach ($order->get_items() as $item){
            $product = wc_get_product( $item->get_product_id());
            $order_products_info[]=array(
                    'product_id'=>$item->get_id(),
                    'product_title'=>get_the_title($item->get_product_id()),
                    'product_quantity'=>$item->get_quantity(),
                    'product_sku'=>$product->get_sku(),
            );
        }
        foreach($subscriptions_ids as $subscription_id=>$subscription){
            $startDate = $subscription->get_time('start');
            $nextPayment = $subscription->get_time('next_payment');
            $start_date = new DateTime("@$startDate");
            $next_payment_date = new DateTime("@$nextPayment");
            $user_final_subscriptions[]=array(
                    'subscription_id'=>$subscription_id,
                    'subscription_start_date'=>$start_date->format('Y-m-d H:i:s'),
                    'subscription_next_payment_date'=> $next_payment_date->format('Y-m-d H:i:s'),
            );
        }

		$product_info = array(
                'order_id' => $order_id,
                'product' => $order_products_info,
                'user_subscriptions'=>$user_final_subscriptions,


        );

        return $this->generate_xml($product_info,$user_id);
//        var_dump($product_info);
	}

	/**
	 * This should generate an XML with the correct info for the specific product id and user.
	 * @return
	 */
	public function generate_xml( $product_info, $user_id ) {
        $final_xml_values='<?xml version="1.0" encoding="utf-8" ?><license><version>1</version>';

        $final_xml_values .='<subscriptionNumber>'.$product_info['user_subscriptions'][0]['subscription_id'].'</subscriptionNumber>';

        $final_xml_values .='<startDate>'.$product_info['user_subscriptions'][0]['subscription_start_date'].'</startDate>';

        $final_xml_values .='<nextPaymentDate>'.$product_info['user_subscriptions'][0]['subscription_next_payment_date'].'</nextPaymentDate>';

        $final_xml_values .='<endDate>'.$product_info['user_subscriptions'][0]['subscription_next_payment_date'].'</endDate>';
        $final_xml_values .='<parentOrder>'.$product_info['user_subscriptions']['order_id'].'</parentOrder>';
        $final_xml_values .='<products>';

        foreach($product_info['product'] as $product){
            $final_xml_values .= '<product>';
            $final_xml_values .= '<ID>'.$product['product_id'].'</ID>';
            $final_xml_values .= '<title>'.$product['product_title'].'</title>';
            $final_xml_values .= '<quantity>'.$product['product_quantity'].'</quantity>';
            $final_xml_values .= '<sku>'.$product['product_sku'].'</sku>';
            $final_xml_values .= '</product>';
        }

        $final_xml_values .='</products>';

        $final_xml_values.='</license>';

        return wp_json_encode($final_xml_values);
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
