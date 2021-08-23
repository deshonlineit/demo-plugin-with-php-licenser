<?php
/*
Plugin Name: Plic Sample Plugin
Version: v1.0
Plugin URI: https://www.tipsandtricks-hq.com
Author: Tips and Tricks HQ
Author URI: https://www.tipsandtricks-hq.com/
Description: Sample plugin to show you how you can interact with the software license manager API from your WordPress plugin or theme
*/

/* ======== Check license active or not and start plugin main codes use anywhere ========*/

if (get_option('license_status') == 'active'):

            add_action('admin_menu', 'test_menu_registered');

            function test_menu_registered() {

            add_menu_page('Test plugin menu ', 'Test plugin','manage_options','test-plugin', 'test_plugin_cb', 'dashicons-lock',5);
            }

            function test_plugin_cb(){


                echo "<h2 style='color:red; margin:10% auto; text-align:center;'>This page showing mean pluign license activated</h2>";
            }

endif;




/*========PHP licenser programe start here ========*/
/* The License Activation or Deactivation API secret key check at Integration Guide page  */
define('YOUR_SPECIAL_SECRET_KEY', '6111e6df832290.71064695');
/* This is the website URL where PHP licenser installed  check at Integration Guide page*/
define('YOUR_LICENSE_SERVER_URL', 'https://md-nazmul.com');
/* Product Reference which you put in the license creation period this is unique */
define('YOUR_ITEM_REFERENCE', 'testplugin');
/*=====================================================================================*/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'plic_add_action_links' );

function plic_add_action_links ( $actions ) {
if (get_option('license_status') == 'active') {
   $mylinks = array(
'<a href="' . admin_url( 'admin.php?page=sample-license' ) . '"><b style="color:green;">Active License<b></a>',
);
    }
    else{

 $mylinks = array(
'<a href="' . admin_url( 'admin.php?page=sample-license' ) . '"><b style="color:red;">Active License<b></a>',
);
    }

$actions = array_merge( $actions, $mylinks );
return $actions;
}
add_action('admin_menu', 'plic_license_menu_registered');
function plic_license_menu_registered() {
// We can add a submenu if our plugin it has admin menu or make new plugin admin menu
add_submenu_page(null, 'License Activation page','License Activation page', 'manage_options','sample-license', 'sample_license_management_page');
}


/*==========redirect after activation ==============*/
register_activation_hook(__FILE__, 'nht_plugin_activate');
add_action('admin_init', 'nht_plugin_redirect');

function nht_plugin_activate() {
add_option('nht_plugin_do_activation_redirect', true);
}

function nht_plugin_redirect() {
if (get_option('nht_plugin_do_activation_redirect', false)) {
    delete_option('nht_plugin_do_activation_redirect');
    if(!isset($_GET['activate-multi']))
    {
        wp_redirect("admin.php?page=sample-license");
    }
 }
}
/* ====================== Plugin menu callback function ======================= */
function sample_license_management_page() {
echo '<div class="wrap">';
    echo '<h2>Plugin Activation page</h2><br>';
    if (get_option('license_status') == 'active') {
    echo '<b>License Status: </b>'.get_option('license_status').'<br>';
    echo '<b>License Exp : </b>'.get_option('license_exp');
    }
    else{

        echo "<b style='color:red;'>License Not Active, Please Enter your key and hit Active</b>";
    }

    if (isset($_REQUEST['activate_license'])) {
    $license_key = $_REQUEST['sample_license_key'];
    $api_params = array(
    'slm_action' => 'slm_activate',
    'secret_key' => YOUR_SPECIAL_SECRET_KEY,
    'license_key' => $license_key,
    'registered_domain' => $_SERVER['SERVER_NAME'],
    'item_reference' => urlencode(YOUR_ITEM_REFERENCE),
    );
    $query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL));
    $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));
    if (is_wp_error($response)){
    echo "Unexpected Error! The query returned with an error.";
    }
    $license_data = json_decode(wp_remote_retrieve_body($response));
    
    if($license_data->result == 'success'){

    echo '<br /><b style="color:green;">'.$license_data->message.'<span style="color:red;">   Please refresh the page</span></b>';

    update_option('sample_license_key', $license_key);
    plic_license_key_status_handler();
  
    }
    else{
    
    echo '<br />The following message was returned from the server: '.$license_data->message;
    }
    }
 /* ======================== End of license activation ======================================== */
    
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
    $license_key = $_REQUEST['sample_license_key'];
    // API query parameters
    $api_params = array(
    'slm_action' => 'slm_deactivate',
    'secret_key' => YOUR_SPECIAL_SECRET_KEY,
    'license_key' => $license_key,
    'registered_domain' => $_SERVER['SERVER_NAME'],
    'item_reference' => urlencode(YOUR_ITEM_REFERENCE),
    );
    $query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL));
    $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));
    // Check for error in the response
    if (is_wp_error($response)){
    echo "Unexpected Error! The query returned with an error.";
    }
    $license_data = json_decode(wp_remote_retrieve_body($response));
    
    if($license_data->result == 'success'){
    echo '<br />The following message was returned from the server: '.$license_data->message;
    
    //Remove the licensse key from the options table. It will need to be activated again.
    delete_option('sample_license_key');
    plic_license_key_status_handler();
    }
    else{
    echo '<br />The following message was returned from the server: '.$license_data->message;
    plic_license_key_status_handler();
    }
    
    }
