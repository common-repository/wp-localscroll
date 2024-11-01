<?php
class RD_Plugin_Settings {
  var $hook;
  var $title;
  var $menu;
  var $permissions;
  var $slug;
  var $page;

  /**
   * Constructor class for the Admin Options Metabox
   *@param $hook - (string) parent page hook
   *@param $title - (string) the browser window title of the page
   *@param $menu - (string)  the page title as it appears in the menuk
   *@param $permissions - (string) the capability a user requires to see the page
   *@param $slug - (string) a slug identifier for this page
   *@param $body_content_cb - (callback)  (optional) a callback that prints to the page, above the metaboxes
  */
  function __construct($hook, $title, $menu, $permissions, $slug, $body_content_cb='__return_true'){
    $this->hook = $hook;
    $this->title = $title;
    $this->menu = $menu;
    $this->permissions = $permissions;
    $this->slug = $slug;
    $this->body_content_cb = method_exists( $this, $body_content_cb ) ? array($this,$body_content_cb) : $body_content_cb;

    /* Add the page */
    add_action('admin_menu', array($this,'add_page'));
  }

  /**
   * Adds the plugin action links.
   * Adds callbacks to the plugin_action_links_* hook
   *@param $plugin_file - (string) the plugin / module filename
  */
  function plugin_action_links($plugin_file){
    /* Add plugin action links for each plugin / module */
    add_filter( 'plugin_action_links_' . plugin_basename( $plugin_file ), array($this, 'settings_link'), 10, 2);
  }

  /**
   * Adds the custom page.
   * Adds callbacks to the load-* and admin_footer-* hooks
  */
  function add_page(){
    /* Add the page */
    $this->page = add_submenu_page($this->hook,$this->title, $this->menu, $this->permissions,$this->slug,  array($this,'render_page'),10);

    /* Add callbacks for this screen only */
    add_action('load-'.$this->page,  array($this,'page_actions'),9);
    add_action('admin_footer-'.$this->page,array($this,'footer_scripts'));
  }

  /**
   * Adds content above metaboxes.
   * Adds custom rd_settings_body hook
  */
  function body_content_cb() {
    // initialize hook
    do_action('rd_settings_body');
  }

  /**
   * Prints the jQuery script to initiliase the metaboxes
   * Called on admin_footer-*
  */
  function footer_scripts() {
    echo '<script>postboxes.add_postbox_toggles(pagenow);</script>';
  }

  /*
   * Actions to be taken prior to page loading. This is after headers have been set.
   * call on load-$hook
   * This calls the add_meta_boxes hooks, adds screen options and enqueues the postbox.js script.   
  */
  function page_actions(){
    do_action('add_meta_boxes_'.$this->page, null);
    do_action('add_meta_boxes', $this->page, null);

    /* User can choose between 1 or 2 columns (default 2) */
    add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );

    /* Enqueue WordPress' script for handling the metaboxes */
    wp_enqueue_script('postbox'); 
  }

  /**
   * Renders the page
  */
  function render_page() {
    if (!current_user_can('manage_options')) :
      wp_die( __('You do not have sufficient permissions to access this page.') );
    endif; ?>

    <div class="wrap">
      <h2><?php echo esc_html($this->title);?></h2>
      <?php settings_errors(); ?>
      <form method="post" action="options.php">
        <?php
          /* Used to save closed metaboxes and their order */
          wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
          wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        ?>
      </form>
      <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>"> 
          <div id="post-body-content">
            <?php call_user_func($this->body_content_cb); ?>
          </div>    

          <div id="postbox-container-1" class="postbox-container">
            <?php do_meta_boxes('rabbett-designs','side',null); ?>
          </div>    

          <div id="postbox-container-2" class="postbox-container">
            <?php do_meta_boxes('rabbett-designs','normal',null);  ?>
            <?php do_meta_boxes('rabbett-designs','advanced',null); ?>
          </div>
        </div> <!-- #post-body -->
      </div> <!-- #poststuff -->
    </div><!-- .wrap -->
<?php
  }

  /**
   * Prints the settings link on plugins page
   * Called on plugin_action_links_*
  */
  function settings_link( $links ) {
    $settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'plugins.php?page=rabbett_designs' ), __( 'Settings', 'rd-settings' ));
    array_unshift($links, $settings_link);

    return $links;
  }
}

