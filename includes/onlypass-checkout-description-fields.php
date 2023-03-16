<?php
add_filter( 'woocommerce_gateway_description', 'onlypass_description_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'onlypass_form_processing' );
add_action( 'woocommerce_checkout_update_order_meta', 'onlypass_checkout_update_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'onlypass_order_data_after_billing_address', 10, 1 );
add_action( 'woocommerce_order_item_meta_end', 'onlypass_order_item_meta_end', 10, 3 );


function onlypass_description_fields( $description, $payment_id ) {
    ob_start();
    global $apikey;
    global $merchantId;
    global $gateways;
    global $currency;
    global $isLive;
    global $woocommerce;
    global $env;

//    if ( !is_user_logged_in() ) {
//        global $wp;
//        $uri =  get_site_url()."/my-account";
//        wp_redirect($uri);
//    }
//    if ( 'onlypass' !== $payment_id ) {
//        return $description;
//    }
//    var_dump($apikey);
//    echo '<br/>';
//    var_dump($merchantId);
//    echo '<br/>';
//    var_dump($gateways);
//    echo '<br/>';
//    var_dump($currency);
//    echo '<br/>';
//    var_dump($isLive);
//    echo 'apikey';
//    echo '<br/>';
        //  echo "SESSION ".$_SESSION["payInit"];
        $isDemo = get_option('onlypass_merchent_id');
        if(isset($gateways) && strlen($gateways) != 0 && 'onlypass' == $payment_id ) {
        $r = hex2bin($gateways);
        // var_dump($r);
        // return;
         $total = intval($woocommerce->cart->total);
         echo '<style>.woocommerce-error{display: none;}</style>';
         echo '<div class="card" style="display: inline-flex; width:279px; height:auto;padding:10px;">
         <input name="apikey" value="'.$apikey.'" type="hidden" />
         <input name="merchant_id"  value="'.$merchantId.'" type="hidden" />
         <input name="isLive"  value="'.$isLive.'" type="hidden" />
         <input name="firstCall" id="firstCall" value="1" type="hidden" />
         <input name="totalAmount" value="' .$total.'" type="hidden" />
        <input name="currencyType" value="' .$currency. '" type="hidden" />
        <input name="env" value="'.$env.'" type="hidden" />';
         $list = json_decode($r);
         echo '<div >';
         if(count($list) != 0) {
            echo '<div >Select one of the following gateways below to proceed:</div>';
           }else{
            echo '<div style="background: #ffe7e7;padding: 10px;font-size: 14px;border-left: solid 2px red;display: table-caption;width: 192px;">Oops! no payment gateway added.</div>';
            }
//                      $list[] = array("merchantPaymentGatewayId"=>count($list)+1,"merchantId"=>3,"gatewayId"=>count($list)+1,"testPublicKey"=>"pub_test_5YECM7A6JKXS69SQ907FN","livePublicKey"=>"pub_test_5YECM7A6JKXS69SQ907FN","status"=>"ACTIVE","gateway"=>array("gatewayId"=>count($list)+1,"name"=>"Bani","className"=>"Bani","logoUrl"=>"https://www.gitbook.com/cdn-cgi/image/width=40,height=40,fit=contain,dpr=2,format=auto/https%3A%2F%2F2283340809-files.gitbook.io%2F~%2Ffiles%2Fv0%2Fb%2Fgitbook-x-prod.appspot.com%2Fo%2Fspaces%252F-MdpY6mzS7POfcSmrF1z%252Ficon%252FTlId57nlZj8NzJeuTgGn%252FA2%2520(1)%2520(1).png%3Falt%3Dmedia%26token%3D673ae05c-8ab0-4576-b6bb-b469aa23d37a","status"=>"ACTIVE"));
echo '<ul id="gateways" style="margin: 20px 10px;"></ul>';
echo '<div>
                <small style="float: right;margin-top:25px;padding-right: 20px;">Powered by Onlypass</small>
                </div>';
            echo '</div>';
            echo '<script>
            jQuery(document).ready(()=>{
                jQuery.each(window.gatewaylist,(i,obj)=>{
                // console.log(obj);
                var sz = "initial";
                var name = "";
                var channels = "";
                var channels_count = 0;
                jQuery.each(obj.gateway.modes,(i,ch)=>{
                    channels += `<li style="display: flex;
                    align-content: center;
                    align-items: center;
                    justify-content:flex-start;
                    padding: 5px 0px;">
                    <input class="payment_channel" required name="payment_channel" value="${convertBinaryToHex(Object.assign(obj,ch))}" type="radio" style="outline: 0px;border: 0px;width: 20px;height: 20px;cursor: pointer;" />
                    <span style="margin-left:5px;">${ch.description}</span></li>`;
                    channels_count += 1;
                })
                if(String(obj.gateway.name).toLowerCase() === "paystack")
                {
                    sz = "cover";
                }
                if(String(obj.gateway.name).toLowerCase() === "flutterwave")
                {
                    sz = "cover";
                }
                if(String(obj.gateway.name).toLowerCase() === "bani")
                {
                    sz = "contain";
                    name = obj.gateway.name;
                }
                jQuery("#gateways").append(`<li><div style="align-items: center;display: inline-flex;align-content: center;justify-content: flex-start;padding: 8px 10px;border: solid 1px #444;width: 100%;border-radius: 10px;margin-bottom: 10px;" >
                <input required data-count="${channels_count}" id="${obj.gateway.name}_input" name="payment_gateway_list" value="${convertBinaryToHex(obj)}" type="radio" class="form-row payment_gateway_list" style="outline: 0px;border: 0px;width: 20px;height: 20px;cursor: pointer;" />
                <img style="padding-left: 9px;width:${sz == "contain"?28:110}px;height: 19px;object-fit:${sz};" src="${obj.gateway.logoUrl}" />
                <span style="padding-left: 9px;"><b>${name}</b></span>
              </div>
              ${channels_count != 0?`<ul class="channels ${obj.gateway.name}_input" style="display:none;border: solid 1px #444;padding: 10px;margin-top: -11px;width: 89%;margin-bottom: 10px;"><li style="line-height:14px;"><small style="color:#999;">Select one of the following channels below:</small></li>${channels}</ul>`:""}
              </li>`);
            })
            setTimeout(()=>{
            jQuery(".payment_gateway_list").on("click",(e)=>{
                const count = jQuery(e.target).data("count");
                const id = jQuery(e.target).attr("id");
                jQuery(".channels").hide();
                if(count != 0)
                {
                jQuery(`.${id}`).show();
                }
                const inp = jQuery(".payment_channel");
                jQuery.each(inp,(i,e)=>{
                 jQuery(e).removeAttr("checked");
                //  console.log(e.target);
                })
            })
        },2000)
        })
            </script>';
            $description = ob_get_clean();
            return $description;
        }
    $description = ob_get_clean();
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