<?php
$path = dirname(__FILE__);
//$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
//require_once($root . '/wp-admin/includes/file.php'); // For wp_handle_upload()
require_once($path . '/security.php');
// Handle AJAX requests for data.  This file should be called from your JavaScript.
// It assumes that the list ID is passed in the request as 'list_id'.  It also assumes an 'action' is sent, such as 'load' or 'save'.

if (isset($_REQUEST['list_id']) && isset($_REQUEST['action'])) {
    $list_id = $_REQUEST['list_id'];
    $action = $_REQUEST['action'];

    if (check_list_access($list_id)) {  // Security check!
        $list_data = get_act_admin_list_by_id($list_id);
        $file_path = $list_data['list'];

        switch ($action) {
            case 'load':
                if (file_exists($file_path)) {
                    $file_contents = file_get_contents($file_path);
                    if ($file_contents !== false) {
                        // Sanitize the file contents if necessary (e.g., for CSV).
                        echo json_encode(array('status' => 'success', 'data' => $file_contents));
                    } else {
                        echo json_encode(array('status' => 'error', 'message' => 'Error reading file.'.$file_path));
                    }
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'File not found.'.$file_path));
                }
                break;

            case 'save':

                if (isset($_POST['data'])) { // Assuming data is sent in POST.
                    $data = $_POST['data'];
                    // Sanitize $data thoroughly!  This is crucial to prevent security vulnerabilities.
                    $result = file_put_contents($file_path, $data);
                    if ($result !== false) {
                        echo json_encode(array('status' => 'success', 'message' => 'File saved.'));
                    } else {
                        echo json_encode(array('status' => 'error', 'message' => 'Error saving file.'));
                    }
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'No data provided.'));
                }
                break;
            default:
                echo json_encode(array('status' => 'error', 'message' => 'Invalid action.'));
        }
    } else {
      echo json_encode(array('status' => 'error', 'message' => 'You do not have permission to perform this action.'));
    }
    exit; // Important: Stop further execution after handling the AJAX request.
}

?>