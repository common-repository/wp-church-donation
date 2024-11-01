<?php
ob_start();
function wp_church_donation_listings_page() {
    
    
    global $wpdb;
    // delete donation entry
    if (isset($_GET['id']) && $_GET['id'] != '' && isset($_GET['action']) && $_GET['action'] == 'delete') {
        $result = $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "church_donation WHERE id = %d", $_GET['id']));
        header("Location: " . site_url() . '/wp-admin/admin.php?page=wp_church_donation');
        exit;
    
    // code to export the list
    } elseif (isset($_GET['act']) && $_GET['act'] == 'export') {
        wp_church_donation_export();
        exit;
    // code to list donation entries
    } elseif (isset($_GET['id']) && $_GET['id'] != '') { ?>

<div>
  <h1>Donation entry details</h1>
  <div>
   <table cellpadding="0" cellspacing="0" bgcolor="#ccc" width="99%">
      <tr>
        <td><table cellpadding="10" cellspacing="1" width="100%">
        <thead>
        
          <?php  global $wpdb; ?>
          <?php $donationDetails = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation where id='" . $_GET['id'] . "'"); ?>
          <?php foreach ($donationDetails as $details): ?>
        <tr>
          <td width="10%" bgcolor="#FFFFFF"><b>First name:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->first_name; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Last name:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->last_name; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Email:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->email; ?></td>
        </tr>
         <tr>
          <td bgcolor="#FFFFFF"><b>Phone:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->phone; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Organization:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->organization; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Category:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->donate_cateogry; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Amount:</b></td>
          <td bgcolor="#FFFFFF">$<?php echo $details->amount; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Address:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->address; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Zip:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->zip; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>City:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->city; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>State:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->state; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Country:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->country; ?></td>
        </tr>
       
      
        <tr>
          <td bgcolor="#FFFFFF"><b>Donation date:</b></td>
          <td align="left" bgcolor="#FFFFFF">
              <?php 
                    $dt = $details->date;  
                    $new_date = date('d M, Y (H:i A)',  strtotime($dt));
                    echo $new_date;
                    ?>
          </td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Payment Method:</b></td>
          <td align="left" bgcolor="#FFFFFF"><?php echo $details->payment_method; ?></td>
        </tr>
        <tr>
          <td bgcolor="#FFFFFF"><b>Payment Status:</b></td>
          <td align="left" bgcolor="#FFFFFF"><?php echo $details->payment_status; ?></td>
        </tr>
          <tr>
          <td bgcolor="#FFFFFF"><b>Comment:</b></td>
          <td bgcolor="#FFFFFF"><?php echo $details->comment; ?></td>
        </tr>
        <?php endforeach; ?>
       
        <tr>
          <td align="right" bgcolor="#FFFFFF" colspan="2"><input type="button"  class="button button-primary button-large" onclick=location.href='<?php echo site_url(); ?>/wp-admin/admin.php?page=wp_church_donation' value="Back" />
          </td>
          
        </tr>
      
        
      </thead>
    </table>
    </td>
    </tr>
    </table>
  </div>
</div>
<?php } else {   ?>
<div>
  <h1>WP Church Donation | Donation List</h1>
  <div> <span> <a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wp_church_donation&act=export" title="Export donors list" class="button button-primary button-large">Export Donation List</a> </span> <br />
    <br />
    <table cellpadding="0" cellspacing="0" bgcolor="#ccc" width="99%">
      <tr>
        <td>
			<table cellpadding="10" cellspacing="1" width="100%">
          <?php
                    global $wpdb;
                    
                    $total = $wpdb->get_var("SELECT COUNT(id)  FROM " . $wpdb->prefix . "church_donation");
                    
                    $records_per_page = 20;
                    $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
                    $offset = ( $page * $records_per_page ) - $records_per_page;
                    
                    $donationEntries = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation order by date desc limit ".$offset. " , ".$records_per_page);
                    
                    if (count($donationEntries) > 0) {
                        ?>
          <thead>
            <tr>
              <th width="15%" align="left" bgcolor="#FFFFFF">Name</th>
              <th width="15%" align="left" bgcolor="#FFFFFF">Email</th>
              <th width="16%" align="left" bgcolor="#FFFFFF">Phone</th>
              <th width="12%" align="left" bgcolor="#FFFFFF">Donation</th>
              <th width="12%" align="left" bgcolor="#FFFFFF">Date</th>
              <th width="12%" align="left" bgcolor="#FFFFFF">Payment Status</th>
              <th width="18%" align="left" bgcolor="#FFFFFF">Action</th>
            </tr>
            <?php
                            foreach ($donationEntries as $row) {
                                ?>
            <tr>
              <td bgcolor="#FFFFFF"><?php echo $row->first_name . ' ' . $row->last_name; ?></td>
              <td bgcolor="#FFFFFF"><?php echo $row->email; ?></td>
              <td bgcolor="#FFFFFF"><?php echo $row->phone; ?></td>
              <td bgcolor="#FFFFFF">$<?php echo $row->amount; ?></td>
              <td bgcolor="#FFFFFF">
                  <?php 
                    $dt = $row->date;  
                    $new_date = date('d M, Y (H:i A)',  strtotime($dt));
                    echo $new_date;
                    ?>
                  
              </td>
              <td bgcolor="#FFFFFF"><?php echo $row->payment_status; ?></td>
              <td bgcolor="#FFFFFF"><a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wp_church_donation&id=<?php echo $row->id; ?>" title="View donation details"  class="button button-primary button-large"> View Details </a> <a onClick="return confirm('Are you sure, want to delete this entry?')" href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wp_church_donation&action=delete&id=<?php echo $row->id; ?>" title="Delete entry" class="button button-primary button-large"> Delete </a> </td>
            </tr>
            <?php
                            }
                            ?>
          </thead>
			<tr><td><?php
                    } else {
                        echo "No Record's Found.";
                    }
                    ?>
                    </td></tr>
        </table></td>
      </tr>
    </table>
    
<?php
$pagination = paginate_links( array(
    'base' => add_query_arg( 'cpage', '%#%' ),
    'format' => '',
    'prev_text' => __('Previous'),
    'next_text' => __('Next'),
    'total' => ceil($total / $records_per_page),
    'current' => $page
));
?>
    
    <div class="donation-pagination">
        <?php echo $pagination; ?>
    </div>
  </div>
</div>
<?php
    }
} ?>
