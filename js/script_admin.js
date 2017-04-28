jQuery(document).ready(function($) {

  var funnelAdmin = {

    init: function() {

      this.test_connection();

    },

    test_connection: function() {

      if ($(".wc_funnel_connection_status").length === 0)
        return;

        var data = {
          'action' : 'check_connection_with_db'
        };

        $.ajaxSetup({
            async: true
        });

      jQuery.post(plugin_wc_funnel_admin.ajax_url, data, function(response) {

        response = $.parseJSON(response)

        if (response.stat === 0) {
          $(".wc_funnel_connection_status").html("<span style='color:red'>"+response.msg+"</span>")
          return;
        }

        console.log(response.msg.length);

        if (parseInt(response.stat) === 1 && response.msg.length > 0) {
          $(".wc_funnel_connection_status").html("<span style='color:green'>Connected with server.</span>")
          $(".wc_funnel_connection_status").html($(".wc_funnel_connection_status").html()+" <span style='color:green'>"+response.msg+"</span>")

        }

      });

      $.ajaxSetup({
          async: true
      });

      console.log("later");

    },

    isUserLoggedIn: function() {

      var data = {
        'action' : 'isUserLoggedIn'
      };

    jQuery.post(plugin_wc_funnel_admin.ajax_url, data, function(response) {

      //response = $.parseJSON(response)

      console.log(response);

    });


    }

  }


  funnelAdmin.init();
  //funnelAdmin.isUserLoggedIn();

})
