<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'admin_menu', 'fcfh_plugin_menu' );
function fcfh_plugin_menu() {
	add_options_page( 'Footer Enquiry', 'Footer Enquiry', 'manage_options', 'fcfh-enquiry-setting', 'fcfh_enquiry_settings' );
    add_management_page( 'Footer Enquiry Report', 'Footer Enquiry', 'export', 'fcfh-enquiry-report', 'fcfh_enquiry_report' );
    add_action( 'admin_init', 'register_fcfh_enquiry_plugin_settings' );
}

function fcfh_enquiry_report(){
     if ( !current_user_can( 'export' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    
?>
<div class="wrap">
    <h1>Footer Enquiry Report</h1>
    <a href="tools.php?page=fcfh-enquiry-report&fcfh_csv_backup=true" target="_blank" class="button button-primary">Download CSV</a>
</div>
<?php
}
function fcfh_enquiry_settings(){
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    ?>
    <div class="wrap">
    <h1>Footer Enquiry Settings</h1>

    <form method="post" action="options.php">
        <?php settings_fields( 'fcfh-enquiry-plugin-settings-group' ); ?>
        <?php do_settings_sections( 'fcfh-enquiry-plugin-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row">Thank you page url</th>
            <td><input type="text" name="fcfh_thank_you_page" value="<?php echo esc_attr( get_option('fcfh_thank_you_page') ); ?>" /></td>
            </tr>
             
            <tr valign="top">
            <th scope="row">Extra CSS</th>
            <td>
                <textarea name="fcfh_extra_css"><?php echo esc_attr( get_option('fcfh_extra_css') ); ?></textarea>
            </td>
            </tr>
            
        </table>
        
        <?php submit_button(); ?>

    </form>
    </div>

    <?php
}

function register_fcfh_enquiry_plugin_settings(){
    if(isset($_GET['fcfh_csv_backup'])){
        global $wpdb;
        $table_name = $wpdb->prefix . 'fcfh_enquiry';
        $sql = "SELECT id,name,email,contact,enquiry,time FROM $table_name";
        $result = $wpdb->get_results( $sql, OBJECT );
        if(empty($result)){
            die("No enquiry submited");
        }
        header("content-type: text/csv");
        header("Content-Disposition: attachment; filename=fcfh_".date('Y-m-d').".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo "S.No,Name,Email,Contact,Time".PHP_EOL;
        foreach($result as $enquiry){
            echo $enquiry->id.','.$enquiry->name.','.$enquiry->email.',"'.$enquiry->contact.'","'.$enquiry->time.'"'.PHP_EOL;
        }
        die();
    }
    register_setting( 'fcfh-enquiry-plugin-settings-group', 'fcfh_thank_you_page' );
    register_setting( 'fcfh-enquiry-plugin-settings-group', 'fcfh_extra_css' );
}

