<?php
/*
Plugin Name: MDAG E-Pin
Plugin URI: http://mdag.my
Description: PIN Management
Version: 1.0.0
Author: Nuar, MDAG Consultancy
Author URI: http://mdag.my
License: MIT License
License URI: http://mdag.mit-license.org/
*/		
setlocale(LC_ALL, 'ms_MY');

class E_pin
{
    public $version = '1.0.0';
    
    public $plugin_url;
    
    public $plugin_path;
    
    public $menu;
    
    public $page;
    
    public $page_addnew;
    
    public $page_transferpin;
    
    public $sub_page;
    
    public $slug = 'e_pin';
    
    public $settings_slug = 'e_pin_settings';
    
    public $addpin_slug = 'e_pin_addnew';
    
    public $transferpin_slug = 'e_pin_transfer';
    
    public $cap = 'manage_options';
    
    const DB_TABLE = 'mc_epin';
    
    const DB_TABLE_META = 'mc_pinmeta'; 
    
    const MK_GENERATOR = 'epin_generator_type';
    
    public $products;
    
    const DEFAULT_STATUS = 'available';
    
    public function __construct()
    {
        $this->_init();
    }
    
    private function _init()
    {
        $this->plugin_path = dirname(__FILE__).'/';
        $this->plugin_uri = WP_PLUGIN_URL.'/mc_epin/';
        
        foreach(array('mc_pin_type','mc_pin_install','utils','pin','mc_metabox') as $file): 
            $this->load_libs($file.'.php'); endforeach;        
        
        if (is_admin()){
            add_action('admin_init', array(&$this, 'register_admin_styles'));
            add_action('admin_init', array(&$this, 'register_admin_scripts'));
            add_action('admin_menu', array(&$this, 'register_admin_menu'));  
           /**
            * metabox 
            */ 
            add_action('add_meta_boxes', array(&$this,'init_settings_metabox'));  
            add_action('mc_epin_settings',array(&$this, 'save_settings'));   
            
            add_action('mc_epin_add_pin',array(&$this,'add_newpin'),1,1);  
            
            
        }        
    }
    
    public function register_admin_menu()
    {   global $_registered_pages;
    
        $title  = 'E-Pin';
        $pos    = 12;
        $icon   = $this->img_url('epin-16.png');
        
        $this->menu = add_menu_page($title,$title, $this->cap, $this->slug, array(&$this,'panel_admin'), $icon, $pos);   
        
        add_action( 'admin_print_styles-' . $this->menu, array(&$this,'admin_styles') );  
        add_action( 'admin_print_scripts-' . $this->menu, array(&$this,'admin_scripts') );
        
        add_action('load-'.$this->menu,  array(&$this,'page_actions'),9);
		add_action('admin_footer-'.$this->menu,array(&$this,'footer_scripts'));            
        //add_action('load-'.$this->menu,  array($this,'page_actions'),9);
        
        $title = 'Add PIN';        
        
        $this->page_addnew = add_submenu_page($this->slug, $title, $title, $this->cap, $this->addpin_slug, array(&$this,'panel_addpin'));
                        
        add_action( 'admin_print_styles-' . $this->page_addnew, array(&$this,'admin_styles') );
        add_action( 'admin_print_scripts-' . $this->page_addnew, array(&$this,'admin_scripts') );
        
        add_action('load-'.$this->page_addnew,  array($this,'page_actions'),9);
		add_action('admin_footer-'.$this->page_addnew, array($this,'footer_scripts')); 
                 

        $title = 'Stockist PIN Request';        
        
        $this->page_transferpin = add_submenu_page($this->slug, $title, $title, $this->cap, $this->transferpin_slug, array(&$this,'panel_transferpin'));
                        
        add_action( 'admin_print_styles-' . $this->page_transferpin, array(&$this,'admin_styles') );
        add_action( 'admin_print_scripts-' . $this->page_transferpin, array(&$this,'admin_scripts') );
        
        add_action('load-'.$this->page_transferpin,  array($this,'page_actions'),9);
		add_action('admin_footer-'.$this->page_transferpin,array($this,'footer_scripts'));      
        add_action('load-'.$this->page_transferpin,  array($this,'stockist_authpin_save'),9);
        //add_action('authorize_stockist_pin_transfer',array($this,'stockist_authpin_save'),1);           
                
        $title = 'Settings';        
        
        $this->page = add_submenu_page($this->slug, $title, $title, $this->cap, $this->settings_slug, array(&$this,'panel_settings'));
                        
        add_action( 'admin_print_styles-' . $this->page, array(&$this,'admin_styles') );
        add_action( 'admin_print_scripts-' . $this->page, array(&$this,'admin_scripts') );
        
        add_action('load-'.$this->page,  array($this,'page_actions'),9);
		add_action('admin_footer-'.$this->page,array($this,'footer_scripts'));     
        
        /**
         * non menu page
         */
        $this->page['authpin'] = $authpin_hook = get_plugin_page_hookname("authpin", $this->slug);        
        $GLOBALS['_registered_pages'][$authpin_hook] = true;  
        
    }
    
