<?php

function mb_stockist_authpin_stockist_info($request, $options){
    global $_registered_pages;
    list($invoice, $orders)    = $options;
    
    $iid        = $invoice->invoice_id;
    $uid        = $invoice->ordered_by;  
      
    $points_due = (float) $invoice->total_amount;
    $points     = (float) uinfo($uid,MKEY::RM);
    
    $balance    = $points - $points_due;
        
?>
<div id="authpin_stockist_ewallet" class="postbox " >
    <div class="handlediv" title="Click to toggle"><br /></div>
    <h3 class='hndle'><span>E-Wallet Info</span></h3>
    <div class="inside">
        <table class="form-table widefat">
            <tbody>
                <tr>
                    <td><strong>E-wallet:</strong></td>
                    <td><?php echo currency_format('%#10n', $points);?></td>
                </tr>
                <tr>
                    <td><strong>After transaction:</strong></td>
                    <td><?php echo currency_format('%#10n',$balance);?></td>
                </tr>                
            </tbody>
        </table>
    </div>
</div>
    
<?php    
}  

function mb_stockist_authpin_stockist_action($placeholder, $options){
    global $_registered_pages;
    list($invoice, $orders)    = get_invoice_metadata($_REQUEST['invoice_id']);
    
    $iid        = $invoice->invoice_id;
    $uid        = $invoice->ordered_by;    
?>
<div id="authpin_stockist_action" class="postbox " >
    <div class="handlediv" title="Click to toggle"><br /></div>
    <h3 class='hndle'><span>Actions</span></h3>
    <div class="inside">
    <table class="form-table widefat">
        <tbody>
            <tr>
                <td>
                    <button type="submit" class="button-primary">Authorize</button>
                    <a href="#" class="button-secondary go-print">Print</a>
                    <button class="button-secondary go-back">Cancel</button>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
</div>    
<script>
jQuery(document).ready(function($){
   $('.go-print').click(function(e){
    e.preventDefault();
    $('#poststuff').jqprint();
   }) 
});
</script>
<?php    
}

function mb_stockist_authpin_items($placeholder, $options){
    
    list($invoice, $orders)    = $options['args'];
    
    $iid        = $invoice->invoice_id;
    $uid        = $invoice->ordered_by;
    
    
?>
       <table class="form-table widefat">
        <tbody>
            <tr>
                <th style="width:5%">#</th>
                <th>Item</th>
                <th>Description</th>
                <th>Retail Price</th>
                <th>Unit Discount</th> 
                <th>Price</th>
                <th>Quantity</th>                               
                <th>Amount</th>
            </tr>        
            <?php $count = 1; ?>
            <?php $total = 0;?>
            <?php $retail_total = $discount_total = $price_total = $rtotal = 0;?>
            <?php foreach($orders['product_id'] as $index => $total_amount): ?>
            <?php
                $unit       = (int) $orders['quantity'][$index];
                
                $retail     = $orders['retail_price'][$index];
                $retail_total += $retail; 
                $price      = $orders['price'][$index];
                
                $price_total += $price;
                $discount   = ($retail - $price) ;
                $discount_total += $discount * $unit;
                $total_amount = (float) ($price * $unit);
                $total_ramount = (float) ($retail * $unit);
            ?>
            <tr>
                <td><?php echo $count;?></td>
                <td><?php echo $orders['sku'][$index];?></td>
                <td><?php t('strong',$orders['quantity'][$index]);?> unit of <?php t('strong',$orders['sku'][$index]);?>.</td>
                <td><?php echo currency_format('%#10n', $retail);?></td>
                <td><?php echo currency_format('%#10n',$discount);?></td>
                
                <td><?php echo currency_format('%#10n', $price);?></td>
                <td><?php echo $orders['quantity'][$index];?></td>                
                <td><?php echo currency_format('%#10n',$total_ramount);?></td>
            </tr>
            <?php $count++; ?>
            <?php $total += $total_amount;?>
            <?php $rtotal += $total_ramount;?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8"></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td><strong>Subtotal</strong></td>
                <td><?php echo currency_format('%#10n',$rtotal) ;?></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td><strong>Discount</strong></td>
                <td><?php echo currency_format('%#10n',$discount_total) ;?></td>
            </tr> 
            <tr>
                <th colspan="6"></th>
                <th><strong>Total</strong></th>
                <th><?php echo currency_format('%#10n', $total) ;?></th>
            </tr>                      
        </tfoot>
    </table>    
<?php        
}

/**
 * show currenc invoice id by stockist to be
 * authorized by admin, refer by stockist request auth pin 
 */