//Create a page
$rd_settings = new RD_Plugin_Settings( 'plugins.php', __( 'Rabbett Designs Plugin Settings', 'rd-settings' ), __( 'Rabbett Designs', 'rd-settings' ), 'manage_options','rabbett_designs', 'body_content_cb');

// Initializes the options by registering the Sections, Fields, and Settings.
add_action( 'admin_init', 'rd_delete_settings_init' );
// Add some metaboxes to the page
add_action('add_meta_boxes', 'rd_delete_settings_meta_boxes');

// This function is registered with the 'admin_init' hook.
function rd_delete_settings_init() {
  // Load language files
  load_plugin_textdomain( 'rd-settings', false, dirname(plugin_basename(__FILE__)) . '/lang' );

  add_settings_section(
    'rd_delete_settings_section',          // ID used to identify this section and with which to register options
    __( 'Active Plugins', 'rd-settings' ), // Title to be displayed on the administration page
    'rd_delete_settings_section_callback', // Callback used to render the description of the section
    'rabbett_designs_delete_settings'      // Page on which to add this section of options
  );

  // Finally, we register the fields with WordPress
  register_setting(
    'rd_delete_settings',           // A settings group name. This must match the group name in settings_fields()
    'rd_delete_settings',           // The name of an option to sanitize and save
    'rd_delete_settings_validation' // Callback used to sanitize the submitted option's
  );
  add_filter('pre_update_option_rd_delete_settings', '__return_false');
} // end rd_settings

// This function is registered with the 'add_settings_section' hook.
function rd_delete_settings_section_callback() {
  printf('<p>%s</p>', __( 'Select plugin data to delete.', 'rd-settings' ));
} // end rd_delete_settings_section_callback

// This function is registered with the 'add_settings_field' hook.
function rd_delete_settings_field_callback($args) {
  $plugin = esc_attr( $args['plugin'] );

  $html .= '<input type="checkbox" id="'.$plugin.'" name="rd_delete_settings['.$plugin.']" value="1" />';

  // print the final result
  echo $html;
} // end rd_delete_settings_field_callback

// This function is registered with the 'add_meta_boxes' hook.
function rd_delete_settings_meta_boxes() {
  add_meta_box(
    'rd_delete_settings_meta_box',                 //Meta box ID
    __( 'Delete Plugin Settings', 'rd-settings' ), //Meta box Title
    'rd_delete_settings_meta_box_callback',        //Callback defining the plugin's innards
    'rabbett-designs',                             // Screen to which to add the meta box
    'side'                                         // Context
  );
} // end rd_delete_settings_meta_boxes

// This function is registered with the 'add_meta_box' hook
function rd_delete_settings_meta_box_callback() {
  echo '<form method="post" action="options.php">';
  settings_fields( 'rd_delete_settings' );
  do_settings_sections( 'rabbett_designs_delete_settings' );
  echo '<p>' . get_submit_button( __( 'Clear Plugin Data', 'rd-settings' ), 'delete', 'delete', false) . '</p>';
  echo '</form>';
} // end rd_delete_settings_meta_box_callback

// This function is registered with the 'register_setting' hook
function rd_delete_settings_validation($input) {
  if (isset($_POST['delete'])) :
    foreach ($input as $key => $value) :
      delete_option($key);
    endforeach;
    if ( !count( get_settings_errors() ) ) :
      add_settings_error( 'rd_delete_settings', esc_attr( 'settings-cleared' ), __( 'Plugin Data Cleared.', 'rd-settings' ), 'updated' );
    endif;
  endif;
  
  return;
} // end rd_delete_settings_validation