/* ==================== End of sample license deactivation ============================== */

    if ( get_option('license_status') == 'active'): 
  echo '
    <form class="community-events-form" action="" method="post">
        <input class="medium-text " type="password" id="sample_license_key" name="sample_license_key"  value="'.get_option("sample_license_key").'">
            <input type="submit" name="deactivate_license" value="Dectivate License" class="button " />
    </form>';
   
    endif;
/*================== If Not Active plugin sisplay this form to Active plugin ==============*/
if (! get_option('license_status') == 'active'): 
  echo '
    <form class="community-events-form" action="" method="post">
        <input class="medium-text " type="text" id="sample_license_key" name="sample_license_key"  value="">
            <input type="submit" name="activate_license" value="Activate License" class="button button-primary" />
    </form>';
   
    endif;


echo '</div>';
}
//wp_schedule_event(time(), 'hourly', 'plic_license_key_status_handler', $args);

/*====================== Check License key Status =======================*/
// Add a new interval of 300 seconds
add_filter( 'cron_schedules', 'php_licencer_isa_add_every_three_minutes' );
function php_licencer_isa_add_every_three_minutes( $schedules ) {
    $schedules['every_three_minutes'] = array(
            'interval'  => 300,
            'display'   => __( 'Every 5 Minutes', 'textdomain' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'php_licencer_isa_add_every_three_minutes' ) ) {
    wp_schedule_event( time(), 'every_three_minutes', 'php_licencer_isa_add_every_three_minutes' );
}

// Hook into that action that'll fire every three minutes
add_action( 'php_licencer_isa_add_every_three_minutes', 'every_three_minutes_event_func' );
function every_three_minutes_event_func() {
  
$to = 'php673500@gmail.com';
$subject = 'The subject';
$body = 'The email body content';
$headers = array('Content-Type: text/html; charset=UTF-8','From: Shedule work <deshonlineit@gmail.com>');
 
wp_mail( $to, $subject, $body, $headers );
plic_license_key_status_handler();

}







//plic_license_key_status_handler();
function plic_license_key_status_handler(){
if (!empty(get_option('sample_license_key'))) {
        $license_keys = get_option('sample_license_key');
    }
else{

   $license_keys = '1234567'; 
}
$license_keys = get_option('sample_license_key');
$secret_key = '6111e6df832290.71064695';
$license_key_url = 'https://md-nazmul.com';

$api_params = array(
'slm_action' => 'slm_check',
'secret_key' => '6111e6df832290.71064695',
'license_key' => $license_keys,
);
// Send query to the license manager server
$response = wp_remote_get(add_query_arg($api_params, $license_key_url), array('timeout' => 30, 'sslverify' => false));
$license_datas = json_decode(wp_remote_retrieve_body($response));

if ($license_datas->result == 'success') {

    update_option('license_status',$license_datas->status);
    update_option('license_exp',$license_datas->date_expiry);
}
else{

    delete_option('license_status' );
    delete_option('license_exp' );
}
}
/* ================ Display Admin notice if License key not active =============*/
if (! get_option('license_status')):

 function sample_admin_notice__success() {

    $plugin_data = get_plugin_data( __FILE__ );
$plugin_name = $plugin_data['Name'];
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( '<b>'.$plugin_name.'</b> License not Active <a href="admin.php?page=sample-license">Active Now</a>'); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'sample_admin_notice__success' );
endif;