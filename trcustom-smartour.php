<?php 
/**
 * @package trcustom-smartour
 * @param WP_Post $post The object for the current post/page.
 * @param int $post_id The ID of the post being saved.
/*
Plugin Name: TR Smartour
Plugin URI: https://smartours.com/
Description: This plugin creates a new CPT for 'Tours' with specific CPT settings and custom fields.
Version: 1.0.0
Author: Tom Robbins
Author URI: https://github.com/DevRobbins
License: GPLv2 or later
Text Domain: trcustom-smartour
*/

/*
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
*/

// Stop anyone who isnt logged in from accessing this plugin file. 
// if(!defined('ABSPATH')) {
//     die;
// }

/*
    Option A) WordPress Plugin
    Please create a simple WordPress plugin that adds a new custom post type for ‘Tours’. This CPT
    should include the capability to save revision history, and have the standard post tag taxonomy. In addition,
    create a custom taxonomy and attach it to the CPT . The new taxonomy should be called ‘Types’ and will be
    used to track the type of a tour (Cruise, Adventure, Relaxed, etc)
    Finally, add two custom fields (post meta) to this CPT. The first should called ‘Tour Code’ that allows
    users to enter and save only uppercase-alphanumeric strings (such as THXA, POR22, or CRK). Secondly add
    a field called ‘Departure Date’, that will accept a date that the tour leaves. (Accomplish these without using
    another plugin like ACF, but with custom PHP code).
*/

defined('ABSPATH') or die('Error 403, you can\'t access this file.');

class TrSmartourPlugin 
{
    function __construct() {
        add_action('init', array( $this, 'custom_post_type'));  
        add_action( 'init', array($this, 'custom_taxonomy'), 0 ); 
        add_action( 'init', array($this, 'add_other_taxonomies' ));  
        add_action( 'add_meta_boxes', 'tours_add_meta_box' );        
    }

    function activate() {
        // generate a CPT
        $this->custom_post_type(); 
        $this->custom_taxonomy();
        $this->add_other_taxonomies();
        // flush rewrite rules
        flush_rewrite_rules();
    }

    function deactivate() {
        // flush rewrite rules
        flush_rewrite_rules();
    }

    function custom_post_type() {
            register_post_type( 'tours',
            array (
                'labels' => array(
                    'name' => 'Tours',
                    'singular_name' => 'Tour',					
                    'add_new_item' => 'Add New Tour',
                    'edit' => 'Edit A Tour',
                    'new_item' => 'New Tour',
                    'view_item' => 'View Tour',
                    'search_items' => 'Search Tours',
                    'not_found' => 'No Tours Found',
                    // 'taxonomies'  => array( 'tour-tags', 'post_tag', 'category' ),
                    'taxonomies'  => array( 'tour-tags', 'post_tag'),
                    'not_found_in_trash' => 'No Tours found in trash',
                ),
                'public' => true,
                'menu_icon' => 'dashicons-airplane', 
                'menu_position' => 5,
                'has_archive'	=> false,
                'rewrite' => array( 'slug' => 'tours'),
                'supports' => array('title', 'editor', 'revisions'), 
                'show_ui'  =>   true,
            )
        );    
    }

    function custom_taxonomy() {
        $labels = array(
            'name'                       => _x( 'Types', 'Taxonomy General Name', 'text_domain' ),
            'singular_name'              => _x( 'Type', 'Taxonomy Singular Name', 'text_domain' ),
            'menu_name'                  => __( 'Tour Types', 'text_domain' ),
            'all_items'                  => __( 'All Types', 'text_domain' ),
            'parent_item'                => __( 'Parent Item', 'text_domain' ),
            'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
            'new_item_name'              => __( 'New Type', 'text_domain' ),
            'add_new_item'               => __( 'Add New Type', 'text_domain' ),
            'edit_item'                  => __( 'Edit Type', 'text_domain' ),
            'update_item'                => __( 'Update Type', 'text_domain' ),
            'view_item'                  => __( 'View Types', 'text_domain' ),
            'add_or_remove_items'        => __( 'Add or remove Types', 'text_domain' ),
            'search_items'               => __( 'Search Types', 'text_domain' ),
            'not_found'                  => __( 'Not Found', 'text_domain' ),
            'no_terms'                   => __( 'No Types', 'text_domain' ),
            'items_list'                 => __( 'Types list', 'text_domain' ),
            'items_list_navigation'      => __( 'Types list navigation', 'text_domain' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true, 
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );
        register_taxonomy( 'tour-tags', array( 'tours' ), $args );
    }

    function add_other_taxonomies() {
        register_taxonomy_for_object_type( 'post_tag', 'tours' );
    }
}


if(class_exists('TrSmartourPlugin')) {
    $trSmartourPlugin = new TrSmartourPlugin(); 
}

//Activation
register_activation_hook(__FILE__, array($trSmartourPlugin, 'activate'));

//Deactivation
register_deactivation_hook(__FILE__, array($trSmartourPlugin, 'deactivate'));

function tours_add_meta_box() {        
    $screens = array( 'tours' );
    
    foreach ( $screens as $screen ) {
    
        add_meta_box(
            'tours_sectionid',
            __( 'Tour Details', 'tours_textdomain' ),
            'tour_meta_box_callback',
            $screen
        );
    }
}

// Generate fields
function tour_meta_box_callback($post) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'tour_save_meta_box_data', 'tour_meta_box_nonce' );
    
    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, '_tour_code_value_key', true );
    $value2 = get_post_meta( $post->ID, '_tour_depdate_value_key', true );
    
    echo '<label for="tour_code_field">';
    _e( 'Tour Code: ', 'tour_textdomain' );
    echo '</label> ';    
    echo '<br />';    
    echo '<input type="text" id="tour_code_field" name="tour_code_field" value="' . esc_attr( $value ) . '" size="25" />';
    echo '<br />';
    echo '<p>';
    _e( 'Only uppercase-alphanumeric strings (such as THXA, POR22, or CRK).', 'tour_textdomain' );
    echo '</p>';
    echo '<br />';    
    echo '<label for="tour_departuredate_field">';
    _e( 'Tour Departure Date: ', 'tour_departuredate_textdomain' );
    echo '</label> ';
    echo '<br />';    
    echo '<input type="date" id="tour_departuredate_field" name="tour_departuredate_field" value="' . esc_attr( $value2 ) . '" size="25" />';
    echo '<br />';    
}   