    public function load_authpin_page()
    {
        require $this->plugin_path.'panels/authorize_stockist_pin.php';
    }
    
    public function init_settings_metabox()
    {  global $_registered_pages;
        // primary metabox
		add_meta_box('generator_options','Pin Generator', 'mb_select_pin_generator',$this->get_page(),'normal','high');        
        
        // sidebar
        add_meta_box('pin_formating_options','Formatting Options', 'mb_general_options',$this->get_page(),'side','high');
		//add_meta_box('stockist_summary','Summary',array($this,'metabox_summary'),$this->get_page(),'side','high');   
        
        /**
         * Pin Transfer
         */
        global $wpdb;
         
        $iid        = $_REQUEST['invoice_id'];
        $table      = $wpdb->base_prefix  .mc_products_sales::DB_TABLE_INVOICE;    
        $sql        = "SELECT * FROM $table WHERE `invoice_id`=$iid";
        
        $invoice    = $wpdb->get_results($sql);
        
        if ($invoice && isset($invoice[0])){
            $invoice = $invoice[0];
        } 
        
        $orders     = get_invoice_meta($iid,'orders'); 
        
        $args = array($invoice, $orders);
        
        add_meta_box('authpin_summary','Request Summary','mb_stockist_authpin','authpin-normal','normal','high', $args); 
        
        add_meta_box('authpin_orders','Order Details','mb_stockist_authpin_items','authpin-advanced','advanced','high', $args);        
        
        // authpin-side
        add_action('authpin-side','mb_stockist_authpin_stockist_info',1, 2);
        add_action('authpin-side','mb_stockist_authpin_stockist_action',1, 2);
        
        
        // auth page
            
    }    
    
    public function save_settings($request)
    {
        $meta_keys = array('epin_generator_type','epin_append_stockist_id');
        
        if (isset($request['section']) && !isset($request['epin_append_stockist_id']) ){
            if ($request['section'] == 'pin_format'){
                delete_option( 'epin_append_stockist_id');
            }
        }        
        
        if (isset($request['action'])){
            foreach($meta_keys as $meta_key){
                if (isset($request[$meta_key])){
                    //we save here
                    
                    $option_name = $meta_key ;
                    $new_value = $request[$meta_key] ;
                    
                    if ( get_option( $option_name ) != $new_value ) {
                        update_option( $option_name, $new_value );
                    } else {
                        $deprecated = ' ';
                        $autoload = 'no';
                        add_option( $option_name, $new_value, $deprecated, $autoload );
                    }  
                                      
                } 
            }
        }
    }
    
    public function add_newpin($request)
    {        
        if (!isset($request['product_id']) || !has_count($request['product_id'])) return;
        
        $items = array();  
          
        foreach($request['product_id'] as $index => $pid){
            
            if ( isset($request['quantity'][$pid]) )
            {
                // make sure its valid integer
                $quantity = (int) $request['quantity'][$pid];
                
                if (!empty($quantity)){
                    array_push($items, array( $pid => $quantity));
                }
            }
        } 
        
       if ( !empty($items) && has_count($items)){
            $this->products = $items;
       }
       
       unset($items, $request);
       
       $this->save_pin();
    }
    
