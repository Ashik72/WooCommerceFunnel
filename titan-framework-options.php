<?php

if (!defined('ABSPATH'))
  exit;


add_action( 'tf_create_options', 's2_sensei_custom_options_wc_funnel_nci', 150 );

function s2_sensei_custom_options_wc_funnel_nci() {


	$titan = TitanFramework::getInstance( 'wc_funnel_nci_opts' );
	$section = $titan->createAdminPanel( array(
		    'name' => __( 'WooCommerce Funnel Integration', 'wc_funnel_nci' ),
		    'icon'	=> 'dashicons-feedback'
		) );

	$tab = $section->createTab( array(
    		'name' =>  __( 'General Options', 'wc_funnel_nci' )
		) );


    $tab->createOption( array(
    'name' => 'WooCommerce Consumer Key',
    'id' => 'wc_cs_key',
    'type' => 'text',
    'desc' => '<a target="_blank" href="'.get_admin_url().'/admin.php?page=wc-settings&tab=api&section=keys">WooCommerce > Settings > API > Keys/Apps</a>'
    ) );

    $tab->createOption( array(
    'name' => 'WooCommerce Consumer Secret',
    'id' => 'wc_cs_secret',
    'type' => 'text',
    'desc' => '<a target="_blank" href="'.get_admin_url().'/admin.php?page=wc-settings&tab=api&section=keys">WooCommerce > Settings > API > Keys/Apps</a>'
    ) );

    $tab->createOption( array(
    'name' => 'WC Funnel Email',
    'id' => 'wc_funnel_mail',
    'type' => 'text',
    'desc' => 'Grab from ...'
    ) );

    $tab->createOption( array(
    'name' => 'WC Funnel Password',
    'id' => 'wc_funnel_password',
    'type' => 'text',
    'desc' => 'Grab from ...'
    ) );

    $tab->createOption( array(
    'name' => 'My Unique Secrect',
    'id' => 'wc_funnel_usecret',
    'type' => 'text',
    'desc' => '<strong>A unique key or phrase.</strong>'
    ) );


    $tab->createOption( array(
    'type' => 'custom',
    'name' => 'Connection Status',
    'custom' => '<div class="wc_funnel_connection_status">Checking...</div>'
    ) );

    $tab = $section->createTab( array(
      		'name' =>  __( 'Stripe Connect', 'wc_funnel_nci' )
  		) );

      $tab->createOption( array(
      'name' => 'Enable Stripe',
      'id' => 'is_stripe_enabled',
      'type' => 'enable',
      'default' => false,
      'desc' => 'Enable or disable Stripe',
      ) );
      $tab->createOption( array(
        'name' => 'Publishable Key',
        'id' => 'stripe_publishable_key',
        'type' => 'text',
        'desc' => 'Stripe Publishable Key'
        ) );


          $tab->createOption( array(
            'name' => 'Secret Key',
            'id' => 'stripe_secret_key',
            'type' => 'text',
            'desc' => 'Stripe Secret Key'
            ) );

        $tab->createOption( array(
        'name' => 'Enable Test Mode',
        'id' => 'is_stripe_test_mode_enabled',
        'type' => 'enable',
        'default' => false,
        'desc' => 'Enable or disable Stripe Test Mode [Publishable and Secret Key MUST be TEST key]',
        ) );

        $tab->createOption( array(
          'name' => 'Stripe Checkout Image',
          'id' => 'stripe_checkout_img',
          'type' => 'text',
          'desc' => 'Stripe Checkout Image',
          'default' => 'https://stripe.com/img/documentation/checkout/marketplace.png',

          ) );

          $tab = $section->createTab( array(
            		'name' =>  __( 'PayPal Connect', 'wc_funnel_nci' )
        		) );

            $tab->createOption( array(
            'name' => 'Enable PayPal',
            'id' => 'is_paypal_enabled',
            'type' => 'enable',
            'default' => false,
            'desc' => 'Enable or disable PayPal',
            ) );

            $tab->createOption( array(
            'name' => 'Sandbox Environment?',
            'id' => 'is_pp_sand',
            'type' => 'enable',
            'default' => false,
            'desc' => 'Enable or disable Sandbox Environment',
            ) );

            $tab->createOption( array(
              'name' => 'Sandbox Client ID',
              'id' => 'pp_sandbox_c_api_key',
              'type' => 'text',
              'desc' => 'PayPal Sandbox Client ID',
              'default' => '',

              ) );

              $tab->createOption( array(
                'name' => 'Sandbox Client Secret',
                'id' => 'pp_sandbox_c_api_key_secret',
                'type' => 'text',
                'desc' => 'PayPal Sandbox Client Secret',
                'default' => '',

                ) );

              $tab->createOption( array(
                'name' => 'Production Client ID',
                'id' => 'pp_production_c_api_key',
                'type' => 'text',
                'desc' => 'PayPal Production Client ID',
                'default' => '',

                ) );

                $tab->createOption( array(
                  'name' => 'Production Client Secret',
                  'id' => 'pp_production_c_api_key_secret',
                  'type' => 'text',
                  'desc' => 'PayPal Production Client Secret',
                  'default' => '',

                  ) );

		$section->createOption( array(
  			  'type' => 'save',
		) );


    $section_2 = $section->createAdminPanel( array(
  		    'name' => __( 'Funnel Design', 'wc_funnel_nci' ),
  		    'icon'	=> 'dashicons-image-filter'
  		) );

      $html_name = '<div class="funnel_name_title"><h2>Funnel Name</h2></div><br>
  <div class="funnel_name_input"><input id="funnel_name_input_id" class="funnel_name_input_class" type="text" name="funnel_name"></div>';

      $section_2->createOption( array(
      'name' => ' ',
      'id' => 'funnely_title',
      'type' => 'custom',
      'custom' => $html_name
      ) );

      $section_2->createOption( array(
      'type' => 'iframe',
      'url' => plugin_dir_url(__FILE__).'interface/',
      'height' => 1368
      ) );


		/////////////New

/*		$embroidery_sub = $section->createAdminPanel(array('name' => 'Embroidering Pricing'));


		$embroidery_tab = $embroidery_sub->createTab( array(
    		'name' => 'Profiles'
		) );


		$wp_expert_custom_options['embroidery_tab'] = $embroidery_tab;

		return $wp_expert_custom_options;
*/
}


 ?>
