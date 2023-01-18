<?php

/**
 * OnlyPass Payments Gateway.
 *
 * Provides a OnlyPass Payments Payment Gateway.
 *
 * @class       WC_Gateway_OnlyPass
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
class WC_Gateway_OnlyPass extends WC_Payment_Gateway {
    public function __construct() {
        // Setup general properties.
        $this->setup_properties();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        // Get settings.
        // call endpoin to verify apikey
        global $env;
        global $apikey;
        global $merchantId;
        global $gateways;
        global $currency;
        global $isLive;
        global $baseURL;
        $env = "live";
        $apikey = $this->get_option('api_key');
        $merchantId = $this->get_option('merchent_id');
        $gateways =  $this->get_option('onlypass_gateways');
        $currency =  $this->get_option('onlypass_currency_type');
        $isLive =  $this->get_option('onlypass_isLive');
        $baseURL = "https://api.onlypassafrica.com/api/v1/external/payments";
//        echo ;
        if($env == "test")
        {
            $baseURL = str_replace("api.","devapi.",$baseURL);
        }
        if(empty($currency))
        {
            $currency = "NGN";
        }
        if((isset($_POST["woocommerce_onlypass_api_key"]) && empty($_POST["woocommerce_onlypass_api_key"])) || (isset($_POST["woocommerce_onlypass_merchent_id"]) && empty($_POST["woocommerce_onlypass_merchent_id"]))) {
            if($apikey !== null) {
                $this->update_option('api_key', '');
                $this->update_option('merchent_id', '');
                $this->update_option('onlypass_currency_type', '');
                $this->update_option('onlypass_gateways', 'NGN');
            }
        }elseif(isset($_POST["woocommerce_onlypass_api_key"]) && isset($_POST["woocommerce_onlypass_merchent_id"])) {
            $apikey = trim($_POST["woocommerce_onlypass_api_key"]);
            $merchantId = trim($_POST["woocommerce_onlypass_merchent_id"]);
            $currency = trim($_POST["woocommerce_onlypass_currency_type"]);
            $isLive = trim($_POST["woocommerce_onlypass_isLive"]);
            if(empty($apikey) && empty($merchantId)) {
                WC_Admin_Settings::add_message(__('Api key and Merchant ID is required' , 'onlypass-payments-woo'));
            }else{
//                if($isLive == 1)
//                {
//                    $baseURL = str_replace("devapi.","api.",$baseURL);
//                }
//                echo $baseURL;
//                die();
            $response = wp_remote_post($baseURL. "/channels", array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array("Authorization" => "application/json", "x-api-key" => $apikey, "x-platform-id" => $merchantId, "Accept" => "application/json", "Content-Type" => "application/json"),
                'body' => array(),
                'cookies' => array()
            ));
            if (is_wp_error($response)) {
                foreach ($response->get_error_messages() as $message) {

                }
            } else
            {
//                echo $body = $response['body'];

                $json_values = json_decode($response['body'], true);
                if (!$json_values["status"]) {
                    $this->update_option('api_key','');
                    $this->update_option('merchent_id','');
                    $this->update_option('onlypass_currency_type','');
                    $this->update_option('onlypass_gateways','');
                    $this->update_option('onlypass_isLive',false);
                    WC_Admin_Settings::add_message(__(esc_html__($json_values["message"] , 'onlypass-payments-woo')));
                } else {
                  $gateways = bin2hex(json_encode($json_values["data"]));
                  if(api_key == null)
                  {
                      $this->add_option('onlypass_api_key',$apikey);
                      $this->add_option('onlypass_merchent_id',$merchantId);
                      $this->add_option('onlypass_gateways',$gateways);
                      $this->add_option('onlypass_currency_type',$currency);
                      $this->add_option('onlypass_isLive',$isLive);
//                      if($isLive == 1)
//                      {
//                          $baseURL = str_replace("devapi.","api.",$baseURL);
//                      }
                  }else{
                      $this->update_option('onlypass_api_key',$apikey);
                      $this->update_option('onlypass_merchent_id',$merchantId);
                      $this->update_option('onlypass_gateways',$gateways);
                      $this->update_option('onlypass_currency_type',$currency);
                      $this->update_option('onlypass_isLive',$isLive);
                  }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
//        $this->title              = $this->get_option('title');
        $this->title        = "Pay online";
        $this->description        = "Select one of the following gateways:";
        $this->api_key            = $this->get_option( 'api_key' );
        $this->merchent_id          = $this->get_option( 'merchent_id' );
        $this->instructions       = $this->get_option( 'instructions' );

        add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );
        add_action( 'woocommerce_thankyou' . $this->id, array( $this, 'thankyou_page' ) );
//        // Customer Emails.
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
                }
            }
        }
        }else if(isset($apikey) && $apikey != null && $merchantId != null){
            $response = wp_remote_post($baseURL. "/channels", array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array("Authorization" => "application/json", "x-api-key" => $apikey, "x-platform-id" => $merchantId, "Accept" => "application/json", "Content-Type" => "application/json"),
                'body' => array(),
                'cookies' => array()
            ));
            if (!is_wp_error($response)){
//                echo $body = $response['body'];
            $json_values = json_decode($response['body'], true);
            $gateways = bin2hex(json_encode($json_values["data"]));
            }
//            die();
        }

    }


    protected function setup_properties() {
        $this->id                 = 'onlypass';
//        $this->icon               = apply_filters('woocommerce_onlypass_icon', plugins_url('../assets/logo.png', __FILE__ ) );
        $this->title        = "Pay online";
        $this->method_title       = __('Pay using OnlyPass', 'onlypass-payments-woo');
        $this->api_key            = __('Add API Key', 'onlypass-payments-woo');
        $this->merchent_id          = __('Add Merchant ID', 'onlypass-payments-woo');
        $this->currency_type          = __('Add Currency', 'onlypass-payments-woo');
        $this->method_description = __( 'Have your customers pay with OnlyPass Payments.', 'onlypass-payments-woo' );
        $this->isLive = __('Enable live', 'onlypass-payments-woo');
        $this->has_fields  = false;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'            => array(
                'title'       => __( 'Enable/Disable', 'onlypass-payments-woo' ),
                'label'       => __( 'Enable OnlyPass Payments', 'onlypass-payments-woo' ),
                'type'        => 'checkbox',
                'description' => __( 'click here to enable plugin', 'onlypass-payments-woo' ),
                'default'     => 'no',
                'required'=>true,
                'desc_tip'    => true,
            ),
            'api_key' => array(
                'title'       => __( 'API Key', 'onlypass-payments-woo' ),
                'type'        => 'text',
                'required'=>true,
                'description' => __( 'Add your API key', 'onlypass-payments-woo' ),
                'desc_tip'    => true,
            ),
            'merchent_id' => array(
                'title'       => __('Merchant ID', 'onlypass-payments-woo'),
                'type'        => 'text',
                'required'=>true,
                'description' => __( 'Add your Merchant Indentification number (Merchant ID)', 'onlypass-payments-woo' ),
                'desc_tip'    => true
            ),
            'currency_type' => array(
                'class'         => array('airport_pickup form-row-wide'),
                'title'       => __('Currency', 'onlypass-payments-woo'),
                'type'        => 'select',
                'description' => __( 'Select Currency', 'onlypass-payments-woo' ),
                'default'=> "NGN",
                'required'=>true,
                'options' => array(
                    'NGN'=>'NGN','USD'=>'USD'),
                'desc_tip'    => true
            ),
            'isLive' => array(
                'title'   => __( 'Enable live', 'onlypass-payments-woo' ),
                'label'   => __( 'Enable go live', 'onlypass-payments-woo' ),
                'type'    => 'checkbox',
                'description' => __( 'Click here to toggle between live and demo', 'onlypass-payments-woo' ),
                'default' => 'no',
                'desc_tip'    => true
            )
        );
    }

    /**
     * Check If The Gateway Is Available For Use.
     *
     * @return bool
     */
    public function is_available() {
        $order          = null;
        $needs_shipping = false;

        // Test if shipping is needed first.
        if ( WC()->cart && WC()->cart->needs_shipping() ) {
            $needs_shipping = true;
        } elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
            $order_id = absint( get_query_var( 'order-pay' ) );
            $order    = wc_get_order( $order_id );

            // Test if order needs shipping.
            if ( 0 < count( $order->get_items() ) ) {
                foreach ( $order->get_items() as $item ) {
                    $_product = $item->get_product();
                    if ( $_product && $_product->needs_shipping() ) {
                        $needs_shipping = true;
                        break;
                    }
                }
            }
        }

        $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

        // Virtual order, with virtual disabled.
        if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
            return false;
        }

        // Only apply if all packages are being shipped via chosen method, or order is virtual.
        if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
            $order_shipping_items  = is_object( $order ) ? $order->get_shipping_methods() : false;
            $chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

            if ( $order_shipping_items ) {
                $canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
            } else {
                $canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
            }

            if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
                return false;
            }
        }

        return parent::is_available();
    }

    /**
     * Checks to see whether or not the admin settings are being accessed by the current request.
     *
     * @return bool
     */
    private function is_accessing_settings() {
        if ( is_admin() ) {
            // phpcs:disable WordPress.Security.NonceVerification
            if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
                return false;
            }
            if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
                return false;
            }

            // phpcs:enable WordPress.Security.NonceVerification

            return true;
        }

        return false;
    }

    /**
     * Loads all of the shipping method options for the enable_for_methods field.
     *
     * @return array
     */
    private function load_shipping_method_options() {
        // Since this is expensive, we only want to do it if we're actually on the settings page.
        if ( ! $this->is_accessing_settings() ) {
            return array();
        }

        $data_store = WC_Data_Store::load( 'shipping-zone' );
        $raw_zones  = $data_store->get_zones();

        foreach ( $raw_zones as $raw_zone ) {
            $zones[] = new WC_Shipping_Zone( $raw_zone );
        }

        $zones[] = new WC_Shipping_Zone( 0 );

        $options = array();
        foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

            $options[ $method->get_method_title() ] = array();

            // Translators: %1$s shipping method name.
            $options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'onlypass-payments-woo' ), $method->get_method_title() );

            foreach ( $zones as $zone ) {

                $shipping_method_instances = $zone->get_shipping_methods();

                foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

                    if ( $shipping_method_instance->id !== $method->id ) {
                        continue;
                    }

                    $option_id = $shipping_method_instance->get_rate_id();

                    // Translators: %1$s shipping method title, %2$s shipping method id.
                    $option_instance_title = sprintf( __( '%1$s (#%2$s)', 'onlypass-payments-woo' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

                    // Translators: %1$s zone name, %2$s shipping method instance name.
                    $option_title = sprintf( __( '%1$s &ndash; %2$s', 'onlypass-payments-woo' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'onlypass-payments-woo' ), $option_instance_title );

                    $options[ $method->get_method_title() ][ $option_id ] = $option_title;
                }
            }
        }

        return $options;
    }

    /**
     * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
     *
     * @since  3.4.0
     *
     * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
     * @return array $canonical_rate_ids    Rate IDs in a canonical format.
     */
    private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {

        $canonical_rate_ids = array();

        foreach ( $order_shipping_items as $order_shipping_item ) {
            $canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
        }

        return $canonical_rate_ids;
    }

    /**
     * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
     *
     * @since  3.4.0
     *
     * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
     * @return array $canonical_rate_ids  Rate IDs in a canonical format.
     */
    private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {

        $shipping_packages  = WC()->shipping()->get_packages();
        $canonical_rate_ids = array();

        if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
            foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
                if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
                    $chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
                    $canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
                }
            }
        }

        return $canonical_rate_ids;
    }

    /**
     * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
     *
     * @since  3.4.0
     *
     * @param array $rate_ids Rate ids to check.
     * @return boolean
     */
    private function get_matching_rates( $rate_ids ) {
        // First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
        return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order->get_total() > 0 ) {
            $this->onlypass_payment_processing( $order);
        }
    }

    private function onlypass_payment_processing($order ) {
//        var_dump( $order);
//        $total = intval( $order->get_total());
//        $gatewayObj = json_decode(hex2bin($_POST["payment_gateway_list"]));
//
        if(isset($_POST["onlypass_order_id"]))
        {
           echo $_POST["onlypass_order_id"];
        }
        global $woocommerce;
        // we need it to get any order detailes
        if(isset($_POST["firstCall"]) && $_POST["firstCall"] == "2") {
//            var_dump($_POST);
            $order = wc_get_order($order);
            $order->update_status('processing');
//            $order->payment_complete();
            $order->reduce_order_stock();
            // some notes to customer (replace true with false to make it private)
            $order->add_order_note('Hey, your order is paid! Thank you!', true);
            $woocommerce->cart->empty_cart();
            // Redirect to the thank you page
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
        }
    }

    /**
     * Change payment complete order status to completed for onlypass orders.
     *
     * @since  3.1.0
     * @param  string         $status Current order status.
     * @param  int            $order_id Order ID.
     * @param  WC_Order|false $order Order object.
     * @return string
     */
    public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
        if ( $order && 'onlypass' === $order->get_payment_method() ) {
//            $status = 'completed';
        }
        return $status;
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order Order object.
     * @param bool     $sent_to_admin  Sent to admin.
     * @param bool     $plain_text Email format: plain text or HTML.
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
            echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
        }
    }
}


