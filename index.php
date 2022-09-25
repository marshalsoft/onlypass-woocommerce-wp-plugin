<?php

/**
 * Plugin Name: OnlyPass Woocommerce Plugin
 * Plugin URI: https://onlypass.africa
 * Author Name: Marshall Ekene
 * Author URI: http://onlypass.africa
 * Description: Bringing all your payment solutions under one roof.
 * Version: 1.0
 * License: 1.0
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: onlypass-woocommerce
*/ 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'onlypass_payment_init', 11 );
add_filter( 'woocommerce_payment_gateways', 'add_to_payment_gateway');

function add_stylesheet_to_head() {
    echo "<style >.payment_method_onlypass > label{background: #212529 !important;color: transparent !important;display:none !important;} .payment_method_onlypass > label > img {float: left !important;display:none !important;} #payment_method_onlypass{opacity: 0} .payment_method_onlypass > label::before{content:'' !important;} </style>";
    echo '<script src="https://js.paystack.co/v2/inline.js"></script>';
    echo '<script src="https://checkout.flutterwave.com/v3.js"></script>';
}

add_action('wp_head', 'add_stylesheet_to_head');


function onlypass_payment_init($value) {
    if(class_exists( 'WC_Payment_Gateway')){
		require_once plugin_dir_path( __FILE__ ) . '/includes/payment-gateway-onlypass.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/onlypass-order-statuses.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/onlypass-checkout-description-fields.php';
        require_once plugin_dir_path( __FILE__ ) . '/includes/payment-page.php';
	}
}

function add_to_payment_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_OnlyPass';
    return $gateways;
}
function add_checkout_script() {

    ?>
    <script >

   const g  = setInterval(()=>{
       const x = jQuery("#place_order");
       if(x.attr("type") !== "button")
       {
           clearInterval(g)
           // x.attr("onclick","PayNow()")
       }
       // x.attr("type","button");
   },1000)
   const f  = setInterval(()=>{
       var form = jQuery(".checkout");
       if(form.length != 0) {
           clearInterval(f)
           jQuery(form[0]).on("submit",function (e) {
               e.preventDefault();
               // alert(window.location.hash)

                   var formObj = {};
                   var emptyInput = [];
                   var whitelist = ['billing_address_2', 'billing_postcode', 'shipping_address_2', 'shipping_postcode', 'order_comments'];
                   jQuery.each(jQuery(e.target).serializeArray(), (i, d) => {
                       formObj[`${d.name}`] = d.value.trim();
                       if (d.value == "" && !whitelist.includes(d.name)) {
                           emptyInput.push(d.name)
                       }
                   });
                   formObj.payment_gateway_list = convertHexToBinary(formObj.payment_gateway_list);
                   // console.log("formObj:",formObj)
                    if(formObj.firstCall == 1) {
                        // console.log(formObj, emptyInput)

                        if (String(formObj.payment_gateway_list.gateway.name).toLowerCase() == "paystack" && emptyInput.length == 0) {
                            jQuery("#firstCall").val("3")
                            let handler = PaystackPop.setup({
                                key: `${formObj.payment_gateway_list.publicKey}`, // Replace with your public key
                                email: `${formObj.billing_email}`,
                                amount: parseFloat(formObj.totalAmount) * 100,
                                currency: `${formObj.currencyType}`,
                                ref: `${formObj.onlyPassRef}`, // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
                                // label: "Optional string that replaces customer email"
                                onClose: function () {
                                    jQuery("#firstCall").val("1")
                                },
                                callback: function (response) {
                                    let message = 'Payment complete! Reference: ' + response.reference;
                                    // jQuery.post(window.location.href,{paymentObj:formObj},(res)=>{
                                    //    console.log(res)
                                    // })
                                    jQuery("#firstCall").val("2")
                                    jQuery('form.checkout').submit();
                                }
                            });
                            handler.openIframe();
                        } else if (String(formObj.payment_gateway_list.gateway.name).toLowerCase() == "flutterwave" && emptyInput.length == 0) {
                            jQuery("#firstCall").val("3")
                            FlutterwaveCheckout({
                                public_key: `${formObj.payment_gateway_list.publicKey}`,
                                tx_ref: `${formObj.onlyPassRef}`,
                                amount: parseFloat(formObj.totalAmount),
                                currency: `${formObj.currencyType}`,
                                country: "NG",
                                payment_options: "card,mobilemoney,ussd,banktransfer,paga,qr,mpesa,account",
                                redirect_url: `#processing`,
                                meta: {},
                                customer: {
                                    email: `${formObj.billing_email}`,
                                    phone_number: `${formObj.billing_phone}`,
                                    name: `${formObj.billing_first_name} ${formObj.billing_last_name}`,
                                },
                                callback: function (data) {
                                    jQuery("#firstCall").val("2")
                                    jQuery('form.checkout').submit();
                                },
                                onclose: function () {
                                    // close modal
                                    jQuery("#firstCall").val("1")
                                }
                            });
                        }
                    }else  if(formObj.firstCall == 2){
                        // jQuery(e.target).trigger("reset");
                        window.location.href = "/cart";
                    }
           })
       }
       },1000)
   function convertBinaryToHex(s)
   {
           s = JSON.stringify(s);
           var i;
           var l;
           var o = '';
           var n;
           s += '';
           for (i = 0, l = s.length; i < l; i++) {
               n = s.charCodeAt(i).toString(16);
               o += n.length < 2 ? '0' + n : n;
           }
           return o;
   }
    function convertHexToBinary(hex)
   {
           var string = '';
           for (var i = 0; i < hex.length; i += 2) {
               string += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
           }
           return JSON.parse(string);
   }
    </script>
<?php }

add_action( 'woocommerce_after_checkout_form', 'add_checkout_script' );

?>