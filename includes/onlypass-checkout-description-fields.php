<?php

add_filter( 'woocommerce_gateway_description', 'onlypass_description_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'onlypass_form_processing' );
add_action( 'woocommerce_checkout_update_order_meta', 'onlypass_checkout_update_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'onlypass_order_data_after_billing_address', 10, 1 );
add_action( 'woocommerce_order_item_meta_end', 'onlypass_order_item_meta_end', 10, 3 );
function initializePayment($apikey,$merchant_id,$amount,$extRef,$isDemo)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://devapi.onlypassafrica.com/api/v1/external/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "gatewayId": 1,
            "externalReference": "'.$extRef.'",
            "amount":'.$amount.',
            "isDemo":'.$isDemo.'
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json',
            'x-api-key: '.$apikey,
            'x-platform-id:'.$merchant_id
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function onlypass_description_fields( $description, $payment_id ) {

    if ( 'onlypass' !== $payment_id ) {
        return $description;
    }

    ob_start();

    $gateways = get_option('onlypass_gateways');
    if($gateways != null || empty($gateways )) {
       $apikey = get_option('onlypass_api_key');
       $merchant_id = get_option('onlypass_merchent_id');
       $r = hex2bin(get_option('onlypass_gateways'));
        global $woocommerce;
        $total = intval($woocommerce->cart->total);
        $currency_type = get_option('onlypass_currency_type' );
        // initialize payment

       $extRef = uniqid("onlypass-");
       $response = initializePayment($apikey,$merchant_id,$total,$extRef,true);
        $res = json_decode($response);
//        var_dump(WC_Cart);
        if(isset($res->status) && $res->status) {
            echo '<style>.woocommerce-error{display: none;}</style>';
            echo '<div class="card" style="display: inline-flex; width:279px; height:auto;padding:10px;">
         <input name="firstCall" id="firstCall" value="1" type="hidden" />
         <input name="totalAmount" value="' . $res->data->amountToPay . '" type="hidden" />
        <input name="currencyType" value="' . $currency_type . '" type="hidden" />
        <input name="onlyPassRef" value="' . $res->data->onlyPassReference. '" type="hidden" />';

//    echo '<img src="' . plugins_url('../assets/logo.png', __FILE__ ) . '">';
//    woocommerce_form_field(
//        'payment_number',
//        array(
//            'type' => 'checkbox',


//            'label' =>__( 'Payment Phone Number', 'onlypass-payments-woo' ),
//            'class' => array( 'form-row', 'form-row-wide' ),
//            'required' => true,
//        )
//    );
            echo '<div >';
            echo '<div >Select one of the following gateways below to proceed:</div>';
            $list = json_decode($r);
            foreach ($list as $k => $p) {
                $checked = $k == 0 ? "checked" : "";
                echo '<div style="align-items: center;display: inline-flex;align-content: center;justify-content: flex-start;padding: 2px 10px;border: solid 1px #444;width: 100%;border-radius: 10px;margin-bottom: 10px;" >
                        <input ' . $checked . ' required id="' . $p->gateway->name . '_input" name="payment_gateway_list" value="' . bin2hex(json_encode($p)) . '" type="radio" class="form-row" style="outline: 0px;border: 0px;width: 20px;height: 20px;cursor: pointer;" />
                        <img style="padding-left: 5px;width: 139px;height: 24px;object-fit: cover;" src="' . $p->gateway->logoUrl . '" />
                      </div>';
            }
            echo '<div>
                <small style="float: right;margin-top:25px;padding-right: 20px;">Powered by Onlypass</small>
                </div>';
            echo '</div>';
        }else{
            echo '<script>jQuery("#place_order").hide()</script>';
            echo '<div class="woocommerce-error" style="display: block;width: 114%;margin: -17px;"><b>Oops!</b><br/>Payment initialization failed.<br/><small>To use OnlyPass Payment gateways, a valid API Key & Merchant ID is required.</small></div>';
        }

    }else{
        echo '<script>jQuery("#place_order").hide()</script>';
        echo '<div class="woocommerce-error" style="display: block;width: 114%;margin: -17px;"><b>Oops!</b><br/>Payment gateways not available.<br/><small>To use OnlyPass Payment gateways, a valid API Key & Merchant ID is required.</small></div>';
    }
    echo '</div>';
    $description .= ob_get_clean();
    return $description;
}

function onlypass_form_processing() {
//    if(isset($_POST['onlypass_enabled'])  || empty($_POST['onlypass_enabled'])) {
//        wc_add_notice( 'Invalid OnlyPass Merchant Credentials', 'error' );
//    }

}

function onlypass_checkout_update_order_meta( $order_id ) {

    if( isset( $_POST['payment_number'] ) || ! empty( $_POST['payment_number'] ) ) {
        update_post_meta( $order_id, 'payment_number', $_POST['payment_number'] );
    }
}

function onlypass_order_data_after_billing_address( $order ) {
    echo '<p><strong>' . __( 'Payment Phone Number:', 'onlypass-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'payment_number', true ) . '</p>';
}

function onlypass_order_item_meta_end( $item_id, $item, $order ) {
    echo '<p><strong>' . __( 'Payment Phone Number:', 'onlypass-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'payment_number', true ) . '</p>';
}