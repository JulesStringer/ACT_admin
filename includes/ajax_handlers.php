<?php
require_once(ABSPATH . 'wp-admin/includes/file.php'); // For file uploads if needed.
if ( ! defined( 'LISTS_DIR' ) ){
    define( 'LISTS_DIR', '/home/customer/www/actionclimateteignbridge.org/jobs/');
}
function get_act_admin_lists() {
    /* Path of lists maintained by ACT_admin */
// TODO when plugin is registered add specific roles for each list and add role to maintainer of list.
    $lists = array(
        'recipients' => array(
            'title' => 'Recipients List',
            'role' => 'manage_options', // Admin role
            'list' => LISTS_DIR . 'recipients.csv',
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

function load_list_data($file_path) { // The new function
    if (file_exists($file_path)) {
        $file_contents = file_get_contents($file_path);
        if ($file_contents !== false) {
            // Sanitize if necessary (e.g., for CSV)
            return array('status' => 'success', 'data' => $file_contents); // Return the data
        } else {
            return array('status' => 'error', 'message' => 'Error reading file.'.$file_path); // Return the error
        }
    } else {
        return array('status' => 'error', 'message' => 'File not found.'.$file_path); // Return the error
    }
}

function get_act_admin_list_data() {
    if (isset($_REQUEST['list_id'])) {
        $list_id = $_REQUEST['list_id'];

        if (check_list_access($list_id)) {
            $list_data = get_act_admin_list_by_id($list_id);
            $file_path = $list_data['list'];

            $result = load_list_data($file_path); // Call the new function

            if ($result['status'] === 'success') {
                wp_send_json_success($result['data']); // Send success response
            } else {
                wp_send_json_error($result, 500); // Send error response
            }
        } else {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'), 403);
        }
    } else {
        wp_send_json_error(array('message' => 'Missing list_id.'), 400);
    }
    die();
}

function save_list_data($file_path, $data){
    $result = file_put_contents($file_path, $data);
    if ($result !== false) {
        return array('status' => 'success', 'message' => 'File saved.');
    } else {
        return array('status' => 'error', 'message' => 'Error saving file.');
    }
}

function save_act_admin_list_data(){
    //var_dump($_POST);
    //var_dump($_FILES);
    if ( isset($_POST['list_id']) && isset($_FILES['data']) && isset($_FILES['data']['tmp_name'])){
        $list_id = $_POST['list_id'];
        if (check_list_access($list_id)) {  // Security check!
            $list_data = get_act_admin_list_by_id($list_id);
            $file_path = $list_data['list'];
            $data = file_get_contents($_FILES['data']['tmp_name']);
            $result = save_list_data($file_path, $data);
            if ($result['status'] === 'success') {
                $response_data = array();
                $response_data['status'] = 'success';
                $response_data['message'] = $result['message'];
                $follow_on_script = isset($list_data['follow_on_script']) ? $list_data['follow_on_script'] : null;
                $response_data['follow_on_script'] = $follow_on_script;
                // 2. Check if the path is set and the file exists:
                if ($follow_on_script && file_exists($follow_on_script)) {
                    $response_date['found_follow_on_script'] = true;
                    // 3. Execute the follow-on script:
                    try {
                        $descriptorspec = array(
                            0 => array("pipe", "r"),  // stdin
                            1 => array("pipe", "w"),  // stdout
                            2 => array("pipe", "w")   // stderr
                        );
                        $process = proc_open("php " . escapeshellarg($follow_on_script), $descriptorspec, $pipes);
                        if (is_resource($process) && get_resource_type($process) === 'process') {
                            $stdout = '';
                            $stderr = '';
                            stream_set_blocking($pipes[1], 0); // Set stdout to non-blocking
                            stream_set_blocking($pipes[2], 0); // Set stderr to non-blocking
                        
                            $status = proc_get_status($process);
                            while ($status['running']) { // Check if process is still running
                                $r = array($pipes[1], $pipes[2]); // Array of streams to read from
                                $w = null;
                                $e = null;
                                $n = stream_select($r, $w, $e, 0); // Check if there's data to read (timeout 0 = non-blocking)
                        
                                if ($n > 0) { // If there's data
                                    if (in_array($pipes[1], $r)) {
                                        $stdout .= fread($pipes[1], 1024); // Read from stdout
                                    }
                                    if (in_array($pipes[2], $r)) {
                                        $stderr .= fread($pipes[2], 1024); // Read from stderr
                                    }
                                }
                                usleep(10000); // Small delay to prevent busy-waiting (10ms)
                                $status = proc_get_status($process); // Update process status
                            }
                            fclose($pipes[1]);
                            fclose($pipes[2]);
                            $response_data['final_sub_process_status'] = $status;
                            $return_value = proc_close($process); // Only after the process has finished
                            if ($return_value != 0){
                                error_log("proc_close() also returned non-zero value ($return_value).");
                                $response_data['return_value'] = $return_value;
                            }
                            if ($status['exitcode'] !== 0) {
                                error_log("Error executing follow-on script: " . $stderr); // Log the error
                                $response_data['status']= 'error';
                                $response_data['error_message'] = 'error_in_follow on';
                                $response_data['follow_on_script_error'] = $stderr;
                                $response_data['return_value'] = $return_value;
                            } else if (!empty($stdout)) {
                                error_log("Follow-on script output: " . $stdout); // Log output if needed
                            }
                            $response_data['follow_on_script_output'] = $stdout;
                        } else {
                            error_log("Failed to open process for follow-on script");
                            $response_data['status'] = 'error';
                            $response_data['error_message'] = "Failed to open process for follow-on script";
                        }
                    } catch (Exception $e) {
                        $message ="Error running follow-on script: " . $e->getMessage(); 
                        error_log($message); // Log exceptions
                        $response_data['status'] = 'error';
                        $response_data['error_message'] = $message;
                    }
                }
                if ( $response_data['status'] == 'success'){
                    wp_send_json_success($response_data);
                } else {
                    wp_send_json_error($response_data);
                }
            } else {
                wp_send_json_error($result, 500); // Send error response
            }
        } else {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'), 403);
        }
    } else {
        wp_send_json_error(array('message' => 'Missing list_id or post data.'), 400);
    }
    die();
}
// Register AJAX actions (important!)
add_action( 'wp_ajax_get_act_admin_list_data', 'get_act_admin_list_data' );
//add_action( 'wp_ajax_nopriv_get_act_admin_list_data', 'get_act_admin_list_data' ); // If needed

add_action( 'wp_ajax_save_act_admin_list_data', 'save_act_admin_list_data' );
//add_action( 'wp_ajax_nopriv_save_act_admin_list_data', 'save_act_admin_list_data' ); // If needed
function wpcf7_form_callback($tag, $args){
    if ( 'select' === $tag['basetype'] && 'recipients' === $tag['name'] ) {
        $file_path = LISTS_DIR . 'recipients.csv';
        $values = array();
        $labels = array();
        $default_value = '';
        
        // Read the CSV file and store data
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $c = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ( $c > 0 ){
                    $labels[] = $row[0];
                    $values[] = str_replace(';',',',$row[1]);
                }
                $c++;
            }
            fclose($handle);
        }
        
        // Populate the tag with all labels and values
        $tag['labels'] = array_merge($tag['labels'], $labels);
        $tag['values'] = array_merge($tag['values'], $values);
        
        // Check for URL parameter and find the corresponding email
        if (isset($_GET['recipients'])) {
            $url_param_name = sanitize_text_field($_GET['recipients']);
            // Loop through the labels to find a match
            foreach ($labels as $index => $label) {
                if ($label === $url_param_name) {
                    $default_value = $values[$index];
                    break; // Exit the loop once a match is found
                }
            }
            
            // If a default value was found, add it to the tag options
            if (!empty($default_value)) {
                //$tag['options'][] = 'default:' . $default_value;
                $tag['labels'] = array($url_param_name);
                $tag['values'] = array($default_value);
                foreach($labels as $l){
                    if ( $l != $url_param_name){
                        $tag['labels'] [] = $l;
                    }
                } 
                foreach($values as $v){
                    if ( $v != $default_value){
                        $tag['values'] [] = $v;
                    }
                }
            }
        }
    }
    return $tag;
}
add_filter( 'wpcf7_form_tag', 'wpcf7_form_callback', 10, 2 );

function wpcf7_split_recipients_for_mail( $replaced, $mail_tag, $html, $mail_tag_name ) {
    if ( 'recipients' === $mail_tag->name ) {
        // Check if the value contains a comma (indicating multiple emails)
        if ( strpos( $replaced, ',' ) !== false ) {
            // Split the comma-separated string into an array of email addresses
            $emails = array_map( 'trim', explode( ',', $replaced ) );
            // Return the array of email addresses, which Contact Form 7 will handle correctly
            error_log(sprintf("translated %s to %s",$replaced, print_r($emails, true)));
            return $emails;
        }
    }
    return $replaced;
}
add_filter( 'wpcf7_mail_tag_replaced', 'wpcf7_split_recipients_for_mail', 10, 4 );
?>