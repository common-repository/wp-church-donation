<?php
/* 	
  Plugin Name: WP Church Donation
  Plugin URI: http://wordpress.org/extend/plugins/wp-church-donation/
  Description: Accept online donations for the church. WP Church Donation Plugin helps to accept donations from your sponsors, donors, members, fans and supporters via credit card using Authorize.Net
  Author: Ashish Ajani
  Version: 1.7
  Author URI: http://freelancer-coder.com/
 */

// Security: Considered blocking direct access to PHP files by adding the following line. 
defined('ABSPATH') or die("No script kiddies please!");

// Starting session
session_start();

// define required contastants
@define('WP_CHURCH_DONATION_VERSION', '1.7');
@define('WP_CHURCH_DONATION_PATH', WP_PLUGIN_URL . '/' . end(explode(DIRECTORY_SEPARATOR, dirname(__FILE__))));


// including required files
include_once('includes/church-donation-functions.php');
include_once('includes/church-donation-form-display.php');
include_once('includes/church-donation-listings.php');
include_once('includes/church-donation-settings-authorize.php');
include_once('includes/church-donation-options.php');

// add actions to include plugin css and js
add_action('wp_print_styles', 'load_wp_church_donation_css');
add_action('wp_print_scripts', 'load_wp_church_donation_js');
add_action('admin_print_styles', 'load_wp_church_donation_admin_css');
add_action('admin_print_scripts', 'load_wp_church_donation_admin_js');

// load CSS for admin area interfaces
function load_wp_church_donation_admin_css() {
    wp_enqueue_style('wp-church-donation-admin-css', WP_CHURCH_DONATION_PATH . '/css/wp-church-donation-admin.css');
}

// load JS for admin area interfaces
function load_wp_church_donation_admin_js() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
}

// load JS for utility functions related to donation
function load_wp_church_donation_js() {
    wp_enqueue_script('wp-church-donation-utility-js', WP_CHURCH_DONATION_PATH . '/js/churchDonationUtility.js', array('jquery'));
    wp_enqueue_script('wp-church-jquery-validate-js', WP_CHURCH_DONATION_PATH . '/js/jquery.validate.js', array('jquery'));
}

// load CSS required in donation form at frontend
function load_wp_church_donation_css() {
    wp_enqueue_style('wp-church-donation-form-css', WP_CHURCH_DONATION_PATH . '/css/wp-church-donation-form.css');
}


// add menu items and actions for church donation plugin
function church_donation_add_menu_items() {
    add_menu_page('WP Church Donation', 'Church Donation', 'manage_options', 'wp_church_donation', 'wp_church_donation_listings_page');
    add_submenu_page('wp_church_donation', 'Auth.Net Settings', 'Auth.Net Settings', 'manage_options', 'wp_church_donation_settings_authorize', 'wp_church_donation_settings_authorize');
    add_submenu_page('wp_church_donation', 'Content', 'Content', 'manage_options', 'wp_church_donation_options', 'wp_church_donation_options');
}
add_action('admin_menu', 'church_donation_add_menu_items');

function church_donation_the_content_filter($content) {
	$content = str_replace('[Show Church Donation Form]','[Show_Church_Donation_Form]',$content);
	return $content;
}
add_filter( 'the_content', 'church_donation_the_content_filter' );

// add short code to use donation form
add_shortcode('Show_Church_Donation_Form', 'wp_church_donation_form');



// add hook and function to install WP Church Donation Plugin
register_activation_hook(__FILE__, 'install_wp_church_donation');

global $church_donation_db_version;
$church_donation_db_version = "1.0";


// wp_church_donation installation process
function install_wp_church_donation() {
    global $wpdb;
    global $church_donation_db_version;

    
    // create table for donation entries
    $table_name = $wpdb->prefix . "church_donation";
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `first_name` varchar(255) CHARACTER SET utf8 NOT NULL,
        `last_name` varchar(255) CHARACTER SET utf8 NOT NULL,
        `organization` varchar(255) CHARACTER SET utf8 NOT NULL,
        `address` varchar(255) CHARACTER SET utf8 NOT NULL,
        `city` varchar(255) CHARACTER SET utf8 NOT NULL,
        `country` varchar(255) CHARACTER SET utf8 NOT NULL,
        `state` varchar(255) CHARACTER SET utf8 NOT NULL,
        `zip` varchar(255) CHARACTER SET utf8 NOT NULL,
        `phone` varchar(255) NOT NULL,
        `donate_cateogry` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `donation_type` varchar(255) NOT NULL,
        `amount` varchar(255) NOT NULL,
        `comment` text NOT NULL,
        `payment_status` varchar(255) NOT NULL,
        `payment_method` varchar(255) NOT NULL,
        `date` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id` (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

    // create table for authorize.net payment gateway settings
    $church_donation_settings = $wpdb->prefix . "church_donation_settings";
    $church_donation_settings_sql = "CREATE TABLE IF NOT EXISTS `$church_donation_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `payment_gateway_name` varchar(255) NOT NULL,
        `payment_method` varchar(255) NOT NULL,
        `credentials_data` text NOT NULL,
        `enabled` int(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id` (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

    // insert default record in authorize.net settings table
    $church_donation_settings_insert_sql = "INSERT INTO `$church_donation_settings` 
        (`id` ,`payment_gateway_name` ,`payment_method` ,`credentials_data` ,`enabled`) 
        VALUES 
        ('', 'Authorize', 'AIM', '', '1');";
    
    // create table for thank you email, thank you page and other donation form related content management
    $church_donation_content = $wpdb->prefix . "church_donation_content";
    $church_donation_content_sql = "CREATE TABLE IF NOT EXISTS `$church_donation_content` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `donation_form_heading` varchar(255) NOT NULL,
        `donation_form_message` text NOT NULL,
        `thank_you_page_content` text NOT NULL,
        `thank_you_email_subject` text NOT NULL,
        `thank_you_email_content` text NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id` (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    
    // insert default record in authorize.net settings table
    $church_donation_content_insert_sql = "INSERT INTO `$church_donation_content` 
        (`id` ,`donation_form_heading` ,`donation_form_message` ,`thank_you_page_content` ,`thank_you_email_subject`,`thank_you_email_content`) 
        VALUES 
        ('', 
        'Make your donation', 
        'Thank you for your interest in supporting our Church. Your financial contributions provide an immediate answer to the most pressing needs for outreach.
        These gifts help us overcome the everyday challenges that stand between our future vision and our present reality. Your gift will go to the area of greatest need. If you wish your donation to be designated for a particular area or program, please note your wishes in the comment box. 
        Thank you for your generosity.', 
        'Thank you for your interest in supporting our Church. Your financial contributions provide an immediate answer to the most pressing needs for outreach.
        These gifts help us overcome the everyday challenges that stand between our future vision and our present reality. Your gift will go to the area of greatest need. If you wish your donation to be designated for a particular area or program. 
        Thank you for your donation.',
        'We received your donation, thank you very much for this.',
        'Thank you for your interest in supporting our Church. Your financial contributions provide an immediate answer to the most pressing needs for outreach.
        These gifts help us overcome the everyday challenges that stand between our future vision and our present reality. Your gift will go to the area of greatest need. If you wish your donation to be designated for a particular area or program.
        Thank you for your donation.');";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
    dbDelta($church_donation_settings_sql);
    dbDelta($church_donation_settings_insert_sql);
    dbDelta($church_donation_content_sql);
    dbDelta($church_donation_content_insert_sql);

    add_option("church_donation_db_version", $church_donation_db_version);
}