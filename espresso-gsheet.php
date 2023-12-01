<?php

/**
 * @link              
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Event Espresso - Google Spreadsheet Integration
 * Plugin URI:        https://wordpress.org/plugins/
 * Description:       Connects Event Espresso with Google Sheets via API. Create event based google spreadsheets and save attendee's information.  
 * Version:           1.0.0
 * Author:            Ramandeep Sandhu
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
# If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

define('EG_FILE_CORE_VERSION_REQUIRED', '4.8.0.rc.0000');

function run_espresso_activation(){
    if (! did_action('AHEE__EE_System__load_espresso_addons')) {
        add_action('admin_notices', 'file_activation_error');
    }
}
add_action('init', 'run_espresso_activation', 1);

function load_espresso_gsheet(){
    
    if (class_exists('EE_Addon')) {
        require_once(plugin_dir_path( __FILE__ ) . 'includes/class-espresso-sheet.php');
        $plugin = new EspressoGSheet();
        $plugin->run();
    } else {
        add_action('admin_notices', 'espresso_promotions_activation_error');
    }
}
add_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_gsheet', 11);

function file_activation_error()
{
    if ( isset( $_GET['activate'] ) ) {
        unset( $_GET['activate'] );
    }
    unset($_REQUEST['activate']);
    if (! function_exists('deactivate_plugins')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    deactivate_plugins( plugin_basename( __FILE__ ) );
    
    ?>
  <div class="error">
    <p><?php printf(__('<b>"Event Espresso - Google Spreadsheet Integration" </b> add on could not be activated. Please ensure that Event Espresso version %1$s or higher is active', 'espresso_gsheet'), EG_FILE_CORE_VERSION_REQUIRED); ?></p>
  </div>
<?php
}
