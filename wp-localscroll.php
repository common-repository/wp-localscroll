<?php
/**
 * Plugin Name: WP LocalScroll
 * Plugin URI: http://wordpress.org/plugins/wp-localscroll/
 * Description: This plugin will animate a regular anchor navigation with a smooth scrolling effect. Each time a link is clicked, the whole screen will gradually scroll to the targeted element, instead of "jumping" as it'd normally do. jQuery.ScrollTo is used for the animation.
 * Version: 1.1
 * Author: Rabbett Designs
 * Author URI: http://www.rabbett.co.uk
 * Text Domain: wp-localscroll
 * Domain Path: /lang
*/

/*
    Copyright 2014  Rabbett Designs  (email : info@rabbettdesigns.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Check if settings page already exists
if (!class_exists( 'rd_plugin_settings' )) :
  include_once( plugin_dir_path(__FILE__) . '/inc/class-rd-plugin-settings.php' );
endif;

// Enqueues required scripts
add_action( 'wp_enqueue_scripts', 'wp_localscroll_enqueue_scripts' );
// Prints custom dynamic script
add_action( 'wp_footer', 'wp_localscroll_dynamic_script' );
// Register default settings
register_activation_hook( __FILE__, 'wp_localscroll_settings_defaults' );
// Add plugin action links
add_action( 'admin_init', call_user_func(array($rd_settings, 'plugin_action_links'), __FILE__) );
// Initializes the options by registering the Sections, Fields, and Settings.
add_action( 'admin_init', 'wp_localscroll_settings' );
// Add some metaboxes to the page
add_action( 'add_meta_boxes', 'wp_localscroll_settings_add_meta_box' );

// This function is registered with the 'wp_enqueue_scripts' hook.
function wp_localscroll_enqueue_scripts() {
  // jQuery - ScrollTo
  wp_register_script('scrollto', plugins_url('js/jquery.scrollTo.min.js', __FILE__), array( 'jquery' ), '1.4.3.1', true);
  // jQuery - LocalScroll
  wp_register_script('localscroll', plugins_url('js/jquery.localscroll.min.js', __FILE__), array( 'jquery', 'scrollto' ), '1.2.7', true);

  // Enqueue script if enabled for location
  if (wp_localscroll_enabled())
    wp_enqueue_script( 'localscroll' );
} // end wp_localscroll_enqueue_scripts

// This function is registered with the 'wp_footer' hook
function wp_localscroll_dynamic_script() {
  // Bail out if script not enabled for location
  if (!wp_localscroll_enabled())
    return;

  echo "<script>
    // When the document is loaded...
    jQuery(document).ready(function($) {
      // Scroll initially if there's a hash (#something) in the url 
      $.localScroll.hash( {
        target: 'body', // Could be a selector or a jQuery object too.
        duration:1500
      });

      /**
       * Scroll the whole document
       * NOTE: I use $.localScroll instead of $('#navigation').localScroll().
      */
      $.localScroll({
        target: 'body', // Could be a selector or a jQuery object too.
        duration:1000,
        hash:true
      });
    });
    </script>";
} // end wp_localscroll_dynamic_script

// This function is registered with the 'register_activation_hook' hook
function wp_localscroll_settings_defaults() {
    $defaults = array(
      'home' => '1',
      'post' => '1',
      'page' => '1',
      'archive' => '1'
    );
    // First, we read the options collection
    $options = get_option('wp_localscroll_settings');
    // If the localscroll options don't exist, create them.
    if( false == $options )
      update_option('wp_localscroll_settings', $defaults);
} // end wp_localscroll_settings_defaults

