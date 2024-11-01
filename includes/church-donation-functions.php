<?php 
/**
 * function processAuthorizePayment
 * 
 * Will process payment and return true or false also set message based on outcomes of payment process
 * 
 * @param type $methodToUse
 * @param type $REQUEST
 * @return boolean
 */
function processAuthorizePayment($methodToUse, $REQUEST) {
    if (!validateCreditCard($REQUEST['x_card_num'], $REQUEST['card_type'], $ccerror, $ccerrortext)) {
        
            $message = 'Please enter valid credit card number.  ' . $REQUEST['x_card_num'] . ' is not valid card number.';
            
            if (get_option('failed_response')) {
                update_option('failed_response', $message);                    
            }else{
                add_option('failed_response');
                update_option('failed_response', $message);
            }

            return false;

    } else {
                	
		if(AUTHORIZENET_SANDBOX){
			$apiUrl = "https://apitest.authorize.net/xml/v1/request.api";
		}else{
			$apiUrl = "https://api.authorize.net/xml/v1/request.api";
		}

        $cc = filter_var($REQUEST['x_card_num'], FILTER_SANITIZE_NUMBER_INT);
        $exp = filter_var(trim($REQUEST['exp_month']) . "-" . trim($REQUEST['exp_year']), FILTER_SANITIZE_STRING);        
        $cvv = filter_var(trim($REQUEST['x_card_code']), FILTER_SANITIZE_STRING);
                
        $xml_post_data = "
            <createTransactionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                <merchantAuthentication>
                    <name>". AUTHORIZENET_API_LOGIN_ID."</name>
                    <transactionKey>". AUTHORIZENET_TRANSACTION_KEY."</transactionKey>
                </merchantAuthentication>
                <refId>4455</refId>
                <transactionRequest>
                    <transactionType>authCaptureTransaction</transactionType>
                    <amount>". $REQUEST['amount']."</amount>
                    <payment>
                        <creditCard>
                            <cardNumber>". $cc ."</cardNumber>
                            <expirationDate>". $exp."</expirationDate>
                            <cardCode>". $cvv ."</cardCode>
                        </creditCard>
                    </payment>
                    <order>
                        <description>Donation received successfully</description>
                    </order>
                    <customer>
                        <email>". $REQUEST['email']."</email>
                    </customer>
                    <billTo>
                        <firstName>". $REQUEST['first_name'] ."</firstName>
                        <lastName>". $REQUEST['last_name'] ."</lastName>
                        <company>". $REQUEST['organization'] ."</company>
                        <address>". $REQUEST['address'] ."</address>
                        <city>" . $REQUEST['city'] . "</city>
                        <state>" . $REQUEST['state'] . "</state>
                        <zip>" . $REQUEST['zip'] . "</zip>
                        <country>US</country>
                        <phoneNumber>" . $REQUEST['phone'] . "</phoneNumber>            
                    </billTo>
                    <userFields>
                        <userField>
                            <name>DonateCategory</name>
                            <value>" . $REQUEST['donate_cateogry'] . "</value>
                        </userField>            
                    </userFields>                    
                    <authorizationIndicatorType>
                        <authorizationIndicator>final</authorizationIndicator>
                    </authorizationIndicatorType>
                </transactionRequest>
            </createTransactionRequest>";  

            $ch  = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_data);
            $result = curl_exec($ch);
            $response = @simplexml_load_string($result);        
            curl_close($ch);        
			
			if($response->transactionResponse->responseCode==1){
                return true;
			}else {
                $error = (string) $response->transactionResponse->errors->error->errorText;
                if (get_option('failed_response')) {
                    update_option('failed_response', $error);                    
                 }else{
                    add_option('failed_response');
                    update_option('failed_response', $error);
                } 
                
                return false;
            }
			
	}
}