    public function save_pin()
    {   global $wpdb;
        
        $products = $this->products;
        $result = false;
        $insert_id = array();
        
        if (count($products) >= 0 && !empty($products)){   
            
            $table = $wpdb->prefix.self::DB_TABLE;            
            
            foreach($products as $index)
            {                
                $product_id = key($index);
                $count      = array_sum($index);                

                            
                $format = array('%s','%d','%s');
                
                for($i=1; $i <= $count; $i++){
                    $result = $wpdb->insert( $table, array(
                            'code'=> $this->get_generate_pin(),
                            'product_id'=> $product_id,                     
                            'status'=> self::DEFAULT_STATUS), $format );
        
                    if($result){
                        array_push($insert_id, $result);
                    }                    
                }
            }
            if (count($insert_id) >= 0 ){
                $this->insert_id = $insert_id;
                
                do_action('after_save_pin',$insert_id, $_POST);
                
                add_action('mc_notification', array(&$this,'insert_success') );
            }
        }
    }
    
    public function insert_success()
    {
        t('div','<p>Insert Success!</p>',array('id'=>'message','class'=>'updated success fade'));
    }
    
    public function get_generate_pin()
    {
         $engine    = get_option(self::MK_GENERATOR);         
         return $this->get_pin($engine);       
    }
    

    
    public function product_post_hook($post_id, $post)
    {
        
          if(!is_object($post) || !isset($post->post_type)) {
                return;
            }
        
            switch($post->post_type) { // Do different things based on the post type
        
                case "products":
                    $this->generate_pin($post_id);
                    break;        
                default:
                    // Do other stuff
                    break;
        
            }
        
    }
    
    public function get_generator()
    {
        return get_option(self::MK_GENERATOR);
    }
    
    public function get_page()
    {
        return $this->page;
    }
    
    public function get_top_menu()
    {
        return $this->menu;
    }
    
    public function get_page_addnew()
    {
        return $this->page_addnew;
    }
        
