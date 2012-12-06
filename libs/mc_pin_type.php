<?php
/**
 * PIN type enum & Constant
 * 
 * @package     isralife
 * @category    epin
 * 
 * @author      Nuarharuha 
 * @copyright   Copyright (C) 2012, Nuarharuha, MDAG Consultancy
 * @license     http://mdag.mit-license.org/ MIT License
 * @filesource  http://code.mdag.my/baydura_isralife/src
 * @version     0.1
 * @access      public
 */
 
 final class PINTYPE
 {
    /**
     * db version numbers
     * 
     * @var float
     */
    const DB_VERSION                        = 1.2;
    
    /**
     * Database table
     */
    const DB_PRIMARY                        = 'mc_epin';
    
    const DB_PRIMARY_META                   = 'mc_epin_meta'; 
    
    const DB_PIN_STOCKIST                   = 'mc_epin_stockist';
    
    const DB_PIN_STOCKIST_META              = 'mc_epin_stockist_meta';  
    
    /**
     *  options metakey
     */
    
    /**
     * db version metakey stored in
     * wp options table
     * 
     * @var string
     */
    const MK_DB_VERSION                     = 'mc_epin_db_version';
    
    /**
     *  enum for stockist pin status
     */
    
    const STATUS_RESERVED                   = 'reserved';
    
    const STATUS_REDEEM                     = 'redeem';
    
    const STATUS_ACTIVATED                  = 'activated';
    
    const STATUS_CANCEL                     = 'cancel';
    
    const STATUS_PENDING                    = 'pending';
    
    const STATUS_APPROVED                   = 'approved';
    
    const STATUS_EXPIRED                    = 'expired';    
     
    public  $PIN_STATUS                     = array('reserved','redeem','activated','cancel','pending','approved','expired');
    
    /**
     * EPIN Subpages
     */
    const PAGE_EPIN_TRANSFER                = 'e_pin_transfer'; 
    
    const PAGE_EPIN_AUTH                    = 'e-pin_page_authpin';
    
    /**
     * ACTION page
     */
     const ACT_AUTHORIZE_STOCKIST           = 'authpin';
     
     const ACT_AUTHSAVE                     = 'authpin_save';
     
     /**
      * nonces
      */
     
     const NONCES                           = 'mc_epin';
     
     const URI_TRANSFER_REQUEST             = 'admin.php?page=e_pin_transfer'; 
    
    /**
     * @uses $wpdb wp database object
     * @author Nuarharuha
     * @since 0.1
     * 
     * @param string $name const of PINTYPE::DB_{$}
     * @return string db table name with base prefix
     */
    public static function DB($name)
    {
        global $wpdb;        
        return $wpdb->base_prefix.$name;
    }
    
    /**
     * @return int|float db version  
     */
    public static function VERSION()
    { 
        return (float) get_option(self::MK_DB_VERSION);
    } 
    
    public static function get($name){
        
        if (isset(self::$name)){
            return self::$name;
        }
    }
 } 