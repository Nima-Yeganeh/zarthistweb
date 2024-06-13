<?php
namespace WPAICG;

if ( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('\\WPAICG\\WPAICG_Assistants')) {
    class WPAICG_Assistants
    {
        private static $instance = null;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action( 'wp_ajax_wpaicg_create_assistant', array( $this, 'wpaicg_create_assistant' ) );
            add_action( 'wp_ajax_wpaicg_sync_assistants', array( $this, 'wpaicg_sync_assistants' ) );
            add_action('wp_ajax_wpaicg_delete_assistant', array($this, 'wpaicg_delete_assistant'));
            add_action('wp_ajax_wpaicg_modify_assistant', array($this, 'wpaicg_modify_assistant'));
        }

        // New function to handle the creation of an assistant via AJAX
        public function wpaicg_create_assistant() {
            check_ajax_referer('wpaicg_create_assistant_action', 'wpaicg_create_assistant_nonce');

            // Ensure only admins can execute this function
            if ( ! current_user_can('manage_options') ) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            // Retrieve or instantiate your OpenAI class here, if needed
            $openAI = \WPAICG\WPAICG_OpenAI::get_instance();

            $assistantName = isset($_POST['wpaicg_assistant_name']) ? sanitize_text_field($_POST['wpaicg_assistant_name']) : 'Math Tutor';
            $instructions = isset($_POST['wpaicg_assistant_instructions']) ? sanitize_textarea_field($_POST['wpaicg_assistant_instructions']) : '';
            $model = isset($_POST['wpaicg_assistant_model']) ? sanitize_text_field($_POST['wpaicg_assistant_model']) : 'gpt-4';
        
            $tools = isset($_POST['assistant_tools']) ? $_POST['assistant_tools'] : [];
            $sanitizedTools = array_map(function($tool) {
                return array("type" => sanitize_text_field($tool));
            }, $tools);

            $new_assistant_data = array(
                "instructions" => $instructions,
                "name" => $assistantName,
                "tools" => $sanitizedTools,
                "model" => $model
            );

            // File upload handling
            if (isset($_FILES['wpaicg_assistant_file']['tmp_name']) && file_exists($_FILES['wpaicg_assistant_file']['tmp_name'])) {
                // Check file size
                $maxFileSize = 512 * 1024 * 1024; // 512MB in bytes
                if ($_FILES['wpaicg_assistant_file']['size'] > $maxFileSize) {
                    error_log('File size exceeds 512MB');
                    // Handle the error appropriately - perhaps set an error message to inform the user
                } else {
                    $file = [
                        'filename' => $_FILES['wpaicg_assistant_file']['name'],
                        'data' => file_get_contents($_FILES['wpaicg_assistant_file']['tmp_name']),
                        'purpose' => 'assistants'
                    ];

                    // Upload the file and get the file ID
                    $upload_response = $openAI->uploadFile($file);
                    $upload_data = json_decode($upload_response, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($upload_data['id'])) {
                        // Add the file ID to the assistant data
                        $new_assistant_data['file_ids'] = [$upload_data['id']];
                    }
                }
            }

            $create_response = $openAI->createAssistant($new_assistant_data);
            $create_response_data = json_decode($create_response, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($create_response_data['id'])) {
                // Retrieve current assistants and add the new one
                $assistants = get_option('wpaicg_assistants', array());
                array_push($assistants, $create_response_data);
                update_option('wpaicg_assistants', $assistants);

                wp_send_json_success(['message' => 'New assistant created successfully']);
            } else {
                // Extracting the error message from the API response
                $apiErrorMessage = isset($create_response_data['error']['message']) ? $create_response_data['error']['message'] : 'Unknown error occurred while creating assistant';
                wp_send_json_error(['message' => $apiErrorMessage]);
            }
        }
        
        public function wpaicg_sync_assistants() {
            check_ajax_referer('wpaicg_sync_assistants_action', 'wpaicg_sync_assistants_nonce');

            if ( ! current_user_can('manage_options') ) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            $openAI = \WPAICG\WPAICG_OpenAI::get_instance();
            $query = ['limit' => 100];
            $response = $openAI->listAssistants($query);
            $assistants_api_response = json_decode($response, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($assistants_api_response['data'])) {
                update_option('wpaicg_assistants', $assistants_api_response['data']);
                wp_send_json_success(['message' => 'Assistants synced successfully']);
            } else {
                wp_send_json_error(['message' => 'Failed to sync assistants']);
            }
        }
        
        public function wpaicg_delete_assistant() {
            check_ajax_referer('wpaicg_delete_assistant_action', 'wpaicg_delete_assistant_nonce');
        
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
        
            $assistantIdToDelete = isset($_POST['assistant_id']) ? sanitize_text_field($_POST['assistant_id']) : '';
        
            if (!$assistantIdToDelete) {
                wp_send_json_error(['message' => 'No assistant ID provided']);
                return;
            }
        
            $openAI = \WPAICG\WPAICG_OpenAI::get_instance();
            $delete_response = $openAI->deleteAssistant($assistantIdToDelete);
            $delete_response_data = json_decode($delete_response, true);
        
            if (isset($delete_response_data['deleted']) && $delete_response_data['deleted']) {
                // Retrieve current assistants from wp_options
                $assistants = get_option('wpaicg_assistants', array());
        
                // Remove the deleted assistant from the array
                foreach ($assistants as $key => $assistant) {
                    if ($assistant['id'] === $assistantIdToDelete) {
                        unset($assistants[$key]);
                        break;
                    }
                }
        
                // Update the option with the modified array
                update_option('wpaicg_assistants', $assistants);
        
                // Format a dynamic success message using the assistant ID
                $assistantID = isset($delete_response_data['id']) ? $delete_response_data['id'] : 'the assistant';
                $success_message = "Assistant with ID '{$assistantID}' deleted successfully";

                wp_send_json_success(['message' => $success_message]);
            } else {
                // Check if the API provided a specific error message
                $error_message = isset($delete_response_data['error']) ? $delete_response_data['error'] : 'Failed to delete assistant';

                wp_send_json_error(['message' => $error_message]);
            }
        }

        public function wpaicg_modify_assistant() {
            check_ajax_referer('wpaicg_modify_assistant_action', 'wpaicg_modify_assistant_nonce');
        
            // Ensure only admins can execute this function
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
        
            $assistantId = isset($_POST['assistant_id']) ? sanitize_text_field($_POST['assistant_id']) : '';
            $assistantName = isset($_POST['assistant_name']) ? sanitize_text_field($_POST['assistant_name']) : '';
            $instructions = isset($_POST['assistant_instructions']) ? sanitize_textarea_field($_POST['assistant_instructions']) : '';
            $model = isset($_POST['assistant_model']) ? sanitize_text_field($_POST['assistant_model']) : '';

            $tools = isset($_POST['tools']) ? (array) $_POST['tools'] : [];
            $sanitizedTools = array_map(function($tool) {
                return array("type" => sanitize_text_field($tool));
            }, $tools);

            $assistantData = array(
                "name" => $assistantName,
                "instructions" => $instructions,
                "model" => $model,
                "tools" => $sanitizedTools
            );

            $openAI = \WPAICG\WPAICG_OpenAI::get_instance();
            $modify_response = $openAI->modifyAssistant($assistantId, $assistantData);
            $modify_response_data = json_decode($modify_response, true);
        
            if (json_last_error() === JSON_ERROR_NONE && isset($modify_response_data['id'])) {
                // Update the assistant data in the database
                $assistants = get_option('wpaicg_assistants', array());
                foreach ($assistants as $key => &$assistant) {
                    if ($assistant['id'] === $assistantId) {
                        $assistant['name'] = $assistantName;
                        $assistant['instructions'] = $instructions;
                        $assistant['model'] = $model;
                        $assistant['tools'] = $sanitizedTools; // Correctly updating the tools here
                        // No need to manually serialize, update_option will handle it
                        break;
                    }
                }

                update_option('wpaicg_assistants', $assistants);
        
                wp_send_json_success(['message' => 'Assistant modified successfully']);
            } else {
                // Check if the specific error message is present and use it
                $error_message = isset($modify_response_data['error']['message']) ? $modify_response_data['error']['message'] : 'Failed to modify assistant';
                wp_send_json_error(['message' => $error_message]);
            }
        }
                  
    }
    WPAICG_Assistants::get_instance();
}
