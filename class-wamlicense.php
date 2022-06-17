<?php

/**
 * @author     Kings Of The Web
 * @year       2022
 * @package    wavereseller.kingsoftheweb.xyz
 * @subpackage ${NAMESPACE}
 */

namespace wamlicense;

use SimpleXMLElement;
use WC_Subscription;
use WC_Subscriptions_Renewal_Order;

class WAMLicense {

	public function __construct() {
		// Only initiate the plugin if woocommerce subscriptions is active.
		if ( $this->is_woocommerce_subscriptions_active() ) {
			$this->update_templates();

			// Find an appropiate hook other that init that only runs on my-account page.
			add_action( 'template_redirect', array( $this, 'generate_xml_on_request' ) );

		}

	}

	/**
	 * This should check if woocommerce subscriptions is active or no
	 * @return bool
	 */
	public function is_woocommerce_subscriptions_active() {

		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
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
		if ( ! is_wc_endpoint_url( 'downloads' ) ) {
			return false;
		}

		if ( ! isset( $_GET['user_id'] ) || ! isset( $_GET['order_id'] ) ) {
			return false;
		}

		$user_id  = (int) $_GET['user_id'];
		$order_id = (int) $_GET['order_id'];

		// Check if the incoming user_id is the same as the current user id.
		$current_user = wp_get_current_user();
		if ( $user_id !== $current_user->ID ) {
			return false;
		}

		$product_info = $this->get_product_information( $order_id, $user_id );

		// Generate the XML.
		$this->generate_xml( $product_info, $user_id );
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
	public function get_product_information( $subscription_id, $user_id ) {
		$subscription        = new WC_Subscription($subscription_id);
        $subscription_products_info      = array();
		$final_related_orders=array();
        $renewal_orders_ids=array();
        foreach ( $subscription->get_items() as $item ) {
			$product               = wc_get_product( $item->get_product_id() );
			$subscription_products_info[] = array(
				'product_id'       => $item->get_id(),
				'product_title'    => $item['name'],
				'product_quantity' => $item->get_quantity(),
				'product_sku'      => $product->get_sku(),
			);
		}

            $related_orders = $subscription->get_related_orders('ids');
			$startDate                  = $subscription->get_time( 'start' );
            $final_start_date= Date('Y-m-d H:I:s',$startDate);
			$nextPayment                = $subscription->get_time( 'next_payment' );
            $final_next_date= Date('Y-m-d H:I:s',$nextPayment);
            $endDate                    = $subscription->get_time( 'end' );
            $final_end_date= Date('Y-m-d H:I:s',$endDate);
			$user_subscription = array(
                'subscription_title'=>'Subscription_'.$subscription_id,
				'subscription_id'                => $subscription_id,
				'subscription_start_date'        => $final_start_date,
				'subscription_next_payment_date' => $final_next_date,
				'subscription_end_date'          => $final_end_date,
                'subscription_products'=>$subscription_products_info,
                'subscription_parent_order'=>$subscription->get_parent_id(),
			);


        foreach ($related_orders as $related_subscription_id => $related_subscription_ids){
            if($related_subscription_id !== $subscription->get_parent_id()){
            $renewal_sub=new WC_Subscription($related_subscription_id);
            if(get_post_meta($related_subscription_id,'_subscription_renewal')){
                    $renewal_orders_ids[]=$related_subscription_id;
            }
            $final_related_orders[]=array(
                'subscription_id'=>$related_subscription_id,
                'subscription_status'=>$renewal_sub->get_status(),
                'subscription_date'=>$renewal_sub->get_time('start'),
            );
        }
        }

		$product_info = array(
			'order_id'           => $subscription_id,
			'user_subscription'           => $user_subscription,
			'related_orders' => $final_related_orders,
            'renewal_orders_ids'=>$renewal_orders_ids,

		);

		return $product_info;
	}

	/**
	 * This should generate an XML with the correct info for the specific product id and user.
	 * @return
	 * @throws \Exception
	 */
	public function generate_xml( $product_info, $user_id ) {
		$xml = new SimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><license></license>' );
		$xml->addChild( 'version', 1 );
		$xml->addChild( 'subscriptionNumber', $product_info['user_subscription']['subscription_id'] );
		$xml->addChild( 'startDate', $product_info['user_subscription']['subscription_start_date'] );
		$xml->addChild( 'nextPaymentDate', $product_info['user_subscription']['subscription_next_payment_date'] );
		$xml->addChild( 'endDate', $product_info['user_subscriptions']['subscription_end_date'] );
		$xml->addChild( 'parentOrder', $product_info['user_subscription']['subscription_parent_order'] );
		$xml->addChild( 'renewalOrders',implode(',',$product_info['renewal_orders_ids']) );

        // Related orders elements
        $related_subscriptions=$xml->addChild('resubscriptions');

        $related_subscriptions_array=$product_info['related_orders'];
        foreach($related_subscriptions_array as $related_sub){
            $subscription = $related_subscriptions->addChild( 'subscription' );
            $subscription->addChild( 'ID', $related_sub['subscription_id'] );
            $subscription->addChild( 'status', $related_sub['subscription_status'] );
            $subscription->addChild( 'date', $related_sub['subscription_date'] );
        }


        // Original product elements
        $products       = $xml->addChild( 'products' );
        $products_array = $product_info['user_subscription']['subscription_products'];
		foreach ( $products_array as $single_product ) {
			$product = $products->addChild( 'product' );
			$product->addChild( 'ID', $single_product['product_id'] );
			$product->addChild( 'title', $single_product['product_title'] );
			$product->addChild( 'quantity', $single_product['product_quantity'] );
			$product->addChild( 'sku', $single_product['product_sku'] );
		}
		// Products

		ob_end_clean();
		header_remove();
        $filename = $product_info['user_subscription']['subscription_title']. "_" . time() . '.lic';

        header( 'Content-type: text/xml' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );
		echo $xml->asXML();
		exit();
	}
}
