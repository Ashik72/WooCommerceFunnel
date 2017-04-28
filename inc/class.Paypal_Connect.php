<?php
if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

    use PayPal\Api\Amount;
    use PayPal\Api\Details;
    use PayPal\Api\Item;
    use PayPal\Api\ItemList;
    use PayPal\Api\Payer;
    use PayPal\Api\Payment;
    use PayPal\Api\RedirectUrls;
    use PayPal\Api\Transaction;
    use PayPal\Auth\OAuthTokenCredential;
    use PayPal\Rest\ApiContext;

class Paypal_Connect {


  private static $instance;

  public static function get_instance() {
  	if ( ! isset( self::$instance ) ) {
  		self::$instance = new self();
  	}

  	return self::$instance;
  }


    function __construct()  {

      //add_action( 'wp_footer', array($this, 'footer_data') );
      add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
      // add_action( 'template_redirect', array($this, 'receieve_stripe_data') );
      // add_action( 'template_redirect', array($this, 'direct_buy') );
      //add_action('load_textdomain', [$this, 'paypal_create_payment_url_func']);

      add_action( 'wp_ajax_paypal_create_payment_url', array($this, 'paypal_create_payment_url_func') );
      add_action( 'wp_ajax_nopriv_paypal_create_payment_url', array($this, 'paypal_create_payment_url_func') );
      //add_action( 'wp_footer', array($this, 'paypal_create_payment_url_func') );


    }


    public function paypal_create_payment_url_func() {

        $success_page = "";
        $not_success_page = "";
        ob_start();

        $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

        $pp_enabled = $titan->getOption('is_pp_sand');

        $pp_key = NULL;
        $pp_secret = NULL;

        if (empty($pp_enabled)) {
          $pp_key = $titan->getOption('pp_production_c_api_key');
          $pp_secret = $titan->getOption('pp_production_c_api_key_secret');

        } else {

          $pp_key = $titan->getOption('pp_sandbox_c_api_key');
          $pp_secret = $titan->getOption('pp_sandbox_c_api_key_secret');

        }

        if (empty($pp_key) || empty($pp_secret))
          wp_die();


        $apiContext = new ApiContext(new OAuthTokenCredential($pp_key, $pp_secret));

        // echo json_encode([$pp_key,$pp_secret,$apiContext]);
        //
        // wp_die();

        // ### Payer
        // A resource representing a Payer that funds a payment
        // For paypal account payments, set payment method
        // to 'paypal'.
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // ### Itemized information
        // (Optional) Lets you specify item wise
        // information
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(7.5);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);

        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

        // ### Additional payment details
        // Use this optional field to set additional
        // payment information such as tax, shipping
        // charges etc.
        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);

        // ### Amount
        // Lets you specify a payment amount.
        // You can also specify additional details
        // such as shipping, tax.
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal(20)
            ->setDetails($details);

        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it.
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());
        // ### Redirect urls
        // Set the urls that the buyer must be redirected to after
        // payment approval/ cancellation.


        $baseUrl = get_site_url();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($baseUrl)
            ->setCancelUrl($baseUrl);

        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent set to 'sale'
        $payment = new Payment();


        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));


        // For Sample Purposes Only.
        $request = clone $payment;

        // ### Create Payment
        // Create a payment by calling the 'create' method
        // passing it a valid apiContext.
        // (See bootstrap.php for more on `ApiContext`)
        // The return object contains the state and the
        // url to which the buyer must be redirected to
        // for payment approval

        $payment_data = "";

        try {

            $payment_data = $payment->create($apiContext);
            echo json_encode([ 'paymentID' => $payment_data->getId() ]);
            wp_die();

        } catch (Exception $ex) {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
            // echo json_encode(var_dump($ex));
            // ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            // exit(1);
            echo json_encode(0);
            wp_die();
        }

        // ### Get redirect url
        // The API response provides the url that you must redirect
        // the buyer to. Retrieve the url from the $payment->getApprovalLink()
        // method
        $approvalUrl = $payment->getApprovalLink();

        $output = ob_get_clean();

        echo json_encode($payment_data);

      wp_die();

    }

    public function enqueue_scripts() {

      wp_register_script( 'wc_funnel_nci-general-script-paypalcheckout', 'https://www.paypalobjects.com/api/checkout.js', array( 'jquery' ), '', false );
      wp_enqueue_script( 'wc_funnel_nci-general-script-paypalcheckout' );

    }

    public static function Paypal_render($data = null) {
      $titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );

      $pp_enabled = $titan->getOption('is_paypal_enabled');
      if (empty($pp_enabled))
         return;
      //
      // $data_key = $titan->getOption('stripe_publishable_key');
      //
      // if (empty($data_key))
      //   return;

      ob_start();
      $this_product = new WC_Product($data['product_id']);

      ?>

      <span id="paypal-button"></span>


      <?php
      $output = ob_get_clean();
      return $output;
    }

}

?>
