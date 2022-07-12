<?php
/**
 * Order Downloads.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-downloads.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Get customer downloads
?>
<section class="woocommerce-order-downloads">
	<?php if ( isset( $show_title ) ) : ?>
		<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Downloads', 'woocommerce' ); ?></h2>
	<?php endif; ?>


	<!-- Render the subscription table if there are subscriptions -->
	<?php
	$user_id             = get_current_user_id();
	$users_subscriptions = wcs_get_users_subscriptions( $user_id );
	if ( count( $users_subscriptions ) > 0 ) {
		?>
	<table class="woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details subscriptions_table">
				<thead style="text-align: center">
		<tr>
			<th class=""><span class="nobr">Subscription Number</span></th>
			<th class=""><span class="nobr">Downloads</span></th>
			<th class=""><span class="nobr">Download License</span></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $users_subscriptions as $subscription ) {
            $downloads = WC()->customer->get_downloadable_products();
            $download_url = wc_get_endpoint_url( 'downloads' );
			$licence_url          = $download_url . '?license_generate=true&order_id=' . $subscription->get_id() . '&user_id=' . $user_id;

            $downloads_subscription = [];
            foreach($downloads as $download){
                if($download['order_id'] === $subscription->get_id()){
                    $downloads_subscription[] = $download;
                }
                $downloads = $downloads_subscription;
            }
			?>
		<tr>
			<td style="text-align: center"><a href="<?php echo $subscription->get_view_order_url(); ?>"><?php echo $subscription->get_id(); ?></td>
			<td>
				<ul style="display: grid">
				<?php
				foreach ( $downloads as $download ) {
					echo '<li><a href="' . esc_url( $download['download_url'] ) . '" class="woocommerce-MyAccount-downloads-file button alt">' . esc_html( $download['download_name'] ) . '</a></li>';
				}
				?>
				</ul>
			</td>
			<td><?php echo "<a class='download-product-license' href='$licence_url'>Download License</a>"; ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php } ?>
</section>