/**
 * function validateCreditCard
 *  
 *  Define the cards we support. You may add additional card types.
 *  Name:      As in the selection box of the form - must be same as user's
 *  Length:    List of possible valid lengths of the card number for the card
 *  Prefixes:  List of possible prefixes for the card
 *  Checkdigit Boolean to say whether there is a check digit
 *  Don't forget - all but the last array definition needs a comma separator!
 * 
 * @param type $cardnumber
 * @param type $cardname
 * @param type $errornumber
 * @param type $errortext
 * @return boolean
 * 
 * 
 */
function validateCreditCard($cardnumber, $cardname, &$errornumber, &$errortext) {

    $cards = array(array('name' => 'AMEX',
            'length' => '15',
            'prefixes' => '34,37',
            'checkdigit' => true
        ),
        array('name' => 'Diners Club Carte Blanche',
            'length' => '14',
            'prefixes' => '300,301,302,303,304,305',
            'checkdigit' => true
        ),
        array('name' => 'DINERS',
            'length' => '14,16',
            'prefixes' => '305,36,38,54,55',
            'checkdigit' => true
        ),
        array('name' => 'Discover',
            'length' => '16',
            'prefixes' => '6011,622,64,65',
            'checkdigit' => true
        ),
        array('name' => 'Diners Club Enroute',
            'length' => '15',
            'prefixes' => '2014,2149',
            'checkdigit' => true
        ),
        array('name' => 'JCB',
            'length' => '16',
            'prefixes' => '35',
            'checkdigit' => true
        ),
        array('name' => 'Maestro',
            'length' => '12,13,14,15,16,18,19',
            'prefixes' => '5018,5020,5038,6304,6759,6761',
            'checkdigit' => true
        ),
        array('name' => 'MASTERCARD',
            'length' => '16',
            'prefixes' => '51,52,53,54,55',
            'checkdigit' => true
        ),
        array('name' => 'Solo',
            'length' => '16,18,19',
            'prefixes' => '6334,6767',
            'checkdigit' => true
        ),
        array('name' => 'Switch',
            'length' => '16,18,19',
            'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759',
            'checkdigit' => true
        ),
        array('name' => 'VISA',
            'length' => '16',
            'prefixes' => '4',
            'checkdigit' => true
        ),
        array('name' => 'VISA Electron',
            'length' => '16',
            'prefixes' => '417500,4917,4913,4508,4844',
            'checkdigit' => true
        ),
        array('name' => 'LaserCard',
            'length' => '16,17,18,19',
            'prefixes' => '6304,6706,6771,6709',
            'checkdigit' => true
        )
    );

    $ccErrorNo = 0;

    $ccErrors [0] = 'Please enter a valid ' . $cardname . ' number.';
    $ccErrors [1] = "No card number provided";
    $ccErrors [2] = "Credit card number has invalid format";
    $ccErrors [3] = "Credit card number is invalid";
    $ccErrors [4] = "Credit card number is wrong length";

    // Establish card type
    $cardType = -1;
    for ($i = 0; $i < sizeof($cards); $i++) {

        // See if it is this card (ignoring the case of the string)
        if (strtolower($cardname) == strtolower($cards[$i]['name'])) {
            $cardType = $i;
            break;
        }
    }

    // If card type not found, report an error
    if ($cardType == -1) {
        $errornumber = 0;
        $errortext = $ccErrors [$errornumber];
        return false;
    }

    // Ensure that the user has provided a credit card number
    if (strlen($cardnumber) == 0) {
        $errornumber = 1;
        $errortext = $ccErrors [$errornumber];
        return false;
    }

    // Remove any spaces from the credit card number
    $cardNo = str_replace(' ', '', $cardnumber);

    // Check that the number is numeric and of the right sort of length.
    if (!preg_match("/^[0-9]{13,19}$/", $cardNo)) {
        $errornumber = 2;
        $errortext = $ccErrors [$errornumber];
        return false;
    }

    // Now check the modulus 10 check digit - if required
    if ($cards[$cardType]['checkdigit']) {
        $checksum = 0;                                  // running checksum total
        $mychar = "";                                   // next char to process
        $j = 1;                                         // takes value of 1 or 2
        // Process each digit one by one starting at the right
        for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {

            // Extract the next digit and multiply by 1 or 2 on alternative digits.
            $calc = $cardNo[$i] * $j;

            // If the result is in two digits add 1 to the checksum total
            if ($calc > 9) {
                $checksum = $checksum + 1;
                $calc = $calc - 10;
            }

            // Add the units element to the checksum total
            $checksum = $checksum + $calc;

            // Switch the value of j
            if ($j == 1) {
                $j = 2;
            } else {
                $j = 1;
            };
        }

        // All done - if checksum is divisible by 10, it is a valid modulus 10.
        // If not, report an error.
        if ($checksum % 10 != 0) {
            $errornumber = 3;
            $errortext = $ccErrors [$errornumber];
            return false;
        }
    }

    // The following are the card-specific checks we undertake.
    // Load an array with the valid prefixes for this card
    $prefix = explode(',', $cards[$cardType]['prefixes']);

    // Now see if any of them match what we have in the card number
    $PrefixValid = false;
    for ($i = 0; $i < sizeof($prefix); $i++) {
        $exp = '/^' . $prefix[$i] . '/';
        if (preg_match($exp, $cardNo)) {
            $PrefixValid = true;
            break;
        }
    }

    // If it isn't a valid prefix there's no point at looking at the length
    if (!$PrefixValid) {
        $errornumber = 3;
        $errortext = $ccErrors [$errornumber];
        return false;
    }

    // See if the length is valid for this card
    $LengthValid = false;
    $lengths = explode(',', $cards[$cardType]['length']);
    for ($j = 0; $j < sizeof($lengths); $j++) {
        if (strlen($cardNo) == $lengths[$j]) {
            $LengthValid = true;
            break;
        }
    }

    // See if all is OK by seeing if the length was valid.
    if (!$LengthValid) {
        $errornumber = 4;
        $errortext = $ccErrors [$errornumber];
        return false;
    };

    // The credit card is in the required format.
    return true;
}

