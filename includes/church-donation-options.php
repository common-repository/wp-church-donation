<?php
function wp_church_donation_options() { 
    global $wpdb;
    
    if (isset($_REQUEST['content'])) {
        if ($_REQUEST['content'] == 1) {
            
            $donation_form_heading = $wpdb->_real_escape($_REQUEST['donation_form_heading']);
            $donation_form_message = $wpdb->_real_escape($_REQUEST['donation_form_message']);
            $thank_you_page_content = $wpdb->_real_escape($_REQUEST['thank_you_page_content']);
            $thank_you_email_subject = $wpdb->_real_escape($_REQUEST['thank_you_email_subject']);
            $thank_you_email_content = $wpdb->_real_escape($_REQUEST['thank_you_email_content']);
            
            $query = "UPDATE `" . $wpdb->prefix . "church_donation_content` 
                SET `donation_form_heading` = '$donation_form_heading',
                `donation_form_message` = '$donation_form_message',
                `thank_you_page_content` = '$thank_you_page_content',
                `thank_you_email_subject` = '$thank_you_email_subject',
                `thank_you_email_content` = '$thank_you_email_content' 
                WHERE `" . $wpdb->prefix . "church_donation_content`.`id` =1;";
            
            $result = $wpdb->query($query);

            if($result == 1){
                $successCMessage = "Settings updated successfully.";
            }
        }
    }
    
    $content = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation_content where id=1");
    
    ?>

<div id="wp-donate-tabs">
  <h1>WP Church Donation | Content Management</h1>
  <h3>We can manage things like Thank you page content, Thank you email content sent after donation. Donation for heading and message at the top of the form.</h3>
  <?php if(isset($successCMessage)): ?>
  <h2><?php echo $successCMessage; unset($successCMessage); ?> </h2>
  <?php endif; ?>
  <div id="wp-donate-tab-settings">
    <form action="<?php echo site_url(); ?>/wp-admin/admin.php?page=wp_church_donation_options" method="post" name="contentForm" id="contentForm">
      <input type="hidden" name="content" value="1" />
      <table cellpadding="0" cellspacing="0" bgcolor="#ccc" width="99%" class="church_donation_options_headers">
        <tr>
          <td><table cellpadding="10" cellspacing="1" width="100%">
              <tr>
                <td width="30%" bgcolor="#FFFFFF"><label for="donation_form_heading"><strong>Donation page heading</strong></label></td>
                <td bgcolor="#FFFFFF"><input size="70" type="text" class="text_area" value="<?php echo $content[0]->donation_form_heading; ?>" id="donation_form_heading" name="donation_form_heading"></td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"><label for="donation_form_message"><strong>Donation form message<br />
                  Shown at top of the form at frontend</strong></label></td>
                <td bgcolor="#FFFFFF"><textarea id="donation_form_message" name="donation_form_message" class="text_area" rows="5" cols="70"><?php echo $content[0]->donation_form_message; ?></textarea></td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"><label for="thank_you_page_content"><strong>Thank you page content<br />
                  Shown at thank you page after donation made</strong></label></td>
                <td bgcolor="#FFFFFF"><textarea id="thank_you_page_content" name="thank_you_page_content" class="text_area" rows="5" cols="70"><?php echo $content[0]->thank_you_page_content; ?></textarea></td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"><label for="thank_you_email_subject"><strong>Thank you email subject<br />
                  Subject of the mail sent after donation process</strong></label></td>
                <td bgcolor="#FFFFFF"><textarea id="thank_you_email_subject" name="thank_you_email_subject" class="text_area" rows="5" cols="70"><?php echo $content[0]->thank_you_email_subject; ?></textarea></td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"><label for="thank_you_email_content"><strong>Thank you email content<br />
                  Content of the mail sent after donation process</strong></label></td>
                <td bgcolor="#FFFFFF"><textarea id="thank_you_email_content" name="thank_you_email_content" class="text_area" rows="5" cols="70"><?php echo $content[0]->thank_you_email_content; ?></textarea></td>
              </tr>
              <tr>
                <td colspan="2" align="center" bgcolor="#FFFFFF"><input type="submit" value="Submit" class="button button-primary button-large" /></td>
              </tr>
            </table></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php } ?>
