<?php

/**
 * @link              
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Spreadsheet Integration – Event Espresso 
 * Plugin URI:        https://wordpress.org/plugins/
 * Description:       Spreadsheet Integration, Connects Event Espresso with Google Sheets via API. 
 * Version:           1.0.0
 * Author:            Ramandeep Sandhu
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       esan
 * Domain Path:       /languages
 */
# If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

require plugin_dir_path( __FILE__ ) . 'includes/class-espresso-sheet.php';

function run_espresso_gsheet(){
        $plugin = new EspressoGSheet();
        $plugin->run();
}
    

    # 786
    run_espresso_gsheet();


    