// This function is registered with the 'admin_init' hook.
function wp_localscroll_settings() {
  // Load language files
  load_plugin_textdomain( 'wp-localscroll', false, dirname(plugin_basename(__FILE__)) . '/lang' );

  // First, we read the options collection
  $options = get_option('wp_localscroll_settings');

  add_settings_section(
    'wp_localscroll_settings_section',  // ID used to identify this section and with which to register options
    __('Activation', 'wp-localscroll'),                       // Title to be displayed on the administration page
    'wp_localscroll_settings_callback', // Callback used to render the description of the section
    'wp_localscroll_settings'           // Page on which to add this section of options
  );

  add_settings_field(  
    'enable_home',               // ID used to identify the field throughout the theme
    sprintf('<label for="home">%s</label>', __('Enable on Home Page', 'wp-localscroll')),            // The label to the left of the option interface element
    'wp_localscroll_settings_enable_callback',  // The name of the function responsible for rendering the option interface
    'wp_localscroll_settings',          // The page on which this option will be displayed
    'wp_localscroll_settings_section',  // The name of the section to which this field belongs
    array(                              // The array of arguments to pass to the callback.
      'options' => $options,
      'slug' => 'home',
    )
  );

  // Next, we retrieve registered post types
  $post_objs = get_post_types( '', 'objects' );
  $pt = array_keys( $post_objs );
  $rempost = array( 'revision', 'nav_menu_item' ); // Remove post types
  $pt = array_diff( $pt, $rempost );
  $post_types = array();
  foreach ( $pt as $p ) :
    if ( !empty( $post_objs[$p]->label ) ) :
      $post_types[$p] = $post_objs[$p]->label;
    else :
      $post_types[$p] = $p;
    endif;
  endforeach;

  // And finally, we cycle through the available post types and mark active post types
  $html = '';
  foreach ($post_types as $slug => $name) :
    add_settings_field(  
      'enable_'.$slug.'_type',               // ID used to identify the field throughout the theme
      sprintf('<label for="%s">%s</label>', $slug, sprintf(__('Enable on %s', 'wp-localscroll'), $name)),            // The label to the left of the option interface element
      'wp_localscroll_settings_enable_callback',  // The name of the function responsible for rendering the option interface
      'wp_localscroll_settings',          // The page on which this option will be displayed
      'wp_localscroll_settings_section',  // The name of the section to which this field belongs
      array(                              // The array of arguments to pass to the callback.
        'options' => $options,
        'slug' => $slug,
      )
    );
  endforeach;

  add_settings_field(  
    'enable_archive',               // ID used to identify the field throughout the theme
    sprintf('<label for="archive">%s</label>', __('Enable on Archive Pages (tags, categories, etc.)', 'wp-localscroll')),            // The label to the left of the option interface element
    'wp_localscroll_settings_enable_callback',  // The name of the function responsible for rendering the option interface
    'wp_localscroll_settings',          // The page on which this option will be displayed
    'wp_localscroll_settings_section',  // The name of the section to which this field belongs
    array(                              // The array of arguments to pass to the callback.
      'options' => $options,
      'slug' => 'archive',

    )
  );

  add_settings_field(  
    'delete_wp_localscroll_settings',               // ID used to identify the field throughout the theme
    sprintf('<label for="wp_localscroll_settings">%s</label>', __('WP LocalScroll', 'wp-localscroll')),            // The label to the left of the option interface element
    'rd_delete_settings_field_callback',  // The name of the function responsible for rendering the option interface
    'rabbett_designs_delete_settings',          // The page on which this option will be displayed
    'rd_delete_settings_section',  // The name of the section to which this field belongs
    array(                              // The array of arguments to pass to the callback.
      'plugin' => 'wp_localscroll_settings'
    )
  );

  // Finally, we register the fields with WordPress
  register_setting(
    'wp_localscroll_settings',
    'wp_localscroll_settings'
  );
} // end wp_localscroll_settings

// This function is registered with the 'add_settings_section' hook.
function wp_localscroll_settings_callback() {
  printf('<p>%s</p>', __('Select where you would like to enable jQuery LocalScroll.', 'wp-localscroll'));
} // end wp_localscroll_settings_callback

// This function is registered with the 'add_settings_field' hook.
function wp_localscroll_settings_enable_callback($args) {
  $options = $args['options'];
  $slug = esc_attr( $args['slug'] );

  $html .= '<input type="checkbox" id="'.$slug.'" name="wp_localscroll_settings['.$slug.']" value="1" ' . checked(1, isset($options[$slug]), false) . '/>';

  // print the final result
  echo $html;
} // end wp_localscroll_settings_enable_callback

// This function is registered with the 'add_meta_boxes' hook.
function wp_localscroll_settings_add_meta_box() {
  add_meta_box(
    'wp_localscroll_settings_metabox', //Meta box ID
    __('jQuery LocalScroll Settings', 'wp-localscroll'), //Meta box Title
    'wp_localscroll_settings_metabox_callback', //Callback defining the plugin's innards
    'rabbett-designs', // Screen to which to add the meta box
    'normal' // Context
  );
} // end wp_localscroll_settings_add_meta_box


// This function is registered with the 'add_meta_box' hook
function wp_localscroll_settings_metabox_callback() {
  echo '<form method="post" action="options.php">';
  settings_fields( 'wp_localscroll_settings' );
  do_settings_sections( 'wp_localscroll_settings' );
  echo '<p>'.get_submit_button( __('Save WP LocalScroll Settings', 'wp-localscroll'), 'primary', 'submit', false).'</p>';
  echo '</form>';
} // wp_localscroll_settings_metabox_callback

// This function checks if plugin is enabled
function wp_localscroll_enabled() {
  if ( !is_feed() && !is_admin() ) :
    //Determine option to check
    if ( is_home() ) :
      $opt = 'home';
    elseif ( is_archive() || is_search() ) :
      $opt = 'archive';
    elseif ( is_singular() ) :
      $opt = get_post_type();
    else :
      $opt = '';
    endif;

    //Check option
    $options = get_option('wp_localscroll_settings');
    if ( !empty($opt) && (false !== $options && is_array($options))) :
      if (array_key_exists($opt, $options)) :
        $enable = true;
      endif;
    endif;
  endif;

  return (isset($enable) ? $enable : false);
} // end wp_localscroll_enabled