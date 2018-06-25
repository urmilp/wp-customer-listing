<?php
/*
Plugin Name: WP Customer Listing Form
Plugin URI: https://github.com/urmilp/
Description: How to create custom admin list-tables from database using WP_List_Table class and front with shortocde
Version:     1.0
Author:      Urmil Patel
Author URI:  https://github.com/urmilp/
License:     
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if( !defined( 'WURGPWC_VERSION_DIR' ) ) {
    define( 'WURGPWC_VERSION_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'WURGPWC_VERSION_URL' ) ) {
    define( 'WURGPWC_VERSION_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

/**
 * PART 1. Defining Custom Database Table
 * ============================================================================
 *
 * In this part you are going to define custom database table,
 * create it, update, and fill with some dummy data
 *
 * http://codex.wordpress.org/Creating_Tables_with_Plugins
 *
 * In case your are developing and want to check plugin use:
 *
 * DROP TABLE IF EXISTS wp_datalisting;
 * DELETE FROM wp_options WHERE option_name = 'mt_management_install_data';
 *
 * to drop table and option
 */

/**
 * $mt_management_db_version - holds current database version
 * and used on plugin update to sync database tables
 */
global $mt_management_db_version;
$mt_management_db_version = '1.1'; // version changed from 1.0 to 1.1

/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */
function mt_management_install()
{
    global $wpdb;
    global $mt_management_db_version;

    $table_name = $wpdb->prefix . 'datalisting'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name tinytext NOT NULL,
      email VARCHAR(100) NOT NULL,
      age int(11) NULL,
      PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('mt_management_db_version', $mt_management_db_version);

    /**
     * [OPTIONAL] Example of updating to 1.1 version
     *
     * If you develop new version of plugin
     * just increment $mt_management_db_version variable
     * and add following block of code
     *
     * must be repeated for each new version
     * in version 1.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('mt_management_db_version');
    if ($installed_ver != $mt_management_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name tinytext NOT NULL,
          email VARCHAR(200) NOT NULL,
          age int(11) NULL,
          PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // notice that we are updating option, rather than adding it
        update_option('mt_management_db_version', $mt_management_db_version);
    }
}

register_activation_hook(__FILE__, 'mt_management_install');

/**
 * register_activation_hook implementation
 *
 * [OPTIONAL]
 * additional implementation of register_activation_hook
 * to insert some dummy data
 */
function mt_management_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'datalisting'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'name' => 'Joy',
        'email' => 'joy@example.com',
        'age' => 20
    ));
    $wpdb->insert($table_name, array(
        'name' => 'Jack',
        'email' => 'jack@example.com',
        'age' => 22
    ));
}

register_activation_hook(__FILE__, 'mt_management_install_data');

/**
 * Trick to update plugin database, see docs
 */
function mt_management_update_db_check()
{
    global $mt_management_db_version;
    if (get_site_option('mt_management_db_version') != $mt_management_db_version) {
        mt_management_install();
    }
}

add_action('plugins_loaded', 'mt_management_update_db_check');


/**
 * Load the plugin after the main plugin is loaded.
 * 
 * @package Woo Product Slider and Carousel with category
 * @since 1.0.0
 */
