jQuery(document).ready(function($) {

  var stripe_custom = {

    init: function() {

      //this.venoBox();
      //this.paypal2();
      //this.paypal();
    },

    venoBox: function() {

      /* default settings */
      $('.venobox').venobox();


      /* custom settings */
      $('.venobox_custom').venobox({
          framewidth: '70%',        // default: ''
          frameheight: '600px',       // default: ''
          border: '10px',             // default: '0'
          bgcolor: '#fff',         // default: '#fff'
          titleattr: 'data-title',    // default: 'title'
          numeratio: true,            // default: false
          infinigall: true            // default: false
      });


    },

    paypal: function() {
      paypal.Button.render({

          env: ( (plugin_data_pp_stripe_custom.pp_env == 1) ? 'sandbox' : 'production' ) , // Specify 'sandbox' for the test environment


          client: {
              sandbox:    plugin_data_pp_stripe_custom.pp_sandbox_key,
              production: plugin_data_pp_stripe_custom.pp_production_key
          },

          payment: function() {
              // Set up the payment here, when the buyer clicks on the button
                          var env    = this.props.env;
                          var client = this.props.client;

                          return paypal.rest.payment.create(env, client, {
                              transactions: [
                                  {
                                      amount: { total: '1.00', currency: 'USD' }
                                  }
                              ]
                          });


          },

          commit: true, // Optional: show a 'Pay Now' button in the checkout flow

          onAuthorize: function(data, actions) {

                        return actions.payment.execute().then(function() {
                            // Show a success page to the buyer
                            console.log("success page");

                        });
                      }

      }, '#paypal-button');

    },

    paypal2: function() {



      paypal.Button.render({

          env: ( (plugin_data_pp_stripe_custom.pp_env == 1) ? 'sandbox' : 'production' ) , // Specify 'sandbox' for the test environment


          client: {
              sandbox:    plugin_data_pp_stripe_custom.pp_sandbox_key,
              production: plugin_data_pp_stripe_custom.pp_production_key
          },

          payment: function() {
              // Set up the payment here, when the buyer clicks on the button

            var CREATE_PAYMENT_URL = plugin_data_pp_stripe_custom.ajax_url;
            var gotData = "";
            var paypal_req = paypal.request.post(CREATE_PAYMENT_URL, { 'action' : 'paypal_create_payment_url', 'paypal_create_payment_url' : 1 })
              .then(function(data) {
                console.log(data.paymentID);

                resolve(data.paymentID);
              })
              .catch(function(err) {
                //reject(err);
                console.log(err);
              });

          },

          commit: true, // Optional: show a 'Pay Now' button in the checkout flow

          onAuthorize: function(data, actions) {

                        return actions.payment.execute().then(function() {
                            // Show a success page to the buyer
                            console.log("success page");

                        });
                      }

      }, '#paypal-button');

    }

  }


  stripe_custom.init();

})
