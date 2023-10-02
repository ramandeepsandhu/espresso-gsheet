<?php

class EspressoGSheet_Events
{
    /**
     * The current Date.
     *
     * @since    1.0.0
     * @access   Public
     * @var      string    $Date    The current version of the plugin.
     */
    public  $Date = "" ;
    /**
     * The current Time.
     * @since    1.0.0
     * @access   Public
     * @var      string    $Time   The current Time.
     */
    public  $Time = "" ;
    /**
     * List of active plugins.
     * @since    1.0.0
     * @access   Public
     * @var      array    $active_plugins     List of active plugins .
     */
    public  $active_plugins = array() ;
    /**
     * Common methods used in the all the classes 
     * @since    3.6.0
     * @var      object    $version    The current version of this plugin.
     */
    public  $common ;
    /**
     * Define the class variables, arrays for Events to use;
     * @since    1.0.0s
     */
    public function __construct( $plugin_name, $version, $common )
    {
        # Set date
        $date_format = get_option( 'date_format' );
        $this->Date = ( $date_format ? current_time( $date_format ) : current_time( 'd/m/Y' ) );
        # set time
        $time_format = get_option( 'time_format' );
        $this->Time = ( $date_format ? current_time( $time_format ) : current_time( 'g:i a' ) );
        # Active Plugins
        $this->active_plugins = get_option( 'active_plugins' );
        # Checking Active And Inactive Plugin
        # Common Methods
        $this->common = $common;
    }

      
}