function mb_stockist_authpin($placeholder, $options){
    
    list($invoice, $orders)    = $options['args'];
    
    $iid        = $invoice->invoice_id;
    $uid        = $invoice->ordered_by;
?>
    <table class="form-table widefat">
        <tbody>
        <tr valign="top">
            <td><strong>Name:</strong></td>
            <td><?php  t('strong',uinfo($uid,'name'));?> <?php t('span','('.uinfo($uid,'stockist_code').')');?></td>            
            <td colspan="2"></td>   
            <td><strong>Invoice:</strong></td>
            <td><?php echo mc_get_invoice_id($iid, $_REQUEST['stockist_id']);?></td>
        </tr>
        <tr>
            <td><strong>Stockist:</strong></td>
            <td><?php  echo ucwords(uinfo($uid,'stockist_type'));?>, <?php echo ucwords(uinfo($uid,'state'));?> </td>   
            <td colspan="2"></td>
            <td><strong>Date:</strong></td>
            <td><?php echo date('d F, Y',strtotime($invoice->created_date));?></td>            
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><?php echo ucwords($invoice->order_status);?> </td>          
            <td colspan="2"></td>
            <td><strong>Amount Due</strong></td>
            <td><?php echo currency_format('%#10n',array_sum($orders['product_id']) );?></td>
        </tr>
        </tbody>
    </table>
<?php     
    //var_dump($orders);
}

function mb_transfer_pin(){
    global $stockist;
    
    $hq_id = get_stockist_hq();
    
    $invoices  = mc_get_invoices_for_id($hq_id, 'pending');
    $stockists = $stockist->get_all_stockist();
    //var_dump($invoices);    
?>

    <h3>Recent Request by Stockist</h3>
    <table class="wp-list-table widefat posts">
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Request Date</th>
                <th>Stockist Name</th>                
                <th>Total Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($invoices as $index => $invoice): ?>
            
            <?php  
                $sid = $invoice->ordered_by;
                
                $items = get_invoice_meta($invoice->invoice_id, 'orders', false);
                
                $code = mc_get_userinfo($sid,'code');
                $name = mc_get_userinfo($sid,'name');
                $link = _t('a', $name,array('href'=>'admin.php?page=wp_crm_add_new&user_id='.$sid));
             
                $date = date("D, d/m/y", strtotime($invoice->created_date));
                $ndate = apply_filters('relative_time', strtotime($invoice->created_date));
                
                $type = mc_get_userinfo($sid,'stockist_type');
                
                $invoice_code = mc_get_invoice_id($invoice->invoice_id, $hq_id);
                //add_query_arg
                $params = array('page'          => PINTYPE::PAGE_EPIN_TRANSFER,
                                'action'        => PINTYPE::ACT_AUTHORIZE_STOCKIST, 
                                'invoice_id'    => $invoice->invoice_id,
                                'stockist_id'   => $hq_id);
                
                $auth_uri = add_query_arg($params);
                
                $invoice_uri = 'edit.php?post_type=products&page=mc_sales&action=edit&invoice_id='.$invoice->invoice_id.'&stockist_id='.$hq_id;
                                 
            ?>
            <tr>
                <td><?php t('a',$invoice_code,array('href'=> $invoice_uri)); ?></td>
                <td>
                    <?php t('span',$date); ?>
                    <?php t('small',$ndate,array('class'=>'db muted')); ?>
                </td>
                <td>
                    <?php t('span',$link.' ('.$code.')'); ?>
                    <?php t('small','Stockist '.ucwords($type),array('class'=>'db')); ?>                
                </td>
                
                <td><?php t('strong', 'RM '.$invoice->total_amount); ?></td>
                <td>
                    <a class="button-secondary view" data-target="#invoice-item-<?php echo $invoice->invoice_id;?>">View Items</a>
                    <a class="button-secondary" href="<?php echo $auth_uri;?>">Authorized PIN</button>
                </td>
            </tr>
            <tr class="fade" id="invoice-item-<?php echo $invoice->invoice_id;?>">              
                <td></td>  
                <td colspan="4">
                <table class="widefat">
                    <thead>
                        <tr>
                            <td>
                            <strong>Item(s) Details</strong>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                 <?php $item = $items[0]; ?>   
                <?php foreach($item['product_id'] as $pid => $amount): ?>                      
                        <tr>
                            <td>
                                <?php t('strong',$item['quantity'][$pid]); ?> unit of 
                                <?php t('a', _t('span',get_the_title($pid)), array('href'=> get_permalink($pid)) );?>
                                for RM <?php t('strong',$item['price'][$pid]); ?> each.
                            </td>
                        </tr>    
                <?php endforeach; // endforeach.items ?>
                    </tbody>
                </table>
                </td>
                
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">
                <?php //var_dump($stockists);?>
                </th>
            </tr>
        </tfoot>
    </table>
    <script>
        jQuery(document).ready(function($){
            $('.fade').fadeOut();
            $('a.view').click(function(){
                var itemid = $(this).data('target');
                $(itemid).slideToggle();
               //console.log($(this).data('target')); 
            }); 
        });
    </script>
<?php    
}    
function mb_add_pin(){
       query_posts(array( 
        'post_type' => 'products',
        'showposts' => 10 
    ) ); 
    $counter = 1;
?>
    <table class="wp-list-table widefat posts">
        <thead>
            <tr>
                <th>#</th>
                <th>Products</th>
                <th>Product in Stock</th>
                <th>Available Pin</th>
                <th title="Max 2147483647">Quantity</th>
            </tr>
        </thead>
        <tbody>
        <?php while (have_posts()) : the_post(); ?>            
            <?php $pid = get_the_ID(); ?> 
            <?php         
            $metadata = get_post_custom($pid); 
            $stock    = $metadata[mc_products::MK_STOCK][0];   
            ?>
            <tr>            
                <td><?php t('small',$counter);?></td>
                <td>
                    <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                    <input type="hidden" name="product_id[<?php echo $pid;?>]" value="<?php echo $pid;?>"/>
                </td>
                <td><?php echo $stock;?></td>
                <td><?php echo get_pin($pid,1);?></td>
                <td><input type="text" name="quantity[<?php echo $pid;?>]" value=""></td>
            
            </tr>
        <?php $counter++; ?>
        <?php endwhile;?>
        </tbody>
        <tfoot>
            <tr>
                <th  colspan="5" class=""><button class="button-secondary">Generate E-Pin</button></th>
            </tr>
        </tfoot>
    </table>
<?php    
}

