<?php

/**
 * @author     Kings Of The Web
 * @year       2022
 * @package    wavereseller.kingsoftheweb.xyz
 * @subpackage ${NAMESPACE}
 */

namespace wamlicense;

use SimpleXMLElement;

class WAMLicense {

	public function __construct() {
		// Only initiate the plugin if woocommerce subscriptions is active.
		if ( $this->is_woocommerce_subscriptions_active() ) {
			$this->update_templates();

			// Find an appropiate hook other that init that only runs on my-account page.
            add_action( 'template_redirect', array($this,'generate_xml_on_request') );

        }

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
     * @throws \Exception
     */
	public function generate_xml_on_request() {
        if( !is_wc_endpoint_url('downloads')){
            return false;
        }

        if (  ! isset( $_GET['user_id'] ) || ! isset( $_GET['order_id'] ) ) {
            return false;
        }

        $user_id=(int)$_GET['user_id'];
        $order_id=(int)$_GET['order_id'];



		// Check if the incoming user_id is the same as the current user id.
		$current_user = wp_get_current_user();
		if ( $user_id !== $current_user->ID ) {
			return false;
		}

        $product_info = $this ->get_product_information($order_id,$user_id);

		// Generate the XML.
        $this->generate_xml($product_info,$user_id);
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
            $endDate = $subscription->get_time('end');
            $user_final_subscriptions[]=array(
                    'subscription_id'=>$subscription_id,
                    'subscription_start_date'=>$startDate,
                    'subscription_next_payment_date'=> $nextPayment,
                    'subscription_end_date'=> $endDate,
            );
        }

		$product_info = array(
                'order_id' => $order_id,
                'products' => $order_products_info,
                'user_subscriptions'=>$user_final_subscriptions,


        );

        return $product_info;
	}

    /**
     * This should generate an XML with the correct info for the specific product id and user.
     * @return
     * @throws \Exception
     */
	public function generate_xml( $product_info, $user_id ) {
        $xml = new SimpleXMLElement("<license></license>");
        $xml->addChild("version", 1);
        $xml->addChild("subscriptionNumber", $product_info['user_subscriptions'][0]['subscription_id']);
        $xml->addChild("startDate", $product_info['user_subscriptions'][0]['subscription_start_date']);
        $xml->addChild("nextPaymentDate", $product_info['user_subscriptions'][0]['subscription_next_payment_date']);
        $xml->addChild("endDate", $product_info['user_subscriptions'][0]['subscription_end_date']);
        $xml->addChild("parentOrder", $product_info['order_id']);
        $xml->addChild("renewalOrders", '');
        $products= $xml->addChild("products");
        $products_array=$product_info['products'];
        foreach($products_array as $single_product){
            $product=$products->addChild('product');
            $product->addChild('ID',$single_product['product_id']);
            $product->addChild('title',$single_product['product_title']);
            $product->addChild('quantity',$single_product['product_quantity']);
            $product->addChild('sku',$single_product['product_sku']);
        }

        // Products

        ob_end_clean();
        header_remove();

        header("Content-type: text/xml");
        header('Content-Disposition: attachment; filename="license.xml"');
        echo $xml->asXML();
        exit();
    }
}
