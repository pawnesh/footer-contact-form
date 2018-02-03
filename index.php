<?php
/*
Plugin Name:  Footer Contact Form
Description:  Basic customaizable contact form stick to footer
Version:      1.0.2
Author:       Pawnesh Rai
Author URI:   https://github.com/pawnesh
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define( 'FCFH__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( FCFH__PLUGIN_DIR . 'admin.php' );


register_activation_hook( __FILE__, 'fcfh_activate' );

function fcfh_activate(){
    global $wpdb;

	$table_name = $wpdb->prefix . 'fcfh_enquiry';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		email tinytext NOT NULL,
		contact tinytext NOT NULL,
		enquiry text NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

add_action('wp_footer', 'addFCFHtml');
function addFCFHtml() {
    ?>
    <style type="text/css">
        .fcfh_core{
            background: #ccc;
            position: fixed;
            bottom: 0;
            width: 100%;
            padding-top: 10px;
            padding-bottom: 10px;
            text-align: center;
            z-index: 1000;
        }
        .fcfh_core label{
            display: inline;
        }
        .fcfh_core input{
            display: inline;
            width: auto;
        }
        <?php echo get_option('fcfh_extra_css');?>
    </style>
    <form method="post" onSubmit="fcfh_save_enquiry(event)" >
    <div class="fcfh_core">
        <div id="fcfch_form_error"></div>
        <label>
            <input type="text" name="fcfh_name" placeholder="Your Name"/>
        </label>
        <label>
            <input type="email" name="fcfh_email" placeholder="Email"/>
        </label>
        <label>
            <input type="text" name="fcfh_contact" placeholder="Contact"/>
        </label>
        <label>
            <input type="text" name="fcfh_enquiry" placeholder="Enquiry"/>
        </label>
        <input type="hidden" name="action" value="fcfh_action"/>
        <button type="submit">Save</button>
    </div>
    </form>
    <?php
}

add_action( 'wp_footer', 'fcfh_action_javascript' );
function fcfh_action_javascript() { ?>
	<script type="text/javascript" >
	function fcfh_save_enquiry(event) {
        var data = jQuery(event.target).serialize();
		jQuery.post('<?php echo admin_url( 'admin-ajax.php' );?>', data, function(response) {
			if(!response.status){
                jQuery('#fcfch_form_error').text(response.message);
            }else{
                if(response.redirect){
                    window.location.href = response.url;
                }else{
                    jQuery('.fcfh_core').html(response.message);
                }
            }
		});
        event.preventDefault();
        return false;
	};
	</script> <?php
}

add_action( 'wp_ajax_fcfh_action', 'fcfh_action_save' );
add_action('wp_ajax_nopriv_fcfh_action', 'fcfh_action_save');
function fcfh_action_save() {
	global $wpdb; 
    $output = [];
    $output['status'] = true;
    if(isset($_POST['fcfh_email']) AND empty($_POST['fcfh_email'])){
        $output['status'] = false;
        $output['message'] = "email is required";
    }
    if($output['status'] == true){
        $table = $wpdb->prefix . 'fcfh_enquiry';
        $data =array( 
            'time' => date('Y-m-d H:i:s'), 
            'name' => $_POST['fcfh_name'], 
            'email' => $_POST['fcfh_email'], 
            'contact' => $_POST['fcfh_contact'], 
            'enquiry' => $_POST['fcfh_enquiry'] 
        );
        $format = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        );
        $response = $wpdb->insert( $table, $data, $format );
        if($response !== false){
            $output['message'] = "Your enquiry submited";
            $url = get_option('fcfh_thank_you_page');
            if(!empty($url)){
                $output['redirect'] = true;
                $output['url'] = $url;
            }
        }else{
            $output['status'] = false;
            $output['message'] = "Sorry! unable to save enquiry";
        }
    }
    header("content-type: json/text");
    echo json_encode($output);
	wp_die(); // this is required to terminate immediately and return a proper response
}

