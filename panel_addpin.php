<?php global $Epin; do_action('mc_epin_add_pin', $_REQUEST);?>
<div class="wrap stockist">
    <div id="icon-ticket" class="icon32"></div>
    <h2 class="">Add New E-pin</h2>
    <?php settings_errors(); ?> 
    <?php do_action('mc_notification', $_REQUEST);?>
    <?php //var_dump($_REQUEST);?>
    <form name="epin_addnew" method="post">
    <input type="hidden" name="action" value="epin_addnew">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
    <?php wp_nonce_field( 'mc-action-epin');
    /* Used to save closed meta boxes and their order */
    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
    <!-- Rest of admin page here -->
    
				<div id="poststuff">
		
					 <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>"> 

						  <div id="post-body-content">
							<?php call_user_func('mb_add_pin'); ?>
						  </div>    

						  <div id="postbox-container-1" class="postbox-container">
						        <?php do_meta_boxes('','side',null); ?>
						  </div>    

						  <div id="postbox-container-2" class="postbox-container">
						        <?php do_meta_boxes('','normal',null);  ?>
						        <?php do_meta_boxes('','advanced',null); ?>
						  </div>	     					

					 </div> <!-- #post-body -->
                     
				 </div> <!-- #poststuff -->            
    
    </form>
    <script>
        jQuery(document).ready(function($){
           $('.fade').fadeOut('slow'); 
        });
    </script>    
</div>