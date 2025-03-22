<?php

require_once(dirname(__FILE__) . '/list_config.php');

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