<?php
namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_OpenRouterMethod')) {
    class WPAICG_OpenRouterMethod
    {
        private static  $instance = null ;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('wp_ajax_wpaicg_sync_openrouter_models', array($this, 'fetch_openrouter_models'));
        }

        public function fetch_openrouter_models() {
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg_sync_openrouter_models')) {
                wp_send_json_error('Nonce verification failed');
                return; // Stop execution if nonce verification fails
            }
        
            $response = wp_remote_request('https://openrouter.ai/api/v1/models');
        
            if (is_wp_error($response)) {
                wp_send_json_error('Failed to fetch models from OpenRouter');
                return; // Stop execution if the request failed
            }
        
            $models = json_decode(wp_remote_retrieve_body($response), true);
            if (is_null($models) || !isset($models['data'])) {
                wp_send_json_error('Invalid response from OpenRouter');
                return; // Stop execution if the response is invalid
            }
        
            // Remove the description field from each model
            $filtered_models = array_map(function($model) {
                unset($model['description']);
                return $model;
            }, $models['data']);
        
            // Update the option with new models excluding the description field
            update_option('wpaicg_openrouter_model_list', $filtered_models);
            wp_send_json_success('Model list updated successfully');
        }
        
    }

    WPAICG_OpenRouterMethod::get_instance();
}
