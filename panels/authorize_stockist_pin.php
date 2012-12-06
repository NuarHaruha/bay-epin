<?php global $Epin; do_action('authorize_stockist_pin_transfer', $_REQUEST);?>
<?php 
global $_registered_pages, $wpdb;
         
        $iid        = $_REQUEST['invoice_id'];
        $table      = $wpdb->base_prefix  .mc_products_sales::DB_TABLE_INVOICE;    
        $sql        = "SELECT * FROM $table WHERE `invoice_id`=$iid";
        
        $invoice    = $wpdb->get_results($sql);
        
        if ($invoice && isset($invoice[0])){
            $invoice = $invoice[0];
        } 
        
        $orders     = get_invoice_meta($iid,'orders'); 
        
        $args       = array($invoice, $orders);
        
?>
<?php //var_dump($_registered_pages); ?>
<div class="wrap stockist">
    <div id="icon-ticket" class="icon32"></div>
    <h2 class="">Authorize PIN Transfer</h2>
    <?php settings_errors(); ?> 
    <?php do_action('mc_notification', $_REQUEST);?>
    <?php //var_dump($_REQUEST);?>
    <form name="epin_authpin" method="post">
    <input type="hidden" name="action" value="<?php echo PINTYPE::ACT_AUTHSAVE; ?>">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
    <input type="hidden" name="invoice_id" value="<?php echo $invoice->invoice_id; ?>"/>
    <input type="hidden" name="uid" value="<?php echo $invoice->ordered_by; ?>"/>
    <?php wp_nonce_field(PINTYPE::NONCES);
    /* Used to save closed meta boxes and their order */
    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
    <!-- Rest of admin page here -->
    
				<div id="poststuff" class="post-authpin">
		
					 <div id="post-body" class="metabox-holder columns-<?php echo 2 == get_current_screen()->get_columns() ? '2' : '1'; ?>"> 

						  <div id="post-body-content">
							<?php do_action('content-authpin-stockist'); ?>
						  </div>    

						  <div id="postbox-container-1" class="postbox-container">
                            <div id="side-sortables" class="meta-box-sortables">
						        <?php do_action('authpin-side',$_REQUEST,$args); ?>
                            </div>
						  </div>    

						  <div id="postbox-container-2" class="postbox-container">
						        <?php do_meta_boxes('authpin-normal','normal',null);  ?>
						        <?php do_meta_boxes('authpin-advanced','advanced',null); ?>
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