<?php
/**
 * Plugin Name: WooCommerce Memberships Stacking
 * Plugin URI:  https://github.com/mudtar/wc-memberships-stacking 
 * Description: Concatenate user membership length when multiple of the same membership plan access item are purchased in a single order.
 * Author:      Ian Burton
 * Author URI:  https://github.com/mudtar
 */

add_action( 'wc_memberships_grant_membership_access_from_purchase',
            'mudtar_wcms_concatenate_length_on_purchase', 10, 2 );

function mudtar_wcms_concatenate_length_on_purchase( $plan, $args ) {
  $order = new WC_Order( $args['order_id'] );

  // Get the quantity purchased of the item associated with this
  // membership.
  foreach ( $order->get_items() as $key => $item ) {
    if ( $item['product_id'] == $args['product_id'] ) {
      $item_quantity = $item['qty'];
      break;
    }
  }

  $user_membership = new WC_Memberships_User_Membership(
    $args['user_membership_id'] );

  // Get the current end date from the membership.
  $end_date = strtotime( $user_membership->get_end_date() );
  // Subtract the access length that has just been added to the user
  // membership after the purchase.
  $end_date = strtotime( '- ' . $plan->get_access_length(), $end_date );
  // Calculate the access length amount to add by multiplying the item
  // quantity by the membership plan's access length amount.
  $access_length_amount_to_add = $plan->get_access_length_amount() *
                                 $item_quantity;
  // Add the proper access length extension to the end date.
  $end_date = strtotime( '+ ' . $access_length_amount_to_add . ' ' .
                         $plan->get_access_length_period(), $end_date );
  // Set the user membership's end date.
  $user_membership->set_end_date( date( 'Y-m-d H:i:s', $end_date ) );
}
