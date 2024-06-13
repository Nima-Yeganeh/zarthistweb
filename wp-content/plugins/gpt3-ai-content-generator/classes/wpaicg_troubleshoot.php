<?php
namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_TroubleShoot')) {
    class WPAICG_TroubleShoot
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('wp_ajax_wpaicg_troubleshoot_add_vector',[$this,'wpaicg_troubleshoot_add_vector']);
            add_action('wp_ajax_wpaicg_troubleshoot_delete_vector',[$this,'wpaicg_troubleshoot_delete_vector']);
            add_action('wp_ajax_wpaicg_troubleshoot_search',[$this,'wpaicg_troubleshoot_search']);
            add_action('wp_ajax_wpaicg_troubleshoot_save',[$this,'wpaicg_troubleshoot_save']);
            add_action('wp_ajax_wpaicg_troubleshoot_connect_qdrant', [$this, 'wpaicg_troubleshoot_connect_qdrant']);
            add_action('wp_ajax_wpaicg_troubleshoot_show_collections', [$this, 'wpaicg_troubleshoot_show_collections']);
            add_action('wp_ajax_wpaicg_troubleshoot_get_collection_details', [$this, 'wpaicg_troubleshoot_get_collection_details']);
            add_action('wp_ajax_wpaicg_troubleshoot_delete_collection', [$this, 'wpaicg_troubleshoot_delete_collection']);
            add_action('wp_ajax_wpaicg_troubleshoot_create_collection', [$this, 'wpaicg_troubleshoot_create_collection']);
            add_action('wp_ajax_wpaicg_troubleshoot_add_vector_qdrant', [$this, 'wpaicg_troubleshoot_add_vector_qdrant']);
            add_action('wp_ajax_wpaicg_troubleshoot_delete_vector_qdrant', [$this, 'wpaicg_troubleshoot_delete_vector_qdrant']);
            add_action('wp_ajax_wpaicg_troubleshoot_search_qdrant', [$this, 'wpaicg_troubleshoot_search_qdrant']);

        }

        public function wpaicg_troubleshoot_create_collection() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $collectionName = sanitize_text_field($_POST['collection_name']);
            $apiKey = get_option('wpaicg_qdrant_api_key', '');
            $dimension = 1536;

            if ($wpaicg_provider === 'Google') {
                $dimension = 768;
            } 
            $endpoint = get_option('wpaicg_qdrant_endpoint', '') . '/collections/' . $collectionName;
        
            $response = wp_remote_request($endpoint, [
                'method' => 'PUT',
                'headers' => [
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'vectors' => [
                        'distance' => 'Cosine',
                        'size' => $dimension
                    ],
                ])
            ]);
        
            if (is_wp_error($response)) {
                echo json_encode(['error' => $response->get_error_message()]);
            } else {
                echo wp_remote_retrieve_body($response);
            }
        
            die();
        }

        public function wpaicg_troubleshoot_delete_collection() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $collectionName = sanitize_text_field($_POST['collection_name']);
            $apiKey = get_option('wpaicg_qdrant_api_key', '');
            $endpoint = get_option('wpaicg_qdrant_endpoint', '') . '/collections/' . $collectionName;
        
            $response = wp_remote_request($endpoint, [
                'method' => 'DELETE',
                'headers' => ['api-key' => $apiKey]
            ]);
        
            if (is_wp_error($response)) {
                echo json_encode(['error' => $response->get_error_message()]);
            } else {
                echo wp_remote_retrieve_body($response);
            }
        
            die();
        }

        public function wpaicg_troubleshoot_get_collection_details() {
            // Verify nonce for security
            if ( ! wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce') ) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $collectionName = sanitize_text_field($_POST['collection_name']);
            $apiKey = get_option('wpaicg_qdrant_api_key', '');
            $endpoint = get_option('wpaicg_qdrant_endpoint', '') . "/collections/{$collectionName}";
        
            $response = wp_remote_get($endpoint, [
                'headers' => ['api-key' => $apiKey]
            ]);
        
            if (is_wp_error($response)) {
                echo json_encode(['error' => $response->get_error_message()]);
            } else {
                echo wp_remote_retrieve_body($response);
            }
        
            die();
        }
        

        public function wpaicg_troubleshoot_show_collections() {
            // Verify nonce for security
            if ( ! wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce') ) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $apiKey = get_option('wpaicg_qdrant_api_key', '');
            $endpoint = get_option('wpaicg_qdrant_endpoint', '') . '/collections';
        
            $response = wp_remote_get($endpoint, [
                'headers' => ['api-key' => $apiKey]
            ]);
        
            if (is_wp_error($response)) {
                echo json_encode(['error' => $response->get_error_message()]);
            } else {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $collections = array_column($body['result']['collections'], 'name');
                echo json_encode($collections);
            }
        
            die();
        }
        
        public function wpaicg_troubleshoot_connect_qdrant()
        {
            // nonce verification
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
            }

            $apiKey = sanitize_text_field($_POST['api_key']);
            $endpoint = sanitize_text_field($_POST['endpoint']);

            // Save Qdrant API key and endpoint
            update_option('wpaicg_qdrant_api_key', $apiKey);
            update_option('wpaicg_qdrant_endpoint', $endpoint);

            // Sample cURL request to Qdrant - replace with actual PHP cURL request
            $response = wp_remote_get($endpoint, [
                'headers' => ['api-key' => $apiKey]
            ]);

            if (is_wp_error($response)) {
                echo 'Error: ' . $response->get_error_message();
            } else {
                echo 'Response: ' . wp_remote_retrieve_body($response);
            }

            die();
        }

        public function wpaicg_troubleshoot_save()
        {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
            }
            if(!current_user_can('manage_options')){
                die(esc_html__('You do not have permission for this action.','gpt3-ai-content-generator'));
            }
            $key = sanitize_text_field($_REQUEST['key']);
            $value = sanitize_text_field($_REQUEST['value']);
            if(in_array($key,array(
                'wpaicg_troubleshoot_pinecone_api',
                'wpaicg_openai_trouble_api'
            ))) {
                update_option($key, $value);
            }
        }

        public function wpaicg_troubleshoot_search_qdrant() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $collectionName = sanitize_text_field($_POST['collection_name']);
            $qdrantEndpoint = sanitize_text_field($_POST['endpoint']) . '/collections/' . $collectionName . '/points/search';
            $query = stripslashes($_POST['query']);
            $api_key = get_option('wpaicg_qdrant_api_key', '');
        
            $response = wp_remote_post($qdrantEndpoint, array(
                'method' => 'POST',
                'headers' => [
                    'api-key' => $api_key,
                    'Content-Type' => 'application/json'
                ],
                'body' => $query
            ));
        
            if (is_wp_error($response)) {
                echo json_encode(['error' => $response->get_error_message()]);
            } else {
                echo wp_remote_retrieve_body($response);
            }
        
            die();
        }
        

        public function wpaicg_troubleshoot_add_vector()
        {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
            }
            $headers = array(
                'Api-Key' => sanitize_text_field($_REQUEST['api_key']),
                'Content-Type' => 'application/json'
            );
            $vectors = str_replace("\\",'',sanitize_text_field($_REQUEST['data']));
            $response = wp_remote_post(sanitize_text_field($_REQUEST['environment']),array(
                'headers' => $headers,
                'body' => $vectors
            ));
            if(is_wp_error($response)){
                die($response->get_error_message());
            }
            else{
                echo wp_remote_retrieve_body($response);
                die();
            }
        }

        public function wpaicg_troubleshoot_add_vector_qdrant()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $endpoint = sanitize_text_field($_REQUEST['endpoint']) . '/collections/' . sanitize_text_field($_REQUEST['collection_name']) . '/points?wait=true';
            // get api key from wpaicg_qdrant_api_key options
            $api_key = get_option('wpaicg_qdrant_api_key', '');

            $vectors = str_replace("\\", '', sanitize_text_field($_REQUEST['data']));
        
            $response = wp_remote_request($endpoint, array(
                'method'    => 'PUT',
                'headers' => ['api-key' => $api_key, 
                              'Content-Type' => 'application/json'],
                'body'      => $vectors,
            ));
        
            if (is_wp_error($response)) {
                die($response->get_error_message());
            } else {
                echo wp_remote_retrieve_body($response);
                die();
            }
        }

        public function wpaicg_troubleshoot_delete_vector_qdrant() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            $endpoint = sanitize_text_field($_REQUEST['endpoint']) . '/collections/' . sanitize_text_field($_REQUEST['collection_name']) . '/points/delete?wait=true';
            $api_key = get_option('wpaicg_qdrant_api_key', '');
            $points = str_replace("\\", '', sanitize_text_field($_REQUEST['data']));
        
            $response = wp_remote_request($endpoint, array(
                'method' => 'POST',
                'headers' => ['api-key' => $api_key, 'Content-Type' => 'application/json'],
                'body' => $points,
            ));
        
            if (is_wp_error($response)) {
                die($response->get_error_message());
            } else {
                echo wp_remote_retrieve_body($response);
                die();
            }
        }
        
        public function wpaicg_troubleshoot_search()
        {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
            }
            $headers = array(
                'Api-Key' => sanitize_text_field($_REQUEST['api_key']),
                'Content-Type' => 'application/json'
            );
            $data = str_replace("\\",'',sanitize_text_field($_REQUEST['data']));

            $response = wp_remote_post(sanitize_text_field($_REQUEST['environment']),array(
                'headers' => $headers,
                'body' => $data
            ));
            if(is_wp_error($response)){
                die($response->get_error_message());
            }
            else{
                echo wp_remote_retrieve_body($response);
                die();
            }
        }

        public function wpaicg_troubleshoot_delete_vector()
        {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
            }
            // get api key from options wpaicg_pinecone_api
            $api_key = get_option('wpaicg_pinecone_api','');
            $headers = array(
                'Api-Key' => $api_key,
                'Content-Type' => 'application/json'
            );
            $data = str_replace("\\",'',sanitize_text_field($_REQUEST['data']));
            $response = wp_remote_post(sanitize_text_field($_REQUEST['environment']),array(
                'headers' => $headers,
                'body' => $data
            ));
            if(is_wp_error($response)){
                die($response->get_error_message());
            }
            else{
                echo wp_remote_retrieve_body($response);
                die();
            }
        }
    }
    WPAICG_TroubleShoot::get_instance();
}
