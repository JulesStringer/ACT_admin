<?php
// includes/list_config.php
function get_act_admin_lists() {
    //$root = dirname(dirname(dirname(dirname(__FILE__))));
    $jobs = ABSPATH. '../jobs/';
    $lists = array(
        'recipients' => array(
            'title' => 'Recipients List',
            'role' => 'manage_options', // Admin role
            'list' => $jobs . 'recipients.csv',
            'list_format' => 'csv'
        ),
        'wwlist' => array(
            'title' => 'WW List',
            'role' => 'ww_admin', // Custom role
            'list' => $jobs . 'WW_Map.csv',
            'list_format' => 'csv'
        ),
        'cclist' => array(
            'title' => 'CC List',
            'role' => 'cc_admin', // Custom role
            'list' => $jobs . 'CC_Map.csv',
            'list_format' => 'csv'
        ),
    );

    $user = wp_get_current_user(); // Get the current user

    $filtered_lists = array();
    foreach ($lists as $list_id => $list_data) {
        if ( user_can( $user, $list_data['role'] ) ) { // Check user capability
            $filtered_lists[$list_id] = $list_data;
        }
    }

    return $filtered_lists;
}

function get_act_admin_list_by_id($list_id) {
    $lists = get_act_admin_lists(); // Now uses the filtered list.
    return isset($lists[$list_id]) ? $lists[$list_id] : null;
}

function check_list_access($list_id) {
    $list_data = get_act_admin_list_by_id($list_id);

    if (!$list_data) {
        return false; // List not found
    }

    if (!is_user_logged_in()) {
       return false; // Not logged in
    }

    $user = wp_get_current_user();
    if (!user_can($user, $list_data['role'])) {
        return false; // Insufficient permissions
    }

    return true; // Access granted
}

?>