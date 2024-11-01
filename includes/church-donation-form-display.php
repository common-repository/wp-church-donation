<?php 
/**
 * Display church donation form at client side
 * @return string Donation Form
 * @since 1.0
 *
 */
function wp_church_donation_form() {
       
    // get necessary vars
    ob_start();
    global $wpdb;
    
   // print_r($_SESSION);
    // get content from table to send email notification after payment process and to show form heading and message
    $content = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation_content where id=1");
    
    if(isset($_REQUEST['success']) && $_REQUEST['success'] == 1){
        if (isset($_SESSION['request_message'])){
            unset($_SESSION['request_message']);
        }
        echo "<div class='thank_you_message'><p>".$content[0]->thank_you_page_content."</p></div>";
    } else {
    
        // define vars
        $firstName = "";
        $lastName = "";
        $organization = "";
        $address = "";
        $city = "";
        $country = "";
        $state = "";
        $zip = "";
        $phone = "";
        $email = "";
        $amount = "";
        $cardType = "";
        $cardNumber = "";
        $expMonth = "";
        $expYear = "";
        $cardCode = "";
        $comment = "";
        $payment_method = "Authorize";
        $donate_cateogry = "";

        // check for the request
        if (isset($_REQUEST['action'])) {

            // check for form submit
            if ($_REQUEST['action'] == 'submit') {

                // get values of form
                $firstName = $wpdb->_real_escape($_POST['first_name']);
                $lastName = $wpdb->_real_escape($_POST['last_name']);
                $organization = $wpdb->_real_escape($_POST['organization']);
                $address = $wpdb->_real_escape($_POST['address']);
                $city = $wpdb->_real_escape($_POST['city']);
                $country = $wpdb->_real_escape($_POST['country']);
                $state = $wpdb->_real_escape($_POST['state']);
                $zip = $wpdb->_real_escape($_POST['zip']);
                $phone = $wpdb->_real_escape($_POST['phone']);
                $donate_cateogry = $wpdb->_real_escape($_POST['donate_cateogry']);
                $email = $wpdb->_real_escape($_POST['email']);
                $amount = $wpdb->_real_escape($_POST['amount']);
                $cardType = $wpdb->_real_escape($_POST['card_type']);
                $cardNumber = $wpdb->_real_escape($_POST['x_card_num']);
                $expMonth = $wpdb->_real_escape($_POST['exp_month']);
                $expYear = $wpdb->_real_escape($_POST['exp_year']);
                $cardCode = $wpdb->_real_escape($_POST['x_card_code']);
                $comment = $wpdb->_real_escape($_POST['comment']);
                $payment_method = $wpdb->_real_escape($_POST['payment_method']);

                // TODO: Add some donation type in the form and let donor select for what he/she is donating
                $donation_type = "Donation";

                // server side validation for all fields
                $error = false;
                if (strlen(trim($firstName)) <= 0) {
                    echo $errorFirstName = "Please enter first name";
                    $error = true;
                }
                if (strlen(trim($lastName)) <= 0) {
                    $errorLastName = "Please enter last name";
                    $error = true;
                }
                if (strlen(trim($address)) <= 0) {
                    $errorAddress = "Please enter address";
                    $error = true;
                }
                if (strlen(trim($city)) <= 0) {
                    $errorCity = "Please enter city";
                    $error = true;
                }
                if (strlen(trim($state)) <= 0) {
                    $errorState = "Please enter state";
                    $error = true;
                }
                if (strlen(trim($country)) <= 0) {
                    $errorCountry = "Please select country";
                    $error = true;
                }
                if (strlen(trim($zip)) <= 0) {
                    $errorZip = "Please enter zip code";
                    $error = true;
                }
                if (strlen(trim($phone)) <= 0) {
                    $errorPhone = "Please enter phone number";
                    $error = true;
                }
                if (strlen(trim($donate_cateogry)) <= 0) {
                    $errorDonate_cateogry = "Please select donation category";
                    $error = true;
                }
                if (strlen(trim($email)) <= 0) {
                    $errorEmail = "Please enter email address";
                    $error = true;
                } else if(!filter_var($email, FILTER_VALIDATE_EMAIL )){
                    $errorEmail = "Please enter valid email address";
                    $error = true;
                }
                if (strlen(trim($amount)) <= 0) {
                    $errorAmount = "Please enter donation amount";
                    $error = true;
                }
                if (strlen(trim($cardNumber)) <= 0) {
                    $errorCardNumber = "Please enter card number";
                    $error = true;
                }
                if (strlen(trim($cardCode)) <= 0) {
                    $errorCardCode = "Please enter CVV code";
                    $error = true;
                }

                // if no error in form then proceed
                if (!$error) {

                    // check for payment method
                    if($payment_method == "Authorize"){

                        // get auth.net settings
                        $authorizeSettings = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation_settings");
                        $credentialsJSON = $authorizeSettings[0]->credentials_data;

                        // check for Authorize.net credentials added or not
                        if (strlen(trim($credentialsJSON)) > 0) {

                            // define settings and constants for Auth.net payment request
                            $data = json_decode($credentialsJSON,true);                            
                            define("AUTHORIZENET_API_LOGIN_ID", $data['authnet_api_login']);
                            define("AUTHORIZENET_TRANSACTION_KEY", $data['authnet_api_key']);
                            if ($data['authnet_test_mode'] == 1) {
                                define("AUTHORIZENET_SANDBOX", true);
                            } else {
                                define("AUTHORIZENET_SANDBOX", false);
                            }
                            $methodToUse = $authorizeSettings[0]->payment_method;

                            // You only need to adjust the two variables below if testing DPM
                            define("AUTHORIZENET_MD5_SETTING", "");

                            // make a call to payment process
                            $REQUEST = $_REQUEST;
                            $paymentStatus = "Pending";
                            $payment_response = processAuthorizePayment($methodToUse, $REQUEST);

                            // on successful payment process
                            if ($payment_response) {
                                $paymentStatus = "Complete";                                
                                $wpdb->insert( 
									$wpdb->prefix . "church_donation", 
									array( 
										'first_name' => $firstName, 
										'last_name' => $lastName, 
										'organization' => $organization,
										'address' => $address,
										'city' => $city,
										'country' => $country,
										'state' => $state,
										'zip' => $zip,
										'phone' => $phone,
										'email' => $email,
										'donation_type' => $donation_type,
										'amount' => $amount,
										'donate_cateogry' => $donate_cateogry,
										'comment' => strip_tags($comment),
										'payment_status' => $paymentStatus,
										'payment_method' => 'Authorize',
										'date' => date('Y-m-d H:i:s')
									), 
									array( 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s' 
									) 
								);

                                // send email notification to user and admin
                                sendNotificationToUser($email, $content[0]->thank_you_email_subject, $REQUEST, $content[0]->thank_you_email_content, $paymentStatus);
                                sendNotificationToAdmin(get_option( 'admin_email' ), $content[0]->thank_you_email_subject, $REQUEST, $content[0]->thank_you_email_content, $paymentStatus);
                                                                                               
                                // redirect according to parmalink settings from admin area
                                if(strstr(get_permalink(get_the_ID()), '?')){
                                    header("Location:".get_permalink(get_the_ID())."&success=1");
                                } else {
                                    header("Location:".get_permalink(get_the_ID())."?success=1");
                                }
                                exit;

                            // payment failure
                            } else {                               
                                $wpdb->insert( 
									$wpdb->prefix . "church_donation", 
									array( 
										'first_name' => $firstName, 
										'last_name' => $lastName, 
										'organization' => $organization,
										'address' => $address,
										'city' => $city,
										'country' => $country,
										'state' => $state,
										'zip' => $zip,
										'phone' => $phone,
										'email' => $email,
										'donation_type' => $donation_type,
										'amount' => $amount,
										'donate_cateogry' => $donate_cateogry,
										'comment' => $comment,
										'payment_status' => $paymentStatus,
										'payment_method' => 'Authorize',
										'date' => date('Y-m-d H:i:s')
									), 
									array( 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s', 
										'%s' 
									) 
								);
                            }
                            // TODO: Add success and failure message of the plugin instead showing response received from Auth.net

                        } else {
                            $errorMessage = "Sorry we can not process your payment at this time, there are some internal problems with the system and we will get back to you sooner.";
                            // TODO: Send notification to inform admin that Auth.net settings are not added to process payment
                        }

                    }
                }
            }
        }

        ?>
    <div class="wp-church-form-style">
        <form method="post" name="church_donation_form" id="church_donation_form" action="<?php echo get_permalink(get_the_ID()); ?>" autocomplete="on">
            <input type="hidden" name="action" value="submit" />

            <?php if (isset($errorMessage)): ?>
                <div class="error">
                    <p><?php echo $errorMessage;  unset($errorMessage); ?></p>
                    <br />
                </div>
            <?php endif; ?>

            <div class="donation-heading">
                <h2><?php echo $content[0]->donation_form_heading; ?></h2>
            </div>

            <div class="donation-header">
               <p><?php echo $content[0]->donation_form_message; ?></p>
            </div>

            <?php if (get_option('failed_response')): ?>
                <div class="error">
                    <p><?php echo get_option('failed_response');
                    delete_option('failed_response');?>
                    <br /></p>
                </div>
            <?php endif; ?>

            <ul class="church-donation-form">
                <li class="donation-parts">
                   <h3> Donor information</h3>
                </li>
                <li>
                    <label for="first_name">First name<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="first_name" id="first_name" value="<?php echo $firstName; ?>" />
                    <?php if(isset($errorFirstName)): ?>
                        <label class="error"><?php echo $errorFirstName; ?></label>
                    <?php endif; ?>
                </li>
                <li>
                    <label for="last_name">Last name<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="last_name" id="last_name" value="<?php echo $lastName; ?>"  />
                    <?php if(isset($errorLastName)): ?>
                        <label class="error"><?php echo $errorLastName; ?></label>
                    <?php endif; ?>
                </li>
               																				
                <li>			
                    <label for="email">Email<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="email" id="email" value="<?php echo $email; ?>" />
                    <?php if(isset($errorEmail)): ?>
                        <label class="error"><?php echo $errorEmail; ?></label>
                    <?php endif; ?>
                </li>
                 <li>			
                    <label for="phone">Phone<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="phone" id="phone" value="<?php echo $phone; ?>" />
                    <?php if(isset($errorPhone)): ?>
                        <label class="error"><?php echo $errorPhone; ?></label>
                    <?php endif; ?>
                </li>	
                <li>			
                    <label for="organization">Organization</label>
                    <input type="text" class="inputbox" name="organization" id="organization" value="<?php echo $organization; ?>" />
                </li>
                <li>			
                    <label for="address">Address<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="address" value="<?php echo $address; ?>" id="address"  />
                    <?php if(isset($errorAddress)): ?>
                        <label class="error"><?php echo $errorAddress; ?></label>
                    <?php endif; ?>
                </li>	
                 <li>			
                    <label for="zip">Zip<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="zip" value="<?php echo $zip; ?>" id="zip"  />
                    <?php if(isset($errorZip)): ?>
                        <label class="error"><?php echo $errorZip; ?></label>
                    <?php endif; ?>
                </li>	
                <li>			
                    <label for="city">City<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="city" value="<?php echo $city; ?>" id="city"  />
                    <?php if(isset($errorCity)): ?>
                        <label class="error"><?php echo $errorCity; ?></label>
                    <?php endif; ?>
                </li>	
               
               
                <li>			
                    <label for="state">State<span class="required">*</span></label>
                    <input type="text" class="inputbox" name="state" value="<?php echo $state; ?>" id="state" />
                    <?php if(isset($errorState)): ?>
                        <label class="error"><?php echo $errorState; ?></label>
                    <?php endif; ?>
                </li>
                 <li>			
                    <label for="country">Country<span class="required">*</span></label>
                    <select id="country" name="country" >
                        <option value="">Select country</option>
                        <?php $countries = getCountryArray(); ?>
                        <?php foreach ($countries as $k => $con): ?>
                            <option value="<?php echo $con; ?>" <?php if ($country == $con) { ?> selected="selected"<?php } ?>>
                                <?php echo $con; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($errorCountry)): ?>
                        <label class="error"><?php echo $errorCountry; ?></label>
                    <?php endif; ?>
                </li>	
                

                <li class="donation-parts">
                    <h3>Donation Information</h3>
                </li>
                <li>
                    <label for="donate_cateogry">Category<span class="required">*</span></label>
                    <select name="donate_cateogry" id="donate_cateogry" class="inputbox donate_cateogry" >
                        <option value="" >Please select donation category</option>
                        <option value="Tithes" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Tithes' ) ? 'selected="selected"' : '' ; ?> >Tithes</option>
                        <option value="Children" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Children' )? 'selected="selected"' : '' ; ?> >Children</option>
                        <option value="Missions" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Missions' )? 'selected="selected"' : '' ; ?>>Missions</option>
                        <option value="Outreach" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Outreach' )? 'selected="selected"' : '' ; ?>>Outreach</option>
                        <option value="Building Fund" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Building Fund' )? 'selected="selected"' : '' ; ?>>Building Fund</option>
                        <option value="Offering" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Offering' )? 'selected="selected"' : '' ; ?>>Offering</option>
                        <option value="Pastor's Love Offering" <?php echo (isset($donate_cateogry) && $donate_cateogry == "Pastor's Love Offering" )? 'selected="selected"' : '' ; ?>>Pastor's Love Offering</option>
                        <option value="Special Offering" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Special Offering' )? 'selected="selected"' : '' ; ?>>Special Offering</option>
                        <option value="Other" <?php echo (isset($donate_cateogry) && $donate_cateogry == 'Other' )? 'selected="selected"' : '' ; ?>>Other</option>
                    </select>  
                     <?php if(isset($errorDonate_cateogry)): ?>
                        <label class="error"><?php echo $errorDonate_cateogry; ?></label>
                    <?php endif; ?>
                </li>
                <li>
                    <label for="amount">Amount<span class="required">*</span></label>				
                    <input type="text" class="inputbox" name="amount" id="amount" placeholder="$" value="<?php echo $amount; ?>" />
                    <?php if(isset($errorAmount)): ?>
                        <label class="error"><?php echo $errorAmount; ?></label>
                    <?php endif; ?>
                </li>		
             	<li>
                    <label for="card_type">Card type<span class="required">*</span></label>
                    <select id="card_type" name="card_type" class="inputbox" >
                        <option value="Visa">Visa</option>
                        <option value="MasterCard">MasterCard</option>
                        <option value="Discover">Discover</option>
                        <option value="Amex">American Express</option>
                    </select>
                </li>		
                <li>
                    <label for="exp_month">Expiration Date<span class="required">*</span></label>
                    <select name="exp_month" id="exp_month" class="inputbox exp_month" >
                        <option value="01" <?php if (date('m') == '01') { ?>  selected="selected"<?php } ?>>01</option>
                        <option value="02" <?php if (date('m') == '02') { ?>  selected="selected"<?php } ?>>02</option>
                        <option value="03" <?php if (date('m') == '03') { ?>  selected="selected"<?php } ?>>03</option>
                        <option value="04" <?php if (date('m') == '04') { ?>  selected="selected"<?php } ?>>04</option>
                        <option value="05" <?php if (date('m') == '05') { ?>  selected="selected"<?php } ?>>05</option>
                        <option value="06" <?php if (date('m') == '06') { ?>  selected="selected"<?php } ?>>06</option>
                        <option value="07" <?php if (date('m') == '07') { ?>  selected="selected"<?php } ?>>07</option>
                        <option value="08" <?php if (date('m') == '08') { ?>  selected="selected"<?php } ?>>08</option>
                        <option value="09" <?php if (date('m') == '09') { ?>  selected="selected"<?php } ?>>09</option>
                        <option value="10" <?php if (date('m') == '10') { ?>  selected="selected"<?php } ?>>10</option>
                        <option value="11" <?php if (date('m') == '11') { ?>  selected="selected"<?php } ?>>11</option>
                        <option value="12" <?php if (date('m') == '12') { ?>  selected="selected"<?php } ?>>12</option>
                    </select>  
                    <select id="exp_year" name="exp_year" class="inputbox exp_month" >
                        <?php
                        $year = date('Y', time());
                        $num = 1;
                        while ($num <= 7) {
                            echo '<option value="' . $year . '">' . $year . '</option>';
                            $year++;
                            $num++;
                        }
                        ?>
                    </select>
                </li>
                 <li>
                    <label for="x_card_num">Credit Card Number<span class="required">*</span></label>
                    <input type="text" name="x_card_num" id="x_card_num" class="inputbox" value=""  />
                    <?php if(isset($errorCardNumber)): ?>
                        <label class="error"><?php echo $errorCardNumber; ?></label>
                    <?php endif; ?>
                </li>
                <li>
                    <label for="x_card_code">Card (CVV) Code<span class="required">*</span></label>
                    <input type="password" name="x_card_code" id="x_card_code" class="inputbox" value="" size="5" />
                    <?php if(isset($errorCardCode)): ?>
                        <label class="error"><?php echo $errorCardCode; ?></label>
                    <?php endif; ?>
                </li>


                <li class="comment-box">			
                    <label for="comment">Comment</label>
                    <textarea rows="7" cols="50" name="comment" id="comment" class="inputbox"></textarea>
                </li>														
                <li class="donate-button">
                    <input type="submit" class="button donate_btn_submit" name="btnSubmit" value="Donate">
                </li>										
            </ul>
            <input type="hidden" name="payment_method" value="Authorize" />
        </form>
    </div>
    
    <script>
        jQuery(document).ready(function() {

                jQuery.validator.addMethod(
                    "money",
                    function(value, element) {
                        var isValidMoney = /^\d{0,4}(\.\d{0,2})?$/.test(value);
                        return this.optional(element) || isValidMoney;
                    },
                    "Please enter a valid amount"
                );

                jQuery("#church_donation_form").validate({
                        rules: {
                                first_name: "required",
                                last_name: "required",
                                address: "required",
                                address: {required: true,maxlength: 50},
                                city: "required",
                                state: "required",
                                country: "required",
                                zip: "required",
                                phone: "required",
                                donate_cateogry: "required",
                                email: {required: true,email: true},
                                amount: {required: true,money: true},
                                card_type: "required",
                                x_card_num: {required: true,digits: true},
                                x_card_code: "required"
                        },
                        messages: {
                                first_name: "Please enter first name",
                                last_name: "Please enter last name",
                                address: "Please enter address",
                                address: {required: "Please enter address",maxlength: "Please ensure that the address provided has at least 50 characters"},
                                city: "Please enter city",
                                state: "Please enter state",
                                country: "Please select country",
                                zip: "Please enter zip code",
                                phone: "Please enter phone number",
                                donate_cateogry: "Please select donation category",
                                email: {email: "Please enter a valid email address",required: "Please enter email address"},
                                amount: {money: "Please enter a valid amount",required: "Please enter donation amount"},
                                card_type: "Please select type of card",
                                x_card_num: {digits: "Please enter only digits",required: "Please enter card number"},
                                x_card_code: "Please enter CVV code"
                        }
                });	
        });
        </script>

        <?php
        $output = apply_filters('wp_donate_filter_form', ob_get_contents());
        ob_end_clean();

        return $output;
    }
}
?>
