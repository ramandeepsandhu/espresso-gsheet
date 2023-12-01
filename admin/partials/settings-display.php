<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Event Expresso - Google Sheet Integration</h2>
    
    <?php settings_errors('espress_gsheet_settings'); ?>

    <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=espresso-gsheet&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
    </h2>
    <?php EspressoGSheet_Settings::handle_form();?>
    
    <p>
        <form method="post" >
            <?php 
                settings_fields( 'espress_gsheet_settings' );              
                do_settings_sections( 'espress_gsheet_settings' );
            ?>
            <?php submit_button();?>          
        </form>
    </p>
</div>


<p>
    <i>
        <span> 
            <b>1.</b> <code><b>step-by-step</b></code>  instructions for creating  <a href="<?php echo  admin_url( 'admin.php?page=espresso-gsheet-help' ) ;?>" style='text-decoration: none;' target="_blank" > Google Service account & Service account credentials </a>
        </span>
        <br><br>

        <span> 
            <b>2.</b> If your integration <b> didn't send data to Google Sheets </b> or didn't respond to the event, Please Delete that integration and create a new one.</a>
        </span>
        <br><br>

        <span> 
            <b>3.</b>** User's permission will be applied to newly created Google Sheet.
        </span>
        <br><br>
  
    </i>
</p>