// Check field inputs before saving
function tour_save_meta_box_data( $post_id ) {

    if ( ! isset( $_POST['tour_meta_box_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['tour_meta_box_nonce'], 'tour_save_meta_box_data' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( ! isset( $_POST['tour_code_field'] ) ) {
        return;
    }
    
    if ( ! isset( $_POST['tour_departuredate_field'] ) ) {
        return;
    }

    $tour_code_data = sanitize_text_field( $_POST['tour_code_field'] );
    $tour_depdate_data = sanitize_text_field( $_POST['tour_departuredate_field'] );


    // field left emply validation
    // if(!isset($_POST['tour_code_field']) || empty($_POST['tour_code_field'])){
    //     //field left empty so we add a notice
    //     $notice = get_option('tour_code_notice');
    //     $notice[$post_id] = "You have left the Tour Code field empty";
    //     update_option('tour_code_notice',$notice);    
    // } 

    $fieldCheckReturn = false; 
    
    if(preg_match('/[a-z]/', $_POST['tour_code_field']) || preg_match('/[\'^�$%&*()}{@#~?><>,|=_+�-]/', $_POST['tour_code_field'])) {
        // field input has special or lower case chars        
        $notice = get_option('tour_code_notice');
        $notice[$post_id] = "Input only uppercase-alphanumeric strings to Tour Code field (such as THXA, POR22, or CRK).";
        update_option('tour_code_notice',$notice);            
        $fieldCheckReturn = true;
    }    

    $todaysDate = strtotime(date('Y-m-d H:i:s'));
    $inputDate = strtotime($_POST['tour_departuredate_field']);
    if($inputDate < $todaysDate) {
        $notice2 = get_option('tour_departuredate_notice');
        $notice2[$post_id] = " Departure Date has to be in the future";
        update_option('tour_departuredate_notice',$notice2);    
        $fieldCheckReturn = true;        
    }

    if($fieldCheckReturn === true) {
        return;
    }
    

    update_post_meta( $post_id, '_tour_code_value_key', $tour_code_data );
    update_post_meta( $post_id, '_tour_depdate_value_key', $tour_depdate_data );
}
add_action( 'save_post', 'tour_save_meta_box_data' );

/*  admin notice */
function my_admin_notice_tourcode(){
    //print the message
    global $post;
    $notice_tourCode = get_option('tour_code_notice');
    if (empty($notice_tourCode)) return '';
    foreach($notice_tourCode as $pid => $m){
        if ($post->ID == $pid ){
            echo '<div id="message" class="error"><p>'.$m.'</p></div>';
            //make sure to remove notice after its displayed so its only displayed when needed.
            unset($notice_tourCode[$pid]);
            update_option('tour_code_notice',$notice_tourCode);
            break;
        }
    }
}

function my_admin_notice_depdate() {
    global $post;
    $notice_DepDate = get_option('tour_departuredate_notice');
    if (empty($notice_DepDate)) return '';
    foreach($notice_DepDate as $pid => $m){
        if ($post->ID == $pid ){
            echo '<div id="message" class="error"><p>'.$m.'</p></div>';
            //make sure to remove notice after its displayed so its only displayed when needed.
            unset($notice_DepDate[$pid]);
            update_option('tour_departuredate_notice',$notice_DepDate); 
            break;
        }
    }
}


//hooks
add_action('admin_notices', 'my_admin_notice_tourcode',0);
add_action('admin_notices', 'my_admin_notice_depdate',0);