<?php
/**
 * mc_epin_install_db()
 * setup our database, this function should be
 * run on plugin active
 * 
 * @author  nuarharuha
 * @since   1.2
 * @return  void
 */	
function mc_epin_install_db(){
    global $wpdb;
    
    $db = $primary_db = PINTYPE::DB(PINTYPE::DB_PRIMARY);

	if($wpdb->get_var("SHOW TABLES LIKE '".$db."'") != $db || PINTYPE::VERSION() < PINTYPE::DB_VERSION )
    {
	   /**
	    * used KEY instead of INDEX for adding
        * INDEX.
	    */
	   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
       
		$sql = "CREATE TABLE " . $db . " (
			  pin_id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
              code VARCHAR(255) NOT NULL,
			  product_id BIGINT(20) unsigned NOT NULL,              
              status ENUM('reserved','redeem','activated','cancel','pending','approved','expired') NOT NULL,              
              created_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              invoice_id BIGINT(20) DEFAULT 0,
              expired_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY pin_id (pin_id),
              KEY code (code),
              KEY invoice_id (invoice_id),
              KEY status (status)
			) ENGINE=INNODB;";            
          
        dbDelta($sql);
        
        /**
         *  add foreign key for our primary table
         */
        $posts_table = $wpdb->posts;
        $sql = "ALTER TABLE $db 
                ADD FOREIGN KEY (product_id) REFERENCES $posts_table(ID)
                      ON DELETE CASCADE;";
                      
        $wpdb->query($sql);         
        
        /**
         *  Meta table for epin
         */
       $db = PINTYPE::DB(PINTYPE::DB_PRIMARY_META);
       
       if($wpdb->get_var("SHOW TABLES LIKE '".$db."'") != $db){       
    		$sql = "CREATE TABLE " . $db . " (
    			  id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,  
                  pin_id BIGINT(20) unsigned NOT NULL,  			  
                  meta_key VARCHAR(255) DEFAULT NULL,
                  meta_value LONGTEXT,
                  PRIMARY KEY (id),                  
                  KEY meta_key (meta_key)
    			) ENGINE=INNODB;";
                
            dbDelta($sql);                     
        }
        
        $sql = "ALTER TABLE $db 
                ADD FOREIGN KEY (pin_id) REFERENCES $primary_db(pin_id)
                      ON DELETE CASCADE;";
                      
        $wpdb->query($sql);
        
        /**
         *  Epin stockist table
         */
       $db = $pin_stockist_db = PINTYPE::DB(PINTYPE::DB_PIN_STOCKIST);;
       
       if($wpdb->get_var("SHOW TABLES LIKE '".$db."'") != $db){       
    		$sql = "CREATE TABLE " . $db . " (
    			  id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
                  pin_id BIGINT(20) unsigned NOT NULL,    			  			  
                  stockist_id BIGINT(20) unsigned NOT NULL,
    			  status ENUM('reserved','redeem','activated','cancel','pending','approved','expired') NOT NULL,
                  activated_uid BIGINT(20) DEFAULT 0,
                  activated_date BIGINT(20) DEFAULT 0,
                  activated_invoice_id BIGINT(20) DEFAULT 0,
                  redeem_by BIGINT(20) DEFAULT 0,
                  redeem_date BIGINT(20) DEFAULT 0,                  
                  modified_date BIGINT(20) DEFAULT 0,
                  modified_by BIGINT(20) DEFAULT 0,
    			  PRIMARY KEY id (id)
    			) ENGINE=INNODB;";                
              
            dbDelta($sql);
                       
        $sql   = array();
        
        $sql[] = "ALTER TABLE $db 
                ADD FOREIGN KEY (pin_id) REFERENCES $primary_db(pin_id)
                      ON DELETE CASCADE;";

        $sql[] = "ALTER TABLE $db 
                ADD FOREIGN KEY (status) REFERENCES $primary_db(status)
                      ON UPDATE CASCADE;";                      
                      
        $user_table = $wpdb->users;
                              
        $sql[] = "ALTER TABLE $db 
                ADD FOREIGN KEY (stockist_id) REFERENCES $user_table(ID)
                      ON DELETE CASCADE;";
                                            
        foreach($sql as $k => $query){                
            $wpdb->query($query); 
        }                                       
      } 
      
        /**
         *  Meta table for epin stockist meta
         */
       $db = PINTYPE::DB(PINTYPE::DB_PIN_STOCKIST_META);
       
       if($wpdb->get_var("SHOW TABLES LIKE '".$db."'") != $db){       
    		$sql = "CREATE TABLE " . $db . " (
    			  id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,  
                  pinstock_id BIGINT(20) unsigned NOT NULL,  			  
                  meta_key VARCHAR(255) DEFAULT NULL,
                  meta_value LONGTEXT,
                  PRIMARY KEY (id),                  
                  KEY meta_key (meta_key)
    			) ENGINE=INNODB;";
                
            dbDelta($sql);                     
        }
        
        $sql = "ALTER TABLE $db 
                ADD FOREIGN KEY (pinstock_id) REFERENCES $pin_stockist_db(id)
                      ON DELETE CASCADE;";
                      
        $wpdb->query($sql);       
                  
                
        add_option(PINTYPE::MK_DB_VERSION, PINTYPE::DB_VERSION);   
	}    
}
/** mc_epin_install_db() */