/**
 * function getCountryArray
 * Will return complete list of countries for donation form
 * 
 * @return array
 */
function getCountryArray() {
    return array(
        "Afghanistan" => "Afghanistan",
        "Albania" => "Albania",
        "Algeria" => "Algeria",
        "American Samoa" => "American Samoa",
        "Andorra" => "Andorra",
        "Angola" => "Angola",
        "Anguilla" => "Anguilla",
        "Antarctica" => "Antarctica",
        "Antigua and Barbuda" => "Antigua and Barbuda",
        "Argentina" => "Argentina",
        "Armenia" => "Armenia",
        "Aruba" => "Aruba",
        "Australia" => "Australia",
        "Austria" => "Austria",
        "Azerbaijan" => "Azerbaijan",
        "Bahamas" => "Bahamas",
        "Bahrain" => "Bahrain",
        "Bangladesh" => "Bangladesh",
        "Barbados" => "Barbados",
        "Belarus" => "Belarus",
        "Belgium" => "Belgium",
        "Belize" => "Belize",
        "Benin" => "Benin",
        "Bermuda" => "Bermuda",
        "Bhutan" => "Bhutan",
        "Bolivia" => "Bolivia",
        "Bosnia and Herzegovina" => "Bosnia and Herzegovina",
        "Botswana" => "Botswana",
        "Bouvet Island" => "Bouvet Island",
        "Brazil" => "Brazil",
        "British Indian Ocean Territory" => "British Indian Ocean Territory",
        "Brunei Darussalam" => "Brunei Darussalam",
        "Bulgaria" => "Bulgaria",
        "Burkina Faso" => "Burkina Faso",
        "Burundi" => "Burundi",
        "Cambodia" => "Cambodia",
        "Cameroon" => "Cameroon",
        "Canada" => "Canada",
        "Cape Verde" => "Cape Verde",
        "Cayman Islands" => "Cayman Islands",
        "Central African Republic" => "Central African Republic",
        "Chad" => "Chad",
        "Chile" => "Chile",
        "China" => "China",
        "Christmas Island" => "Christmas Island",
        "Cocos (Keeling) Islands" => "Cocos (Keeling) Islands",
        "Colombia" => "Colombia",
        "Comoros" => "Comoros",
        "Congo" => "Congo",
        "Congo,  the Democratic Republic of the" => "Congo,  the Democratic Republic of the",
        "Cook Islands" => "Cook Islands",
        "Costa Rica" => "Costa Rica",
        "Cote D'Ivoire" => "Cote D'Ivoire",
        "Croatia" => "Croatia",
        "Cuba" => "Cuba",
        "Cyprus" => "Cyprus",
        "Czech Republic" => "Czech Republic",
        "Denmark" => "Denmark",
        "Djibouti" => "Djibouti",
        "Dominica" => "Dominica",
        "Dominican Republic" => "Dominican Republic",
        "Ecuador" => "Ecuador",
        "Egypt" => "Egypt",
        "El Salvador" => "El Salvador",
        "Equatorial Guinea" => "Equatorial Guinea",
        "Eritrea" => "Eritrea",
        "Estonia" => "Estonia",
        "Ethiopia" => "Ethiopia",
        "Falkland Islands (Malvinas)" => "Falkland Islands (Malvinas)",
        "Faroe Islands" => "Faroe Islands",
        "Fiji" => "Fiji",
        "Finland" => "Finland",
        "France" => "France",
        "French Guiana" => "French Guiana",
        "French Polynesia" => "French Polynesia",
        "French Southern Territories" => "French Southern Territories",
        "Gabon" => "Gabon",
        "Gambia" => "Gambia",
        "Georgia" => "Georgia",
        "Germany" => "Germany",
        "Ghana" => "Ghana",
        "Gibraltar" => "Gibraltar",
        "Greece" => "Greece",
        "Greenland" => "Greenland",
        "Grenada" => "Grenada",
        "Guadeloupe" => "Guadeloupe",
        "Guam" => "Guam",
        "Guatemala" => "Guatemala",
        "Guinea" => "Guinea",
        "Guinea-Bissau" => "Guinea-Bissau",
        "Guyana" => "Guyana",
        "Haiti" => "Haiti",
        "Heard Island and Mcdonald Islands" => "Heard Island and Mcdonald Islands",
        "Holy See (Vatican City State)" => "Holy See (Vatican City State)",
        "Honduras" => "Honduras",
        "Hong Kong" => "Hong Kong",
        "Hungary" => "Hungary",
        "Iceland" => "Iceland",
        "India" => "India",
        "Indonesia" => "Indonesia",
        "Iran,  Islamic Republic of" => "Iran,  Islamic Republic of",
        "Iraq" => "Iraq",
        "Ireland" => "Ireland",
        "Israel" => "Israel",
        "Italy" => "Italy",
        "Jamaica" => "Jamaica",
        "Japan" => "Japan",
        "Jordan" => "Jordan",
        "Kazakhstan" => "Kazakhstan",
        "Kenya" => "Kenya",
        "Kiribati" => "Kiribati",
        "Korea,  Democratic People's Republic of" => "Korea,  Democratic People's Republic of",
        "Korea,  Republic of" => "Korea,  Republic of",
        "Kuwait" => "Kuwait",
        "Kyrgyzstan" => "Kyrgyzstan",
        "Lao People's Democratic Republic" => "Lao People's Democratic Republic",
        "Latvia" => "Latvia",
        "Lebanon" => "Lebanon",
        "Lesotho" => "Lesotho",
        "Liberia" => "Liberia",
        "Libyan Arab Jamahiriya" => "Libyan Arab Jamahiriya",
        "Liechtenstein" => "Liechtenstein",
        "Lithuania" => "Lithuania",
        "Luxembourg" => "Luxembourg",
        "Macao" => "Macao",
        "Macedonia,  the Former Yugoslav Republic of" => "Macedonia,  the Former Yugoslav Republic of",
        "Madagascar" => "Madagascar",
        "Malawi" => "Malawi",
        "Malaysia" => "Malaysia",
        "Maldives" => "Maldives",
        "Mali" => "Mali",
        "Malta" => "Malta",
        "Marshall Islands" => "Marshall Islands",
        "Martinique" => "Martinique",
        "Mauritania" => "Mauritania",
        "Mauritius" => "Mauritius",
        "Mayotte" => "Mayotte",
        "Mexico" => "Mexico",
        "Micronesia,  Federated States of" => "Micronesia,  Federated States of",
        "Moldova,  Republic of" => "Moldova,  Republic of",
        "Monaco" => "Monaco",
        "Mongolia" => "Mongolia",
        "Montserrat" => "Montserrat",
        "Morocco" => "Morocco",
        "Mozambique" => "Mozambique",
        "Myanmar" => "Myanmar",
        "Namibia" => "Namibia",
        "Nauru" => "Nauru",
        "Nepal" => "Nepal",
        "Netherlands" => "Netherlands",
        "Netherlands Antilles" => "Netherlands Antilles",
        "New Caledonia" => "New Caledonia",
        "New Zealand" => "New Zealand",
        "Nicaragua" => "Nicaragua",
        "Niger" => "Niger",
        "Nigeria" => "Nigeria",
        "Niue" => "Niue",
        "Norfolk Island" => "Norfolk Island",
        "Northern Mariana Islands" => "Northern Mariana Islands",
        "Norway" => "Norway",
        "Oman" => "Oman",
        "Pakistan" => "Pakistan",
        "Palau" => "Palau",
        "Palestinian Territory,  Occupied" => "Palestinian Territory,  Occupied",
        "Panama" => "Panama",
        "Papua New Guinea" => "Papua New Guinea",
        "Paraguay" => "Paraguay",
        "Peru" => "Peru",
        "Philippines" => "Philippines",
        "Pitcairn" => "Pitcairn",
        "Poland" => "Poland",
        "Portugal" => "Portugal",
        "Puerto Rico" => "Puerto Rico",
        "Qatar" => "Qatar",
        "Reunion" => "Reunion",
        "Romania" => "Romania",
        "Russian Federation" => "Russian Federation",
        "Rwanda" => "Rwanda",
        "Saint Helena" => "Saint Helena",
        "Saint Kitts and Nevis" => "Saint Kitts and Nevis",
        "Saint Lucia" => "Saint Lucia",
        "Saint Pierre and Miquelon" => "Saint Pierre and Miquelon",
        "Saint Vincent and the Grenadines" => "Saint Vincent and the Grenadines",
        "Samoa" => "Samoa",
        "San Marino" => "San Marino",
        "Sao Tome and Principe" => "Sao Tome and Principe",
        "Saudi Arabia" => "Saudi Arabia",
        "Senegal" => "Senegal",
        "Serbia and Montenegro" => "Serbia and Montenegro",
        "Seychelles" => "Seychelles",
        "Sierra Leone" => "Sierra Leone",
        "Singapore" => "Singapore",
        "Slovakia" => "Slovakia",
        "Slovenia" => "Slovenia",
        "Solomon Islands" => "Solomon Islands",
        "Somalia" => "Somalia",
        "South Africa" => "South Africa",
        "South Georgia and the South Sandwich Islands" => "South Georgia and the South Sandwich Islands",
        "Spain" => "Spain",
        "Sri Lanka" => "Sri Lanka",
        "Sudan" => "Sudan",
        "Suriname" => "Suriname",
        "Svalbard and Jan Mayen" => "Svalbard and Jan Mayen",
        "Swaziland" => "Swaziland",
        "Sweden" => "Sweden",
        "Switzerland" => "Switzerland",
        "Syrian Arab Republic" => "Syrian Arab Republic",
        "Taiwan,  Province of China" => "Taiwan,  Province of China",
        "Tajikistan" => "Tajikistan",
        "Tanzania,  United Republic of" => "Tanzania,  United Republic of",
        "Thailand" => "Thailand",
        "Timor-Leste" => "Timor-Leste",
        "Togo" => "Togo",
        "Tokelau" => "Tokelau",
        "Tonga" => "Tonga",
        "Trinidad and Tobago" => "Trinidad and Tobago",
        "Tunisia" => "Tunisia",
        "Turkey" => "Turkey",
        "Turkmenistan" => "Turkmenistan",
        "Turks and Caicos Islands" => "Turks and Caicos Islands",
        "Tuvalu" => "Tuvalu",
        "Uganda" => "Uganda",
        "Ukraine" => "Ukraine",
        "United Arab Emirates" => "United Arab Emirates",
        "United Kingdom" => "United Kingdom",
        "United States" => "United States",
        "United States Minor Outlying Islands" => "United States Minor Outlying Islands",
        "Uruguay" => "Uruguay",
        "Uzbekistan" => "Uzbekistan",
        "Vanuatu" => "Vanuatu",
        "Venezuela" => "Venezuela",
        "Viet Nam" => "Viet Nam",
        "Virgin Islands,  British" => "Virgin Islands,  British",
        "Virgin Islands,  U.s." => "Virgin Islands,  U.s.",
        "Wallis and Futuna" => "Wallis and Futuna",
        "Western Sahara" => "Western Sahara",
        "Yemen" => "Yemen",
        "Zambia" => "Zambia",
        "Zimbabwe" => "Zimbabwe",
    );
}

