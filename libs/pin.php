<?php

function get_pin($product_id, $do_count = false){
    global $wpdb;
    
    $results    = false;
    $product_id = (int) $product_id;    
    $table      = $wpdb->prefix.E_pin::DB_TABLE;    
    
    if (!$do_count){
        $sql        = "SELECT * FROM $table WHERE `product_id`=%d";    
        $results    = $wpdb->get_results($wpdb->prepare($sql, $product_id));
    } else {
        $sql        = "SELECT COUNT(*) FROM $table WHERE `product_id`=%d";    
        $results    = $wpdb->get_var($wpdb->prepare($sql, $product_id));
        
        $results    = (empty($results)) ? 0 : $results;
    }
    
    return $results;
}	

function generate_epin($stockist_id=false){
    global $Epin;
    
    $pin = $Epin->get_generate_pin();
    
    if ($stockist_id){
        if ( get_option('epin_append_stockist_id') != false ){
            $pin = '#00'.$stockist_id.'-'.$pin;
        }
    }
    
    return $pin;
}

function get_invoice_metadata($iid){
    global $wpdb;
        
        $iid        = (int) $iid;
        $table      = $wpdb->base_prefix  .mc_products_sales::DB_TABLE_INVOICE;    
        
        $sql        = $wpdb->prepare("SELECT * FROM $table WHERE `invoice_id`=%d",$iid);        
        $invoice    = $wpdb->get_results($sql);
        
        if ($invoice && isset($invoice[0])){
            $invoice = $invoice[0];
        } 
        
        $orders     = get_invoice_meta($iid,'orders'); 
        
        return array($invoice, $orders);    
}

function add_pin_meta($pin_id, $metakey, $meta_value)
{
    
}