function wurgpwc_load_plugin() {

		/**
		 * Load Text Domain
		 * This gets the plugin ready for translation
		 * 
		 * @package 
		 * @since 1.0.0
		 */
		function wurgpwc_load_textdomain() {
			load_plugin_textdomain( 'custom-languages', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
		}
		// Action to load plugin text domain
		add_action('plugins_loaded', 'wurgpwc_load_textdomain');

		/**
		 * Function add some script and style
		 * 
		 * @package Woo Product Slider and Carousel with category
		 * @since 1.2.5
		 */
		function wurgpwc_style_css() {
			
			// Custom CSS
			wp_enqueue_style( 'wurgpwc_public_style',  plugin_dir_url( __FILE__ ) . 'assets/css/custom.css', array(), WCPSCWC_VERSION);		
			
			// Public script
			wp_register_script( 'wcpscwc-custom-jquery', WCPSCWC_VERSION_URL.'assets/js/custom.js', array('jquery'), WCPSCWC_VERSION, true );			
		}

		// Action to add some style and script
		add_action( 'wp_enqueue_scripts', 'wurgpwc_style_css' );

}

// Action to load plugin after the main plugin is loaded
add_action('plugins_loaded', 'wurgpwc_load_plugin', 15);



/**
 * PART 2. Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wordpress.org/extend/plugins/custom-list-table-example/
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Custom_Table_Example_List_Table class that will display our custom table
 * records in nice table
 */
class Custom_Table_Example_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'customer',
            'plural' => 'Customers',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_age($item)
    {
        return '<em>' . $item['age'] . '</em>';
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_name($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &customer=2
        $actions = array(
            'edit' => sprintf('<a href="?page=customers_form&id=%s">%s</a>', $item['id'], __('Edit', 'mt_management')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'mt_management')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'name' => __('Name', 'mt_management'),
            'email' => __('E-Mail', 'mt_management'),
            'age' => __('Age', 'mt_management'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
            'email' => array('email', false),
            'age' => array('age', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datalisting'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datalisting'; // do not forget about tables prefix

        $per_page = 5; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */

/**
 * admin_menu hook implementation, will add pages to list customers and to add new one
 */
function mt_management_admin_menu()
{
    add_menu_page(__('Customers', 'mt_management'), __('Customers', 'mt_management'), 'activate_plugins', 'customers', 'mt_management_customers_page_handler');
    add_submenu_page('customers', __('Customers', 'mt_management'), __('Customers', 'mt_management'), 'activate_plugins', 'customers', 'mt_management_customers_page_handler');
    // add new will be described in next part
    add_submenu_page('customers', __('Add new', 'mt_management'), __('Add new', 'mt_management'), 'activate_plugins', 'customers_form', 'mt_management_customers_form_page_handler');
	
	
}

add_action('admin_menu', 'mt_management_admin_menu');

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function mt_management_customers_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'mt_management'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Customers', 'mt_management')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=customers_form');?>"><?php _e('Add new', 'mt_management')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="customers-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/seledatalistingd
 */

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function mt_management_customers_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'datalisting'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => '',
        'email' => '',
        'age' => null,
    );

    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = mt_management_validate_customer($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'mt_management');
                } else {
                    $notice = __('There was an error while saving item', 'mt_management');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'mt_management');
                } else {
                    $notice = __('There was an error while updating item', 'mt_management');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'mt_management');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('customers_form_meta_box', 'Customer Data', 'mt_management_customers_form_meta_box_handler', 'customer', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Customer', 'mt_management')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=customers');?>"><?php _e('back to list', 'mt_management')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('customer', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'mt_management')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function mt_management_customers_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Name', 'mt_management')?></label>
        </th>
        <td>
            <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name'])?>"
                   size="50" class="code" placeholder="<?php _e('Your name', 'mt_management')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="email"><?php _e('E-Mail', 'mt_management')?></label>
        </th>
        <td>
            <input id="email" name="email" type="email" style="width: 95%" value="<?php echo esc_attr($item['email'])?>"
                   size="50" class="code" placeholder="<?php _e('Your E-Mail', 'mt_management')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="age"><?php _e('Age', 'mt_management')?></label>
        </th>
        <td>
            <input id="age" name="age" type="number" style="width: 95%" value="<?php echo esc_attr($item['age'])?>"
                   size="50" class="code" placeholder="<?php _e('Your age', 'mt_management')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function mt_management_validate_customer($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'mt_management');
    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format', 'mt_management');
    if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format', 'mt_management');
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == mt_management
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function mt_management_languages()
{
    load_plugin_textdomain('mt_management', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'mt_management_languages');

// shortcode form
require_once( WURGPWC_VERSION_DIR . '/includes/wcpscwc-customer-shortcode.php' );
