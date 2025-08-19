<?php
/*
Plugin Name: ACT Admin
Plugin URI:  https://sites.stringerhj.co.uk/ACT/WP_plugins/ACT_admin/html/ACT_admin.html
// (Optional: URL to your plugin's website)
Description: ACT admin provides facilities for 
Version: 1.1.0 // Your plugin's version
Author: Julian Stringer
Author URI: // (Optional: URL to your website)
// ... other plugin details
*/

require_once plugin_dir_path( __FILE__ ) . 'includes/ajax_handlers.php';

function act_enqueue_frontend_scripts() { // New function
    wp_enqueue_script( 'jquery' ); // Enqueue jQuery for the front-end
}
add_action( 'wp_enqueue_scripts', 'act_enqueue_frontend_scripts' ); // Hook to enqueue on the front end

function act_admin_enqueue_scripts_and_styles() {
    // For admin area:
    wp_enqueue_style( 'act-admin-styles', plugins_url( 'css/list_manager.css', __FILE__ ) );
    wp_enqueue_script( 'act-admin-tooltips',    plugins_url( 'js/tooltips.js', __FILE__),     array('jquery'), 1.0, true);
    wp_enqueue_script( 'act-admin-tableeditor', plugins_url( 'js/tableeditor.js', __FILE__ ), array('jquery','act-admin-tooltips'), '1.0', true );
    wp_enqueue_script( 'act-admin-namevalueeditor', plugins_url( 'js/namevalue.js', __FILE__), array('jquery', 'act-admin-tooltips'), '1.0', true);
    wp_enqueue_script( 'act-admin-list-manager', plugins_url( 'js/list_manager.js', __FILE__));
    wp_enqueue_script( 'act-admin-common', plugins_url( 'js/act_admin_common.js', __FILE__));
    wp_localize_script( 'act-admin-list-manager', 'my_ajax_object', 
        array('ajaxurl' => admin_url( 'admin-ajax.php' ))
    );
    if ( isset($_GET['page'])){
        $page = $_GET['page'];
        if ( strpos($page, 'act-admin-') === 0 ) {
            $listid = substr($page, strlen('act-admin-'));
            if ( $listid != 'admin'){
                wp_enqueue_script( 'act-admin-dynamic', plugins_url( 'js/' . $listid . '_admin.js', __FILE__ ), array('jquery','act-admin-list-manager',
                        'act-admin-common','act-admin-tableeditor','act-admin-namevalueeditor'), '1.0', true );
            }
        }
    }
}
add_action( 'admin_enqueue_scripts', 'act_admin_enqueue_scripts_and_styles' );

add_action( 'admin_menu', 'act_admin_menu' );
function act_admin_menu() {
    $lists = get_act_admin_lists(); // Get the filtered list based on user roles

    if ( !empty( $lists ) ) { // Check if there are any lists the user can access
        add_menu_page( 'ACT Admin', 'ACT Admin', 'read', 'act-admin', 'act_admin_page', 'dashicons-list-view' ); // Top-level menu

        foreach ($lists as $list_id => $list_data) {
            add_submenu_page( 
                'act-admin', 
                $list_data['title'], 
                $list_data['title'], 
                $list_data['role'], 
                'act-admin-' . $list_id, 
                'act_admin_list_page' ); // Use the role for capability check on submenu
        }
    }
}
function act_admin_page() {
    // Top-level page content (can be empty or a welcome message)
    echo '<h2>ACT Admin</h2>';

    // Display links to the sub-menu pages (optional)
    $lists = get_act_admin_lists();
    echo '<ul>';
    foreach ($lists as $list_id => $list_data) {
        echo '<li><a href="' . admin_url( 'admin.php?page=act-admin-' . $list_id ) . '">' . $list_data['title'] . '</a></li>';
    }
    echo '</ul>';
}
function act_admin_list_page() {
    $current_screen = get_current_screen();
    $screen_id = $current_screen->id;
    if ( strpos($screen_id,'act-admin_page_act-admin-' ) !== 0){
        return;
    } 
    $list_id = str_replace('act-admin_page_act-admin-', '', $screen_id);
    $list_data = get_act_admin_list_by_id($list_id);
    if (!$list_data) {
        echo '<h2>Screen id{'. $screen_id, '}</h2>';
        echo '<h2>List not found{'. $list_id.'}</h2>';
        return;
    }

    if (!current_user_can($list_data['role'])) {
        echo '<h2>You do not have permission to access this page.</h2>';
        return;
    }

    // Include the HTML file.  Use include or require for security reasons.
    $file_path = plugin_dir_path( __FILE__ ) . 'html/template_admin.html'; // Use a single template
    if ( $list_id === 'wwareas' || $list_id === 'ccareas'){
        $file_path = plugin_dir_path( __FILE__ ) .'html/area_admin.html';
    }
    if ( file_exists($file_path) ) {
        $css_url = plugins_url( 'css/list_manager.css', __FILE__ );
        $js_tableeditor_url = plugins_url( 'js/tableeditor.js', __FILE__ );
        $js_dynamic_url = plugins_url( 'js/' . str_replace('act-admin-', '', $_GET['page']) . '_admin.js', __FILE__ ); // Dynamic JS

        ob_start();
        include $file_path;
        $html_content = ob_get_clean();

        $html_content = str_replace('{list_manager.css}', $css_url , $html_content);

        // Replace placeholders (Title, Format, Help Text)
        $html_content = str_replace('{List Title}', esc_html($list_data['title']), $html_content);

        $html_content = str_replace('{list-id}', $list_id, $html_content);
        // Help Text (Example - adapt as needed)
        $help_text = isset($list_data['help_text']) ? $list_data['help_text'] : ''; // Get help text, or default to empty
        $html_content = str_replace('{Help Text}', $help_text, $html_content);

        echo $html_content;

    } else {
        echo '<h2>HTML file not found'. $template_file_path.'</h2>';
        error_log("HTML file not found: " . $template_file_path); // Log the error!
        return;
    }
}
?>
