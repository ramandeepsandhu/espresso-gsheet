<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Expresso Events - Google Sheet Integration</h2>
    
    <?php settings_errors('espress_gsheet_settings'); ?>

    <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=espresso-gsheet&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
    </h2>

    <?php 
    if ( isset( $credential['client_email'] ) ) { ?>
        <p><b> Service Account email address : </b></p>
        <textarea id="credentialFinal" name="espresso_gsheet_google_credential" cols="80" rows="8"  class="large-text" disabled > 
            <?php 
                echo  "*** Your account is successfully integrated with Google spreadsheet with this service account email address :  " . esc_html( $credential['client_email'] ) ;
            ?>
        </textarea>
        <br><br>
        <?php 
    echo  "<a href=" . admin_url( 'admin-post.php?action=google_settings&deleteCredential=1&nonce=' ) . wp_create_nonce( 'wpgsi-google-nonce-delete' ) . " class='button-secondary' style=' text-decoration: none; color: #7f7f7f;'>  Remove Credential  </a>" ;

    $ret = $this->googleSheet->token_validation_checker();
    //print_r($ret);
    # Checking token is valid || Display it
    //print_r($ret);
    if ( $ret[0] ) {
        echo  "<span style='vertical-align: middle;padding-top: 5px;' class='dashicons dashicons-yes'> </span>" ;
        # if valid it will show tick
    } else {

        echo  "<span style='vertical-align: middle;padding-top: 5px;' class='dashicons dashicons-no'>  </span>" ;
        # if false it will Show cross
    }
    
    ?>
            <?php 
} else {

    //$ret = $this->googleSheet->token_validation_checker();
    //print_r($ret);
    ?>
    
    <form method="post" action="options.php">

        <?php 
            settings_fields( 'espress_gsheet_settings' );              
            do_settings_sections( 'espress_gsheet_settings' );
        ?>

    <?php 
        submit_button();
    ?>          
    </form>
<?php }?>
</div>


<p>
            <i>
                <span> 
                    <b>1.</b> <code><b>step-by-step</b></code>  instructions for creating  <a href="<?php 
echo  admin_url( 'admin.php?page=espresso-gsheet-help' ) ;
?>" style='text-decoration: none;' target="_blank" > Google Service account & Service account credentials </a>
                </span>
                <br><br>

                <span> 
                    <b>2.</b> If your integration <b> didn't send data to Google Sheets </b> or didn't respond to the event, Please Delete that integration and create a new one.</a>
                </span>
                <br><br>

                <span> 
                    <b>3.</b> Please share your google spreadsheet with your service account email. Otherwise, it will not work.
                </span>
                <br><br>

                <span> 
                    <b>4.</b> You can only update and create WordPress post type, users and database table from Google sheet. ( users and database table are in the professional version )
                </span>
                <br><br>

                <span> 
                    <b>5.</b> If USER ID or POST ID  is not present then a new user or post will be created. remember it may create it also setting dependent.
                </span>
                <br><br>

                <span> 
                    <b>6.</b> All default WordPress events are in the <b> Free version </b>. enjoy ! 3rd party plugin events are in the Professional version. Though, some events are in the Free version to see the functionality.
                </span>
                <br><br>

                <span> 
                    <b>7.</b> You can use the Professional version for 7 days as a Trial.
                </span>
                <br><br>

                <span> 
                    <b>8.</b> Spreadsheet Integration uses <code> <a style='text-decoration: none;' href='https://github.com/woocommerce/woocommerce/blob/master/templates/checkout/thankyou.php'> woocommerce_thankyou</a></code> Hook for WooCommerce Checkout Page orders so it will  <b>  not </b> work 
                    without any thank you page. Please make sure you have a thank you page for WooCommerce. 
                </span> 
                <br><br>

                <span> 
                   <b>9.</b> Professional version supports custom post type that is created with wordpress default <code><a style='text-decoration: none;' href='https://developer.wordpress.org/reference/functions/register_post_type'> register_post_type()</a></code> Function . The professional version also supports <b> MetaData </b> as a data source.
                </span> 
                <br><br>

                <span> 
                   <b>10.</b> Every WordPress installation is <b>different</b>, their version, PHP version, MySql version, even hosting environments are different, so please follow the installation instructions carefully.
                </span> 
                <br><br>

                
                
                <!-- For Paid User  -->
                <?php 
?>
                
               
            </i>
        </p>