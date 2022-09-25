<?php
$phone = esc_attr($_POST['payment_number'] );
$network_id = '1'; // mtn
$reason = 'Test';

$url = 'https://e.patasente.com/phantom-api/pay-with-patasente/' . $this->api_key . '/' . $this->merchent_id . '?phone=' . $phone . '&amount=' . $total . '&mobile_money_company_id=' . $network_id . '&reason=' . 'Payment for Order: ' .$order_id;

//var_dump($url);

$response = wp_remote_post( $url, array( 'timeout' => 45 ) );

if ( is_wp_error( $response ) ) {
    $error_message = $response->get_error_message();
    return "Something went wrong: $error_message";
}

if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
    $order->update_status( apply_filters( 'woocommerce_payleo_process_payment_order_status', $order->has_downloadable_item() ? 'wc-invoiced' : 'processing', $order ), __( 'Payments pending.', 'onlypass-payments-woo' ) );
}

if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
    $response_body = wp_remote_retrieve_body( $response );
//    var_dump($response_body['message']);
    if ( 'Thank you! Your payment was successful' === $response_body['message'] ) {
        $order->payment_complete();

        // Remove cart.
        WC()->cart->empty_cart();

        // Return thankyou redirect.
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }
}