function sendNotificationToUser($to, $subject, $request, $content, $paymentStatus){
    
    // get email header
    $message = getEmailHeader();
    
    // add content added from admin area for this email
    $message .= "<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>Hello ".  ucfirst($request['first_name'])." ".ucfirst($request['last_name']).",</td></tr>
	<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>&nbsp;</td></tr>
	<tr><td>".$content."</td></tr>
	<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>&nbsp;</td></tr>
	<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>We have received your donation request with details mentioned below:</td></tr>
    <tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>&nbsp;</td></tr>
    ";
    
    
    // add message according to payment status
    if($paymentStatus == "Pending"){
        $message .= "<table border='0'><tr><td>There are some internal problems in payment system so we were enable to receive your donation, we will contact you sooner with further details.</td></tr></table>";
    }
    
    $message .= '<tr><td colspan="2" bgcolor="#ccc"><table width="100%" cellspacing="1" cellpadding="10">
		<tr><td width="17%" height="25" align="left" valign="middle" bgcolor="#FFFFFF">First name</td><td width="83%" height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['first_name'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Last name</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['last_name'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Email</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['email'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Phone</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['phone'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Organization</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['organization'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Address</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['address'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Zip</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['zip'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">City</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['city'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">State</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['state'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Country</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['country'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Donation amount</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">$'.$request['amount'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Payment made with</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['payment_method'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Comment</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['comment'].'</td></tr>
		</table></td></tr>';
    
    // get email footer
    $message .= getEmailFooter(); 
    sendEmail($to, $subject, $message);
    
}

function sendNotificationToAdmin($to, $subject, $request, $content, $paymentStatus){
    
    // get email header
    $message = getEmailHeader();
    
    
    $message .= "<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>Hello Admin,</td></tr>
	<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>&nbsp;</td></tr>
	<tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>We have received your donation request with details mentioned below:</td></tr>
    <tr><td height='25' colspan='2' align='left' valign='middle' bgcolor='#FFFFFF'>&nbsp;</td></tr>";
	
	if($paymentStatus == "Pending"){
        $message .= "<table border='0'><tr><td><b>There was some problem with processing payment, please check Authorize.net settings and account for more details.</b></td></tr></table>";
    }
    
    // add content added from admin area for this email
    //$message .= "<table></table>";
    
     $message .= '<tr><td colspan="2" bgcolor="#ccc"><table width="100%" cellspacing="1" cellpadding="10">
		<tr><td width="17%" height="25" align="left" valign="middle" bgcolor="#FFFFFF">First name</td><td width="83%" height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['first_name'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Last name</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['last_name'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Email</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['email'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Phone</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['phone'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Organization</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['organization'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Address</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['address'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Zip</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['zip'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">City</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['city'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">State</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['state'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Country</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['country'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Donation amount</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">$'.$request['amount'].'</td></tr>
		<tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Payment made with</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['payment_method'].'</td></tr>
        <tr><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">Comment</td><td height="25" align="left" valign="middle" bgcolor="#FFFFFF">'.$request['comment'].'</td></tr>
		</table></td></tr>';
    
    // get email footer
    $message .= getEmailFooter(); 
   
    sendEmail($to, $subject, $message);
    
}

function sendEmail($to, $subject, $message){
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . get_option( 'blogname' ) . '<' . get_option( 'admin_email' ) . '>' . "\r\n";
    wp_mail($to, $subject, $message, $headers);
}

function getEmailHeader() {
        $headerHTML = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="" content="" />
                <meta name="" content="" />
                <style type="text/css">
                    body {
                        margin:0px;
                        padding:0px;
                        color:#333333;
                        font-family:Arial, Helvetica, sans-serif;
                        font-size:14px;
                        width:100% !important;
                    }
                </style>
            </head>
            <body>
            <table width="99%" cellspacing="0" cellpadding="0" border="0">
                <!-- Start of main container -->
                <tr><td><table width="100%" cellspacing="0" cellpadding="0">';
        return $headerHTML;
}

function getEmailFooter() {
            $footerHTML = '<tr><td height="25" align="left" colspan="2" valign="middle" bgcolor="#FFFFFF">&nbsp;</td></tr>
			<tr><td height="25" colspan="2" align="left" valign="middle" bgcolor="#FFFFFF">Thank you,<br/><a href="' . site_url() . '">'.get_option( 'blogname' ).'</a></td></tr>
            
                </table>
                <!-- End of footer -->
                </td>
                </tr>
                <!-- End of main container -->
                </table>
                </body>
                </html>'; 
        return $footerHTML;
}

function wp_church_donation_export(){


    global $wpdb;
    $allEntries = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "church_donation order by date desc");
    ob_clean();
    
    
    // printing headers
    $sep = "\t";
    echo "First name" . $sep;
    echo "Last name" . $sep;
    echo "Organization" . $sep;
    echo "Address" . $sep;
    echo "City" . $sep;
    echo "State" . $sep;
    echo "Country" . $sep;
    echo "Zipcode" . $sep;
    echo "Phone" . $sep;
    echo "Email address" . $sep;
    echo "Amount" . $sep;
    echo "Payment status" . $sep;
    echo "Payment method" . $sep;
    echo "Payment date" . $sep;
    echo "Comments" . $sep;
    print("\n");

    foreach ($allEntries as $key => $entry) {

        $schema_insert = "";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->first_name);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->last_name);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->organization);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->address);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->city);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->state);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->country);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->zip);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->phone);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->email);
        $schema_insert .= "\t";
        $schema_insert .= "$" . preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->amount);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->payment_status);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->payment_method);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->date);
        $schema_insert .= "\t";
        $schema_insert .= preg_replace("/\r\n|\n\r|\n|\r/", " ", $entry->comment);
        $schema_insert .= "\t";

        print(trim($schema_insert));
        print "\n";
    }

    header("Content-Type: application/vnd.ms-excel; charset=iso-8859-1");
    header("Content-type: application/x-msexcel; charset=iso-8859-1");
    header('Content-Disposition: csv; filename=wp-church-donation-entries-' . date('Y-m-d') . '.csv');
    header('Pragma: no-cache');
    header('Expires: 0');
    exit;
}   