    public function footer_scripts()
    {
        $scripts = "jQuery(document).ready(function($){
           $('.go-back').click(function(e){
                e.preventDefault(); window.history.go(-1); });
                postboxes.add_postbox_toggles(pagenow);
        });";
        t('script',$scripts);
    }
    
    
    public function page_actions($hook = false)
    {   
		do_action('add_meta_boxes_'.$this->page, null);
		do_action('add_meta_boxes', $this->page, null);        
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		wp_enqueue_script('postbox');    
     
    }
    
    public function process_request()
    {
        var_dump($_REQUEST);
        
    }
    
    public function get_pin($type)
    {
        $cb = false;
        $type = (int) $type;
        
        switch ($type){
            case 1:$cb = 'epin_rand_pad'; break;
            case 2: $cb = 'epin_rand_string';break;
            case 3: $cb = 'epin_rand_key'; break;
            case 4: $cb = 'epin_rand_pairing_4'; break;
        }   
        
        return call_user_func($cb);
    }

    public function panel_admin()
    {
        require $this->plugin_path.'panel_admin.php';
    }
    
    public function panel_settings()
    {
        require $this->plugin_path.'panel_settings.php';
    }
    
    public function panel_transferpin()
    {
        if (isset($_REQUEST['page']) && $_REQUEST['page'] == PINTYPE::PAGE_EPIN_TRANSFER){
            
            if (!isset($_GET['action']) || $_GET['action'] != PINTYPE::ACT_AUTHORIZE_STOCKIST ){
                require $this->plugin_path.'panels/stockist_request.php';
            } else {
                require $this->plugin_path.'panels/authorize_stockist_pin.php';
            }
        }
    }
    
    public function stockist_authpin_save()
    {   global $wpdb;      
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == PINTYPE::ACT_AUTHSAVE ){
            
            
            
            // we have a request lets grab the invoice first
            
            $req = foreach_push($req, $_POST);
            
            list($invoice, $orders) = get_invoice_metadata($req->invoice_id);
            
            // now we have invoice & items metadata
            
            $this->invoice_id = $req->invoice_id;
            
            /** update invoice status to approved */
            $this->update_invoice_status(PINTYPE::STATUS_APPROVED);
            
            /**
             * create reserved pin by products
             */
                        
            // loops products
            foreach ($orders['product_id'] as $pid => $total_amount):
            
                // loops over product unit
                $quantity = (int) $orders['quantity'][$pid];
                
                for ($i=1; $i <= $quantity; $i++){
                    $meta = array(
                        'code'          => $this->get_pin($this->get_generator()) ,
                        'product_id'    => (int) $pid,
                        'status'        => PINTYPE::STATUS_RESERVED,
                        'invoice_id'    => (int) $req->invoice_id
                    );
                    
                    /** primary sql insert */
                    $pin_id = $this->insert_pin($meta);

                    $this->add_bonus_pv($req->uid, $meta['product_id'],$meta['invoice_id']);

                    if ($pin_id){                   
                        
                        /** prepare stockist pin insert data */
                        $stockist = array(
                            'pin_id'      => (int) $pin_id,
                            'stockist_id' => (int) $req->uid,
                            'status'      => PINTYPE::STATUS_RESERVED,                        
                        );
                        
                        $result = $this->insert_pin_stockist($stockist);                                         
                    
                    } // if.result
                } // end for
                
               
            endforeach;
            
            wp_redirect(PINTYPE::URI_TRANSFER_REQUEST);
            exit();
             
        }
    }

    public function add_bonus_pv($uid, $pid, $invoice_id)
    {
        $meta       = get_post_custom($pid);
        $pv         = $meta[mc_products::MK_PV][0];
        $sku        = $meta[mc_products::MK_SKU][0];
        $invoice    = mc_get_invoice_id($invoice_id, $uid);

        add_product_pv_bonus($uid, $sku, $invoice, $pv);
    }
    
    public function update_invoice_status($status)
    {   global $wpdb;
    
        $id = $this->invoice_id;
        $db = $wpdb->base_prefix.'mc_invoices';
        
        $meta = array(
            'order_status'  => $status,
            'modified_date' => date("Y-m-d H:m:s",$_SERVER['REQUEST_TIME']),
            'modified_by'   => _current_user_id() );
        
        $row_count = $wpdb->update($db, $meta, array('invoice_id'=>$id), array('%s'), array('%d'));
        
        if ($row_count){
            do_action('update_invoice_status',$id, $status);
        }
        
    }   
    
    public function add_pin_meta()
    {
        
    }
    
    public function add_pin_stockist_meta()
    {
        
    }
    
    /**
     *  insert pin data to primary db
     *  
     */
    public function insert_pin($meta, $format = array('%s','%d','%s'))
    {   global $wpdb;
    
        $db = PINTYPE::DB(PINTYPE::DB_PRIMARY);
        
        $pin_id = $wpdb->insert($db, $meta, $format);   
                        
        if ($pin_id){
            do_action('insert_pin', $wpdb->insert_id, $meta, $_REQUEST);
            return $pin_id;
        } else {
            return $false;
        }         
    }
    
    public function insert_pin_stockist($meta, $format = array('%d','%d','%s'))
    {   global $wpdb;
        
        $db = PINTYPE::DB(PINTYPE::DB_PIN_STOCKIST);
        
        $stockist_pin = $wpdb->insert($db, $meta, $format);   
                        
        if ($stockist_pin){
            do_action('insert_pin_stockist', $stockist_pin, $meta, $_REQUEST);
            return $stockist_pin;
        } else {
            return $false;
        }        
    }

    public function panel_addpin()
    {
        require $this->plugin_path.'panel_addpin.php';
    }    
    
    public function admin_styles() {
       wp_enqueue_style( 'mc_pin_stylesheet' );
    } 

    public function admin_scripts() {
       //wp_enqueue_script( 'jquery' );
       wp_enqueue_script( 'jqprint' );
    }     
       
    public function register_admin_styles()
    {
        wp_register_style( 'mc_pin_stylesheet', plugins_url('/libs/styles.css', __FILE__) );
    }

    public function register_admin_scripts()
    {
        wp_register_script( 'jqprint', plugins_url('/libs/print.js', __FILE__), array('jquery'), false,true);
    }    
    
    public function load_libs($file){
        require $this->plugin_path.'libs/'.$file;
    }
    
    public function img_url($file){
        return $this->plugin_uri.'img/'.$file;
    }    
}

$Epin = new E_pin();

/** plugin setup installation, run once */
register_activation_hook( __FILE__ , 'mc_epin_on_activate_install_db');
function mc_epin_on_activate_install_db(){	mc_epin_install_db(); }

register_uninstall_hook(__FILE__, 'mc_epin_uninstall_options');
 
function mc_epin_uninstall_options()
{
}