function mb_show_format()
{   global $Epin;
    
    
    $generator = get_option('epin_generator_type');
    $generator = (!empty($generator)) ? $generator : 1;
    $append_stockist = get_option('epin_append_stockist_id');
    
    $prefix = $suffix = $pin = false;
    
    $code = array();
    
    if (!empty($append_stockist)) {
        $prefix = '#00'. rand(1,99);
        array_push($code, $prefix ); 
    }
    
    $pin    = $Epin->get_pin($generator);
    array_push($code, $pin ); 
    //$prefix = (!empty($append_stockist)) ? '#00'. rand(1,99) : '';
    
   
    //$suffix = false;  
    $code  = join('-',$code);
    
?>
    <h3>Example Formatting</h3>
    <table class="widefat">
        <thead>
            <tr>
                <th>Prefix</th>
                <th>Code</th>
                <th>Suffix</th>
                <th>Full Pin</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php t('strong', $prefix); ?></td>
                <td><?php t('strong', $pin); ?></td>
                <td><?php t('strong', $suffix); ?></td>
                <td><?php t('strong', $code); ?></td>
            </tr>
        </tbody>
    </table>
    <br />
<?php        
}
    	
function mb_select_pin_generator(){
?>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th colspan="2">
                        Code
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <label for="epin_generator_type">Pin Engine</label>
                    </td>
                    <td>
                        <select id="epin_generator_type" name="epin_generator_type">
                            <option value="4">Pair 4x3 - ####-####-####</option>
                            <option value="3">Pair 2x4 - ##-##-##-##</option>
                            <option value="1">Rand 11  - ###########</option>
                            <option value="2">Rand 4   - ####</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">
                        <button type="submit" class="btn button-secondary">Update</button>
                    </th>
                </tr>
            </tfoot>
        </table>
    <?php $epin_generator = get_option('epin_generator_type'); ?>
    <script>var epin_generator = <?php echo $epin_generator; ?>;</script>
    <?php t('script', "jQuery(document).ready(function($){ $('#epin_generator_type').val(epin_generator);});");
    ?>
<?php    
}

function mb_general_options()
{
?>        
        <input type="hidden" name="section" value="pin_format">
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th colspan="2">
                        Prefix
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <label for="epin_append_stockist_id">Stockist ID</label>
                    </td>
                    <td>
                        <input type="checkbox" id="epin_append_stockist_id" name="epin_append_stockist_id"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">
                        <button type="submit" class="btn button-secondary">Update</button>
                    </th>
                </tr>
            </tfoot>
        </table>
        <?php //var_dump(get_option('epin_append_stockist_id'));?>
        <?php if (get_option('epin_append_stockist_id') != false): ?>
        <script>
        jQuery(document).ready(function($){
            $('#epin_append_stockist_id').prop('checked', true);
            $('#epin_append_stockist_id').val('on');
        });        
        </script>
        <?php endif; ?>
    </form>
<?php    
}