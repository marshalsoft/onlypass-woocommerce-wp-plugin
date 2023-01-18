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
//add_action('wp_enqueue_scripts','js_init');
add_action('wp_head', 'add_stylesheet_to_head');
function js_init()
{
//    wp_enqueue_script( 'banis', 'https://bani-assets.s3.eu-west-2.amazonaws.com/static/widget/js/window.js',  null, null, true );
    wp_enqueue_script( 'baniJs',plugins_url('/includes/js/bani.js?v='.uniqid(), __FILE__ ));
    wp_enqueue_script( 'paystack', 'https://js.paystack.co/v2/inline.js',  null, null, true );
    wp_enqueue_script( 'flutterwave', 'https://checkout.flutterwave.com/v3.js',  null, null, true );
    wp_enqueue_script( 'squad', 'https://checkout.squadco.com/widget/squad.min.js',  null, null, true );
}
function add_stylesheet_to_head() {
    $path = plugins_url('/includes/js/bani.js?v='.uniqid(), __FILE__ );
//    echo "<style >.payment_method_onlypass > label{background: #212529 !important;color: transparent !important;display:none !important;} .payment_method_onlypass > label > img {float: left !important;display:none !important;} #payment_method_onlypass{opacity: 0} .payment_method_onlypass > label::before{content:'' !important;} </style>";
    echo '<script src="'.$path.'" ></script>';
    echo '<script src="https://js.paystack.co/v2/inline.js"></script>';
    echo '<script src="https://checkout.flutterwave.com/v3.js"></script>';
    echo '<script src="https://checkout.squadco.com/widget/squad.min.js"></script>';
}

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
function add_checkout_script (){
    global $gateways;
    $r = hex2bin($gateways);
    ?>
    <script >
 var url = "https://api.onlypassafrica.com/api/v1/external/payments";
var $ = jQuery;
var xhr;
window.genRef = null;
window.gatewaylist = JSON.parse('<?php echo $r; ?>');
function generateRef()
{
    var text = "";
    var tm = new Date().getMilliseconds();
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 15; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    return text+tm;
}
function InitializePayment()
{
  return new Promise((resolve)=>{
      if(window.genRef == null)
      {
          window.genRef = {extRef:generateRef()};
          resolve(window.genRef)
      }else{
          resolve(window.genRef)
      }
  })
}

   // const g  = setInterval(()=>{
   //     const x = $("#place_order");
   //     if(x.attr("type") !== "button")
   //     {
   //         clearInterval(g)
   //         // x.attr("onclick","PayNow()")
   //     }
   //     // x.attr("type","button");
   // },1000)
       var form = jQuery("form.checkout");
       form.on("submit",function (e) {
               e.preventDefault();
               // alert(window.location.hash)
           jQuery(".woocommerce-error").show();
                   var formObj = {PKey:""};
                   var emptyInput = [];
                   var whitelist = ['billing_address_2', 'billing_postcode', 'shipping_address_2', 'shipping_postcode', 'order_comments','isLive'];
                   jQuery.each(form.serializeArray(), (i, d) => {
                       formObj[`${d.name}`] = d.value.trim();
                       if (d.value.trim() == "" && !whitelist.includes(d.name)) {
                           if(d.name == "billing_email" || d.name == "billing_first_name" || d.name == "billing_last_name" || d.name == "billing_phone") {
                               if (d.name == "billing_email") {
                                   d.name = "email address";
                               } else if (d.name == "billing_first_name") {
                                   d.name = "first name";
                               } else if (d.name == "billing_last_name") {
                                   d.name = "last name";
                               } else if (d.name == "billing_phone") {
                                   d.name = "phone number";
                               } else {

                               }
                               // if(d.name == "billing_phone")
                               // {
                               //     const chk = checkNumber(formObj[`${d.name}`]);
                               //     if(!chk) {
                               //         d.name = "a valid phone number";
                               //     }
                               // }
                               // if(d.name == "billing_email")
                               // {
                               //     const x = formObj[`${d.name}`].split("@");
                               //     const xs = formObj[`${d.name}`].split(".");
                               //     if(x.length == 0 || xs.length == 0)
                               //     {
                               //         d.name = "a valid email address";
                               //     }
                               // }
                               emptyInput.push({name: d.name})
                           }
                       }
                   });
                    // console.log("formObj:", formObj);
                    formObj.payment_gateway_list = convertHexToBinary(formObj.payment_gateway_list);
                    formObj.gatewayName = String(formObj.payment_gateway_list.gateway.name).toLowerCase();
                   
                    if(formObj.payment_method !== 'onlypass')
                   {
                       // jQuery('form.checkout').submit();
                   }else if(formObj.payment_gateway_list == undefined) {
                        alert("Oops! select one of payment gateways from OnyPass gateway list.");
                    }else if(formObj.payment_channel == undefined && formObj.gatewayName != "bani") {
                        alert("Oops! select one of payment channel.");
                   }else{
                           if (formObj.isLive == 1) {
                               formObj.PKey = formObj.payment_gateway_list.livePublicKey;
                           } else {
                               formObj.PKey = formObj.payment_gateway_list.testPublicKey;
                           }
                           formObj.gatewayId = String(formObj.payment_gateway_list.gateway.gatewayId).toLowerCase();
                           delete formObj.payment_gateway_list;

                           if (emptyInput.length !== 0) {
                               // console.log("emptyInput", emptyInput)
                               alert(`Oops! ${emptyInput[0].name} is a required field`);
                           } else if (formObj.firstCall == 1) {
                               //   return;
                                   if (formObj.env === "test")
                                   {
                                       url = String(url).replace("api.","devapi.");
                                   }
                               jQuery(".woocommerce-error").hide();
                               InitializePayment().then((ref) => {
                                   var settings = {
                                       "url": url,
                                       "method": "POST",
                                       "timeout": 0,
                                       "headers": {
                                           "Content-Type": "application/json",
                                           "Accept": "application/json",
                                           "x-api-key": `${formObj.apikey}`,
                                           "x-platform-id": `${formObj.merchant_id}`
                                       },
                                       "data": JSON.stringify({
                                           "gatewayId": formObj.gatewayId,
                                           "externalReference": `${ref.extRef}`,
                                           "amount": formObj.totalAmount,
                                           "isDemo": formObj.isLive == 1 ? false : true
                                       }),
                                   };

                                //    console.log("settings:", settings);
                                   if (window.genRef.call == undefined) {
                                       jQuery.ajax(settings).done(function (res) {
                                           // return;
                                           if (res.status) {
                                               window.genRef = Object.assign(res.data, window.genRef, {call: 1}, formObj);
                                               formObj = window.genRef;
                                               CallGateways(formObj);
                                           }
                                       });
                                   } else {
                                       formObj = Object.assign(window.genRef, formObj);
                                       CallGateways(formObj);
                                   }
                               })
                           } else if (formObj.firstCall == 2) {
                               // jQuery(e.target).trigger("reset");
                               window.genRef = null;
                               window.location.href = window.location.href + "/order-received";
                           }
                   }
               })
function checkNumber(num = "")
{
    const codelist = ["0701","07020","07025","07026","07027","07028","07029","07029","0703","0704","0705","0706","0707","0708","0709","0802","0803","0804","0805","0806","0807","0808","0809","0810","0811","0812","0813","0814","0815","0816","0817","0818","0819","0909","0908","0901","0902","0903","0905","0906,0907","0915","0913","0912","0916"];
    const cd = String(num).substring(0,4);
    const cd2 = String(num).substring(0,5);
    if(String(num).length != 11)
    {
        return false;
    }
    if(codelist.includes(cd))
    {
        return true;
    }
    if(codelist.includes(cd2))
    {
        return true;
    }

    return false;
}
function successCallback(reference = "")
{
    jQuery("#firstCall").val("2");
    jQuery('form.checkout').submit();
}

function Aborted(formObj = {}) {
        var settings = {
            "url": `${url}/${formObj.onlyPassReference}`,
            "method": "PATCH",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "x-api-key": `${formObj.apikey}`,
                "x-platform-id": `${formObj.merchant_id}`
            },
            "data": JSON.stringify({})
        };
    jQuery("#firstCall").val("1");
    jQuery.ajax(settings);
}
function CallGateways(formObj)
{
    // console.log("formObj:",formObj);
    // console.log("genRef:",window.genRef);
    // return ;
    if (String(formObj.gatewayName).toLowerCase().includes("paystack")) {
        jQuery("#firstCall").val("3")
      const x = {
          channels:[`${formObj.payment_channel}`],
          key: `${formObj.PKey}`, // Replace with your public key
          email: `${formObj.billing_email}`,
          amount: parseFloat(formObj.amountToPay) * 100,
          currency: `${formObj.currencyType}`,
          ref: `${formObj.onlyPassReference}`, // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
          // label: "Optional string that replaces customer email"
          onClose: function () {
              Aborted(formObj);
          },
          callback: function (response) {
              let message = 'Payment complete! Reference: ' + response.reference;
              // jQuery.post(window.location.href,{paymentObj:formObj},(res)=>{
              //    console.log(res)
              // })
              successCallback(response.reference)

          }
      }
        // console.log("PaystackPop:",x);
        let handler = window.PaystackPop.setup(x);
        handler.openIframe();
    } else if (String(formObj.gatewayName).toLowerCase().includes("flutterwave")) {
        jQuery("#firstCall").val("3")
        window.FlutterwaveCheckout({
            public_key: `${formObj.PKey}`,
            tx_ref: `${formObj.onlyPassReference}`,
            amount:parseFloat(formObj.amountToPay),
            currency: `${formObj.currencyType}`,
            country: "NG",
            payment_options:`${formObj.payment_channel}`,
            redirect_url: `#processing`,
            meta: {},
            customer: {
                email: `${formObj.billing_email}`,
                phone_number: `${formObj.billing_phone}`,
                name: `${formObj.billing_first_name} ${formObj.billing_last_name}`,
            },
            callback: function (data) {
                successCallback(data);
            },
            onclose: function () {
                Aborted(formObj);
            }
        });
    } else if (String(formObj.gatewayName).toLowerCase().includes("squad")) {
        jQuery("#firstCall").val("3")
        const dd = {
            onClose: () =>Aborted(formObj),
            onLoad: () => {},
            onSuccess: (response) =>successCallback(response),
            key: `${formObj.PKey}`,
            email:`${formObj.billing_email}`,
            amount:parseFloat(formObj.amountToPay) * 100,
            currency_code: `${formObj.currencyType}`,
            transaction_ref: `${formObj.onlyPassReference}`,
            payment_channels:[`${formObj.payment_channel}`],
            Customer_name:`${formObj.billing_first_name} ${formObj.billing_last_name}`,
        };
        // console.log(dd);
       const squadInstance = new squad(dd);
            squadInstance.setup();
            squadInstance.open();
    }else if (String(formObj.gatewayName).toLowerCase().includes("bani")) {

        const bsni  = {
            amount:formObj.amountToPay,
            phoneNumber:formObj.billing_phone,
            email:`${formObj.billing_email}`,
            firstName:`${formObj.billing_first_name}`,
            lastName:`${formObj.billing_last_name}`,
            merchantKey:`${formObj.PKey}`,
            merchantRef:`${formObj.onlyPassReference}`,
            metadata: "",
            onClose: (response) => {
                Aborted(formObj);
            },
            callback: function (response) {
                successCallback(response);
            }
        }
        // console.log("bani:",bsni);
        let handler = BaniPopUp(bsni);
        handler
    }
}
function update_shipping()
{


   }
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