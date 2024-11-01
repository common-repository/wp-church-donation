<?php
function wp_church_donation_settings_authorize() { 
    global $wpdb;
    
    if (isset($_REQUEST['setting'])) {
        if ($_REQUEST['setting'] == 1) {
            $credentialsArray = array(
                'authnet_api_login' => $wpdb->_real_escape($_POST['authnet_api_login']),
                'authnet_api_key' => $wpdb->_real_escape($_POST['authnet_api_key']),
                'authnet_test_mode' => isset($_POST['authnet_test_mode']) && $_POST['authnet_test_mode'] == 1 ? 1 : 0,
            );
            $credentialsDataJson = json_encode($credentialsArray);
            $result = $wpdb->query("UPDATE `" . $wpdb->prefix . "church_donation_settings` SET `credentials_data` = '" . $credentialsDataJson . "' WHERE `id` =1");
            if($result == 1){
                $successMessage = "Settings updated successfully.";
            }
        }
    }
    
    ?>

<div id="wp-donate-tabs">
  <h1>WP Church Donation | Authorize.net payment gateway settings</h1>
  <?php if(isset($successMessage)): ?>
  <h2><?php echo $successMessage; unset($successMessage); ?> </h2>
  <?php endif; ?>
  <div id="wp-donate-tab-settings">
    <?php
                $authorizeSettings = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation_settings where payment_gateway_name='Authorize'");
                $credentialsJSON = $authorizeSettings[0]->credentials_data;
                if (strlen(trim($credentialsJSON)) > 0) {
                    $data = json_decode($credentialsJSON, true);
                } else {
                    $data = array('authnet_test_mode' => 0, 'authnet_api_login' => '', 'authnet_api_key' => '');
                }
            ?>
    <form action="<?php echo site_url(); ?>/wp-admin/admin.php?page=wp_church_donation_settings_authorize" method="post" name="setting" id="setting">
      <input type="hidden" name="setting" value="1" />
      <table cellpadding="0" cellspacing="0" bgcolor="#ccc" width="99%" class="church_donation_options_headers">
        <tr>
          <td><table cellpadding="10" cellspacing="1" width="100%" >
              <tr>
                <td width="20%" bgcolor="#FFFFFF"><label for="authnet_test_mode"><strong>Test mode</strong></label></td>
                <td bgcolor="#FFFFFF"><input type="checkbox" name="authnet_test_mode" id="authnet_test_mode" value="1" <?php echo $data['authnet_test_mode'] == 1 ? "checked" : ""; ?>>
                  <span>Check to enable test mode and uncheck to enable live mode</span> </td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"><label for="authnet_api_login"><strong>API Login</strong></label></td>
                <td bgcolor="#FFFFFF"><input type="text" class="text_area" value="<?php echo $data['authnet_api_login']; ?>" id="authnet_api_login" name="authnet_api_login"></td>
              </tr>
              <tr>
                <td bgcolor="#FFFFFF"><label for="authnet_api_key"><strong>Transaction Key</strong></label></td>
                <td bgcolor="#FFFFFF"><input type="text" class="text_area" value="<?php echo $data['authnet_api_key']; ?>" id="authnet_api_key" name="authnet_api_key"></td>
              </tr>
              <tr>
                <td colspan="2" align="left" bgcolor="#FFFFFF"><input type="submit" value="Submit"  class="button button-primary button-large" /></td>
              </tr>
            </table></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php } ?>
