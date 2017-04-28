jQuery(document).ready(function($) {

  var funnelActions = {

    init: function() {

      this.add_to_cart_modify();
      this.stripe_save_card();
      this.CheckoutPop();
    },


    stripe_save_card: function() {


      $('body').on('click', '.woocommerce-SavedPaymentMethods-new', function() {

        $('#wc-stripe-new-payment-method').prop('checked', true);
        $('p.woocommerce-SavedPaymentMethods-saveNew').addClass('SavedPaymentMethodsNone');

      });


      $("#wc-stripe-new-payment-method").load(function(){


      })

    },


    add_to_cart_modify: function() {


      $(document).on("submit", "form.cart", function(evt) {

        var form = this;

        var btn_txt = $(".single_add_to_cart_button").text();

        if (btn_txt != "Checkout")
          return;


        evt.preventDefault();
        $(this).find("button")
          .attr("disabled", "disabled")
          .html("...");

        var productID = $(this).find('input[name="add-to-cart"]').val();

        $.ajaxSetup({
            async: false
        });

        var data = {
          'action': 'the_product_upsell',
          'productID': productID      // We pass php values differently!
        };

        //jQuery.post(plugin_data.ajax_url, data);

        $.ajaxSetup({
            async: true
        });

        //console.log(productID);

        $(this).find("button")
          .removeAttr("disabled");

        //form.submit();

        jQuery.post($(this).attr('action'), $(this).serialize(), function() {

          //window.location.href = window.location.protocol+"//"+window.location.host+"/cart";

          jQuery.post(plugin_data_wcf.ajax_url, {'action' : 'add_upsell_product'}, function(data) {

              //console.log(data);

              window.location.href = plugin_data_wcf.checkout_url;

          });



        });

      })

    },

    CheckoutPop: function() {

      $(document).on('click', '.single_add_to_cart_button', function(event) {

        event.preventDefault();

        console.log(  $('.woocommerce-main-image').magnificPopup({type:'image'}));

      })

      $('.single_add_to_cart_button').magnificPopup({
        items: [
          {
            src: '.attachment-shop_single.size-shop_single.wp-post-image', // CSS selector of an element on page that should be used as a popup
            type: 'inline'
          }
        ],
        type: 'image'

      });



    }

  }


  funnelActions.init();

})
