<?php
/**
 * pin functions
 * @author Nuarharuha <nhnoah+bay-isra@gmail.com>
 * @version 0.1
 */

function product_total_pin($pid = false, $uid = false){
    echo get_product_total_pin($pid, $uid);
}

function get_product_total_pin($pid = false, $uid = false){
    $activated = count_product_activated_pin($pid, $uid);
    $reserved  = count_product_reserved_pin($pid, $uid);

    return $reserved+$activated;
}

function product_activated_pin($pid = false, $uid = false){
    echo count_product_activated_pin($pid, $uid);
}

function product_reserved_pin($pid = false, $uid = false){
    echo count_product_reserved_pin($pid, $uid);
}

/**
 * get total product activated pin
 */
function count_product_activated_pin($pid = false, $uid = false){
    return count_product_pin_type(PINTYPE::STATUS_ACTIVATED, $pid, $uid);
}

/**
 * get total product reserved pin
 */
function count_product_reserved_pin($pid = false, $uid = false){
    return count_product_pin_type(PINTYPE::STATUS_RESERVED, $pid, $uid);
}

/**
 * get total product pin base on status
 *
 */
function count_product_pin_type($status=PINTYPE::STATUS_RESERVED, $pid = false, $uid = false){
    global $wpdb, $post, $current_user;

    $pid = ($pid == false ) ? $post->ID : $pid;
    $uid = ($uid == false ) ? $current_user->ID : $uid;

    $db = PINTYPE::DB(PINTYPE::DB_PRIMARY);
    $db_stockist = PINTYPE::DB(PINTYPE::DB_PIN_STOCKIST);

    $sql = "SELECT COUNT(p.pin_id) FROM $db p JOIN $db_stockist s ON p.pin_id=s.pin_id WHERE p.status=%s AND s.stockist_id=%d AND p.product_id=%d";

    $query = $wpdb->prepare($sql, $status, $uid , $pid);

    return $wpdb->get_var($query);
}

/**
 * get all user reserved pin
 *
 * @params int $uid user id
 * @params int $pid product id (custom post type id)
 */
function get_reserved_pin($uid){
    global $wpdb;

    $db = PINTYPE::DB(PINTYPE::DB_PIN_STOCKIST);

    $sql = "SELECT * FROM $db WHERE `status`=%s AND `stockist_id`=%d";

    $query = $wpdb->prepare($sql, PINTYPE::STATUS_RESERVED, $uid);

    return $wpdb->get_results($query);
}

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