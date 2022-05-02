<?php

/** 
 * Trigger this file on plugin uninstall 
 * 
 * @package trcustom-smartour 
*/

defined('WP_UNINSTALL_PLUGIN') or die('Error 403, you can\'t access this file.');

// Clear database stored data
$tours = get_posts( array('post_type' => 'tours', 'numberposts' => -1));

global $wpdb;
$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'tours'");
$wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)");
$wpdb->query("DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT id FROM wp_posts)");
$wpdb->query("DELETE FROM wp_term_taxonomy WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM wp_term_relationships)");
$wpdb->query("DELETE FROM wp_terms WHERE term_id NOT IN (SELECT term_id FROM wp_term_taxonomy)");
