<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<style>
@keyframes rotation {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.rotating {
    animation: rotation 2s infinite linear;
}
</style>

<?php 
$wpaicg_save_setting_success = false;
$wpaicg_reset_setting_success = false;
if ( isset( $_POST['wpaicg_submit'] ) ) {
    check_admin_referer( 'wpaicg_setting_save' );
    if ( isset( $_POST['wpaicg_provider'] ) ) {
        update_option( 'wpaicg_provider', sanitize_text_field( $_POST['wpaicg_provider'] ) );
    }
    $option_mappings = [
        'wpaicg_ai_model'                        => 'wpaicg_ai_model',
        'wpaicg_sleep_time'                      => 'wpaicg_sleep_time',
        'wpaicg_google_model'                    => 'wpaicg_google_default_model',
        'wpaicg_openrouter_model'                => 'wpaicg_openrouter_default_model',
        'wpaicg_azure_endpoint'                  => 'wpaicg_azure_endpoint',
        'wpaicg_azure_deployment'                => 'wpaicg_azure_deployment',
        'wpaicg_azure_embeddings'                => 'wpaicg_azure_embeddings',
        'wpaicg_toc_title'                       => 'wpaicg_toc_title',
        'wpaicg_toc_title_tag'                   => 'wpaicg_toc_title_tag',
        'wpaicg_intro_title_tag'                 => 'wpaicg_intro_title_tag',
        'wpaicg_conclusion_title_tag'            => 'wpaicg_conclusion_title_tag',
        'wpaicg_content_custom_prompt'           => 'wpaicg_content_custom_prompt',
        'wpaicg_woo_custom_prompt_title'         => 'wpaicg_woo_custom_prompt_title',
        'wpaicg_woo_custom_prompt_short'         => 'wpaicg_woo_custom_prompt_short',
        'wpaicg_woo_custom_prompt_description'   => 'wpaicg_woo_custom_prompt_description',
        'wpaicg_woo_custom_prompt_keywords'      => 'wpaicg_woo_custom_prompt_keywords',
        'wpaicg_woo_custom_prompt_meta'          => 'wpaicg_woo_custom_prompt_meta',
        'wpaicg_woo_custom_prompt_focus_keyword' => 'wpaicg_woo_custom_prompt_focus_keyword',
        'wpaicg_order_status_token'              => 'wpaicg_order_status_token',
        'wpaicg_image_source'                    => 'wpaicg_image_source',
        'wpaicg_featured_image_source'           => 'wpaicg_featured_image_source',
        'wpaicg_dalle_type'                      => 'wpaicg_dalle_type',
        '_wpaicg_image_style'                    => '_wpaicg_image_style',
        'wpaicg_custom_image_settings'           => 'wpaicg_custom_image_settings',
        'wpaicg_pexels_orientation'              => 'wpaicg_pexels_orientation',
        'wpaicg_pexels_size'                     => 'wpaicg_pexels_size',
        'wpaicg_pixabay_language'                => 'wpaicg_pixabay_language',
        'wpaicg_pixabay_type'                    => 'wpaicg_pixabay_type',
        'wpaicg_pixabay_orientation'             => 'wpaicg_pixabay_orientation',
        'wpaicg_pixabay_order'                   => 'wpaicg_pixabay_order',
        'wpaicg_sd_api_version'                  => 'wpaicg_sd_api_version',
        'wpaicg_editor_change_action'            => 'wpaicg_editor_change_action',
        'wpaicg_comment_prompt'                  => 'wpaicg_comment_prompt',
        'wpaicg_search_placeholder'              => 'wpaicg_search_placeholder',
        'wpaicg_search_font_size'                => 'wpaicg_search_font_size',
        'wpaicg_search_font_color'               => 'wpaicg_search_font_color',
        'wpaicg_search_border_color'             => 'wpaicg_search_border_color',
        'wpaicg_search_bg_color'                 => 'wpaicg_search_bg_color',
        'wpaicg_search_width'                    => 'wpaicg_search_width',
        'wpaicg_search_height'                   => 'wpaicg_search_height',
        'wpaicg_search_no_result'                => 'wpaicg_search_no_result',
        'wpaicg_search_result_font_size'         => 'wpaicg_search_result_font_size',
        'wpaicg_search_result_font_color'        => 'wpaicg_search_result_font_color',
        'wpaicg_search_result_bg_color'          => 'wpaicg_search_result_bg_color',
        'wpaicg_search_loading_color'            => 'wpaicg_search_loading_color',
        'wpaicg_editor_button_menus'             => 'wpaicg_editor_button_menus',
    ];
    foreach ( $option_mappings as $post_key => $option_key ) {
        if ( isset( $_POST[$post_key] ) ) {
            if ( $post_key === 'wpaicg_content_custom_prompt' ) {
                update_option( $option_key, wp_kses_post( $_POST[$post_key] ) );
            } elseif ( $post_key === 'wpaicg_custom_image_settings' ) {
                update_option( $option_key, \WPAICG\wpaicg_util_core()->sanitize_text_or_array_field( $_POST[$post_key] ) );
            } elseif ( $post_key === 'wpaicg_editor_button_menus' ) {
                $wpaicg_editor_button_menus = array();
                $sanitized_data = \WPAICG\wpaicg_util_core()->sanitize_text_or_array_field( $_POST[$post_key] );
                // Strip slashes from the sanitized data
                $wpaicg_list_menus = stripslashes_deep( $sanitized_data );
                if ( $wpaicg_list_menus && is_array( $wpaicg_list_menus ) && count( $wpaicg_list_menus ) ) {
                    foreach ( $wpaicg_list_menus as $wpaicg_list_menu ) {
                        if ( isset( $wpaicg_list_menu['name'] ) && isset( $wpaicg_list_menu['prompt'] ) && $wpaicg_list_menu['name'] != '' && $wpaicg_list_menu['prompt'] != '' ) {
                            $wpaicg_editor_button_menus[] = $wpaicg_list_menu;
                        }
                    }
                }
                update_option( $option_key, $wpaicg_editor_button_menus );
            } else {
                update_option( $option_key, sanitize_text_field( $_POST[$post_key] ) );
            }
        }
    }
    // Check and update the image source option if api keys are not set then revert to dalle3
    function update_image_source_option(  $optionName, $apiName  ) {
        if ( isset( $_POST[$optionName] ) ) {
            $source = $_POST[$optionName];
            if ( $source === 'pexels' && empty( $_POST['wpaicg_pexels_api'] ) || $source === 'pixabay' && empty( $_POST['wpaicg_pixabay_api'] ) ) {
                update_option( $optionName, 'dalle3' );
            }
        }
    }

    // Check and update the main image source option
    update_image_source_option( 'wpaicg_image_source', 'wpaicg_pexels_api' );
    update_image_source_option( 'wpaicg_image_source', 'wpaicg_pixabay_api' );
    // Check and update the featured image source option
    update_image_source_option( 'wpaicg_featured_image_source', 'wpaicg_pexels_api' );
    update_image_source_option( 'wpaicg_featured_image_source', 'wpaicg_pixabay_api' );
    // Get the current provider and model
    $current_provider = get_option( 'wpaicg_provider', 'OpenAI' );
    // Get the current model based on the provider
    $current_model = ( $current_provider === 'OpenAI' ? get_option( 'wpaicg_ai_model', 'gpt-3.5-turbo' ) : (( $current_provider === 'Google' ? get_option( 'wpaicg_google_default_model', 'gemini-pro' ) : '' )) );
    // Default model for Google or other providers
    $settings_to_update = [
        'wpaicg_temperature'     => [
            'key'    => 'temperature',
            'filter' => 'floatval',
        ],
        'wpaicg_max_tokens'      => [
            'key'    => 'max_tokens',
            'filter' => 'floatval',
        ],
        'wpaicg_top_p'           => [
            'key'    => 'top_p',
            'filter' => 'floatval',
        ],
        'wpaicg_frequency'       => [
            'key'    => 'frequency_penalty',
            'filter' => 'floatval',
        ],
        'wpaicg_presence'        => [
            'key'    => 'presence_penalty',
            'filter' => 'floatval',
        ],
        'wpaicg_best_of'         => [
            'key'    => 'best_of',
            'filter' => 'intval',
        ],
        'wpai_language'          => [
            'key'    => 'wpai_language',
            'filter' => 'sanitize_text_field',
        ],
        'wpai_content_style'     => [
            'key'    => 'wpai_writing_style',
            'filter' => 'sanitize_text_field',
        ],
        'wpai_content_tone'      => [
            'key'    => 'wpai_writing_tone',
            'filter' => 'sanitize_text_field',
        ],
        'wpai_number_of_heading' => [
            'key'    => 'wpai_number_of_heading',
            'filter' => 'intval',
        ],
        'wpai_heading_tag'       => [
            'key'    => 'wpai_heading_tag',
            'filter' => 'sanitize_text_field',
        ],
        'wpai_cta_pos'           => [
            'key'    => 'wpai_cta_pos',
            'filter' => 'sanitize_text_field',
        ],
        'wpaicg_img_size'        => [
            'key'    => 'img_size',
            'filter' => 'sanitize_text_field',
        ],
    ];
    global $wpdb;
    $table_name = "{$wpdb->prefix}wpaicg";
    foreach ( $settings_to_update as $post_field => $details ) {
        if ( isset( $_POST[$post_field] ) ) {
            // Apply the specified filter function to sanitize/validate the input
            // Adjust for callable to allow use of anonymous function for max_tokens
            $sanitized_value = ( is_callable( $details['filter'] ) ? call_user_func( $details['filter'], $_POST[$post_field] ) : $details['filter']( $_POST[$post_field] ) );
            $wpdb->update( $table_name, [
                $details['key'] => $sanitized_value,
            ], [
                'name' => 'wpaicg_settings',
            ] );
        }
    }
    // List of checkbox fields to update
    $checkboxFields = [
        'wpai_modify_headings',
        'wpai_add_tagline',
        'wpai_add_faq',
        'wpai_add_keywords_bold',
        'wpai_add_intro',
        'wpai_add_conclusion'
    ];
    foreach ( $checkboxFields as $field ) {
        // Check if the checkbox was checked (present in $_POST) and set accordingly
        $value = ( isset( $_POST[$field] ) ? 1 : 0 );
        $wpdb->update( $table_name, [
            $field => $value,
        ], [
            'name' => 'wpaicg_settings',
        ] );
    }
    // List of checkbox fields to update in options
    $checkboxFieldsOptions = [
        'wpaicg_toc',
        'wpaicg_hide_introduction',
        'wpaicg_hide_conclusion',
        'wpaicg_content_custom_prompt_enable',
        '_wpaicg_seo_meta_desc',
        '_wpaicg_seo_meta_tag',
        '_yoast_wpseo_metadesc',
        '_aioseo_description',
        'rank_math_description',
        '_wpaicg_shorten_url',
        '_wpaicg_focus_keyword_in_url',
        '_wpaicg_sentiment_in_title',
        '_wpaicg_power_word_in_title',
        'wpaicg_woo_generate_title',
        'wpaicg_woo_generate_description',
        'wpaicg_woo_generate_short',
        'wpaicg_woo_generate_tags',
        'wpaicg_woo_meta_description',
        '_wpaicg_shorten_woo_url',
        'wpaicg_generate_woo_focus_keyword',
        'wpaicg_enforce_woo_keyword_in_url',
        'wpaicg_woo_custom_prompt',
        'wpaicg_pexels_enable_prompt',
        'wpaicg_pixabay_enable_prompt'
    ];
    foreach ( $checkboxFieldsOptions as $field ) {
        // Check if the checkbox was checked (present in $_POST) and set accordingly
        $value = ( isset( $_POST[$field] ) ? 1 : 0 );
        update_option( $field, $value );
    }
    $genTitleFromKeywords = ( isset( $_POST['_wpaicg_gen_title_from_keywords'] ) ? 1 : 0 );
    update_option( '_wpaicg_gen_title_from_keywords', $genTitleFromKeywords );
    // If '_wpaicg_gen_title_from_keywords' is not checked, '_wpaicg_original_title_in_prompt' should be false
    $originalTitleInPrompt = ( $genTitleFromKeywords ? ( isset( $_POST['_wpaicg_original_title_in_prompt'] ) ? 1 : 0 ) : 0 );
    update_option( '_wpaicg_original_title_in_prompt', $originalTitleInPrompt );
    if ( isset( $_POST['wpaicg_openai_api_key'] ) ) {
        $submittedApiKey = $_POST['wpaicg_openai_api_key'];
        // Check if the submitted API key is masked. This is just an example condition. Adjust according to your masking logic.
        if ( !preg_match( '/^\\*+/', $submittedApiKey ) ) {
            // The API key is not masked; proceed with updating it
            $sanitizedApiKey = sanitize_text_field( $submittedApiKey );
            $wpdb->update( $table_name, [
                'api_key' => $sanitizedApiKey,
            ], [
                'name' => 'wpaicg_settings',
            ] );
        }
        // If the API key is masked, do nothing to avoid updating with the masked value.
    }
    function update_api_key_option(  $postFieldName, $optionName  ) {
        if ( isset( $_POST[$postFieldName] ) ) {
            $submittedApiKey = $_POST[$postFieldName];
            // Check if the submitted API key is masked
            if ( !preg_match( '/^\\*+/', $submittedApiKey ) ) {
                $sanitizedApiKey = sanitize_text_field( $submittedApiKey );
                // Update the option with the sanitized API key
                update_option( $optionName, $sanitizedApiKey );
            }
            // If the API key is masked, do nothing to avoid updating with the masked value
        }
    }

    // Now use the function for each API key
    update_api_key_option( 'wpaicg_google_api_key', 'wpaicg_google_model_api_key' );
    update_api_key_option( 'wpaicg_azure_api_key', 'wpaicg_azure_api_key' );
    update_api_key_option( 'wpaicg_pexels_api', 'wpaicg_pexels_api' );
    update_api_key_option( 'wpaicg_pixabay_api', 'wpaicg_pixabay_api' );
    update_api_key_option( 'wpaicg_sd_api_key', 'wpaicg_sd_api_key' );
    update_api_key_option( 'wpaicg_openrouter_api_key', 'wpaicg_openrouter_api_key' );
    // Save Google Safety Settings
    if ( $_POST['wpaicg_provider'] === 'Google' && isset( $_POST['google_safety_settings'] ) ) {
        $safety_settings = [];
        foreach ( $_POST['google_safety_settings'] as $category => $threshold ) {
            $safety_settings[] = [
                'category'  => sanitize_text_field( $category ),
                'threshold' => sanitize_text_field( $threshold ),
            ];
        }
        update_option( 'wpaicg_google_safety_settings', $safety_settings );
    }
    // save the google model list
    if ( isset( $_POST['wpaicg_google_model_list'] ) ) {
        $google_model_list = explode( ',', $_POST['wpaicg_google_model_list'] );
        update_option( 'wpaicg_google_model_list', $google_model_list );
    }
    // save the openrouter model list
    if ( isset( $_POST['wpaicg_openrouter_model_list'] ) ) {
        $openrouter_models = explode( ',', $_POST['wpaicg_openrouter_model_list'] );
        update_option( 'wpaicg_openrouter_model_list', $openrouter_models );
    }
    $wpaicg_save_setting_success = true;
}
if ( isset( $_POST['wpaicg_reset'] ) ) {
    check_admin_referer( 'wpaicg_setting_save' );
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpaicg';
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
    // Recreate the table
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table_name} (\n                ID mediumint(11) NOT NULL AUTO_INCREMENT,\n                name text NOT NULL,\n                temperature float NOT NULL,\n                max_tokens float NOT NULL,\n                top_p float NOT NULL,\n                best_of float NOT NULL,\n                frequency_penalty float NOT NULL,\n                presence_penalty float NOT NULL,\n                img_size text NOT NULL,\n                api_key text NOT NULL,\n                wpai_language VARCHAR(255) NOT NULL,\n                wpai_add_img BOOLEAN NOT NULL,\n                wpai_add_intro BOOLEAN NOT NULL,\n                wpai_add_conclusion BOOLEAN NOT NULL,\n                wpai_add_tagline BOOLEAN NOT NULL,\n                wpai_add_faq BOOLEAN NOT NULL,\n                wpai_add_keywords_bold BOOLEAN NOT NULL,\n                wpai_number_of_heading INT NOT NULL,\n                wpai_modify_headings BOOLEAN NOT NULL,\n                wpai_heading_tag VARCHAR(10) NOT NULL,\n                wpai_writing_style VARCHAR(255) NOT NULL,\n                wpai_writing_tone VARCHAR(255) NOT NULL,\n                wpai_target_url VARCHAR(255) NOT NULL,\n                wpai_target_url_cta VARCHAR(255) NOT NULL,\n                wpai_cta_pos VARCHAR(255) NOT NULL,\n                added_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,\n                modified_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,\n                PRIMARY KEY  (ID)\n            ) {$charset_collate};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    // Insert default values into the table
    $wpdb->insert( $table_name, array(
        'name'                   => 'wpaicg_settings',
        'temperature'            => 1,
        'max_tokens'             => 1500,
        'top_p'                  => 0.01,
        'best_of'                => 1,
        'frequency_penalty'      => 0,
        'presence_penalty'       => 0,
        'img_size'               => '1024x1024',
        'api_key'                => 'sk..',
        'wpai_language'          => 'en',
        'wpai_add_img'           => 1,
        'wpai_add_intro'         => 'false',
        'wpai_add_conclusion'    => 'false',
        'wpai_add_tagline'       => 'false',
        'wpai_add_faq'           => 'false',
        'wpai_add_keywords_bold' => 'false',
        'wpai_number_of_heading' => 3,
        'wpai_modify_headings'   => 'false',
        'wpai_heading_tag'       => 'h1',
        'wpai_writing_style'     => 'infor',
        'wpai_writing_tone'      => 'formal',
        'wpai_cta_pos'           => 'beg',
        'added_date'             => gmdate( 'Y-m-d H:i:s' ),
        'modified_date'          => gmdate( 'Y-m-d H:i:s' ),
    ) );
    // Reset additional options to their default values
    update_option( 'wpaicg_ai_model', 'gpt-3.5-turbo' );
    update_option( 'wpaicg_provider', 'OpenAI' );
    update_option( 'wpaicg_sleep_time', 1 );
    update_option( 'wpaicg_google_model_api_key', '' );
    update_option( 'wpaicg_google_model_list', ['gemini-pro'] );
    update_option( 'wpaicg_google_default_model', 'gemini-pro' );
    update_option( 'wpaicg_azure_api_key', '' );
    update_option( 'wpaicg_azure_endpoint', '' );
    update_option( 'wpaicg_azure_deployment', '' );
    update_option( 'wpaicg_azure_embeddings', '' );
    update_option( 'wpaicg_toc', 0 );
    update_option( 'wpaicg_toc_title', 'Table of Contents' );
    update_option( 'wpaicg_toc_title_tag', 'h2' );
    update_option( 'wpaicg_hide_introduction', 0 );
    update_option( 'wpaicg_intro_title_tag', 'h2' );
    update_option( 'wpaicg_hide_conclusion', 0 );
    update_option( 'wpaicg_conclusion_title_tag', 'h2' );
    update_option( 'wpaicg_content_custom_prompt_enable', false );
    update_option( '_wpaicg_seo_meta_desc', false );
    update_option( '_wpaicg_seo_meta_tag', false );
    update_option( '_yoast_wpseo_metadesc', false );
    update_option( '_aioseo_description', false );
    update_option( 'rank_math_description', false );
    update_option( '_wpaicg_shorten_url', false );
    update_option( '_wpaicg_gen_title_from_keywords', false );
    update_option( '_wpaicg_original_title_in_prompt', false );
    update_option( '_wpaicg_focus_keyword_in_url', false );
    update_option( '_wpaicg_sentiment_in_title', false );
    update_option( '_wpaicg_power_word_in_title', false );
    update_option( 'wpaicg_woo_generate_title', false );
    update_option( 'wpaicg_woo_generate_description', false );
    update_option( 'wpaicg_woo_generate_short', false );
    update_option( 'wpaicg_woo_generate_tags', false );
    update_option( 'wpaicg_woo_meta_description', false );
    update_option( '_wpaicg_shorten_woo_url', false );
    update_option( 'wpaicg_generate_woo_focus_keyword', false );
    update_option( 'wpaicg_enforce_woo_keyword_in_url', false );
    update_option( 'wpaicg_woo_custom_prompt', false );
    update_option( 'wpaicg_woo_custom_prompt_title', 'Compose an SEO-optimized title in English for the following product: %s. Ensure it is engaging, concise, and includes relevant keywords to maximize its visibility on search engines.' );
    update_option( 'wpaicg_woo_custom_prompt_short', 'Provide a compelling and concise summary in English for the following product: %s, highlighting its key features, benefits, and unique selling points.' );
    update_option( 'wpaicg_woo_custom_prompt_description', 'Craft a comprehensive and engaging product description in English for: %s. Include specific details, features, and benefits, as well as the value it offers to the customer, thereby creating a compelling narrative around the product.' );
    update_option( 'wpaicg_woo_custom_prompt_keywords', 'Propose a set of relevant keywords in English for the following product: %s. The keywords should be directly related to the product, enhancing its discoverability. Please present these keywords in a comma-separated format, avoiding the use of symbols such as -, #, etc.' );
    update_option( 'wpaicg_woo_custom_prompt_meta', 'Craft a compelling and concise meta description in English for: %s. Aim to highlight its key features and benefits within a limit of 155 characters, while incorporating relevant keywords for SEO effectiveness.' );
    update_option( 'wpaicg_woo_custom_prompt_focus_keyword', 'Identify the primary keyword for the following product: %s. Please respond in English. No additional comments, just the keyword.' );
    update_option( 'wpaicg_order_status_token', 'completed' );
    update_option( 'wpaicg_image_source', 'dalle3' );
    update_option( 'wpaicg_featured_image_source', 'dalle3' );
    update_option( 'wpaicg_dalle_type', 'vivid' );
    update_option( '_wpaicg_image_style', '' );
    update_option( 'wpaicg_custom_image_settings', [] );
    update_option( 'wpaicg_sd_api_key', '' );
    update_option( 'wpaicg_pexels_api', '' );
    update_option( 'wpaicg_pexels_orientation', '' );
    update_option( 'wpaicg_pexels_size', '' );
    update_option( 'wpaicg_pexels_enable_prompt', false );
    update_option( 'wpaicg_pixabay_api', '' );
    update_option( 'wpaicg_pixabay_language', 'en' );
    update_option( 'wpaicg_pixabay_type', 'all' );
    update_option( 'wpaicg_pixabay_orientation', 'all' );
    update_option( 'wpaicg_pixabay_order', 'popular' );
    update_option( 'wpaicg_pixabay_enable_prompt', false );
    update_option( 'wpaicg_sd_api_version', '' );
    update_option( 'wpaicg_editor_button_menus', [] );
    update_option( 'wpaicg_editor_change_action', 'below' );
    $wpaicg_default_comment_prompt = "Please generate a relevant and thoughtful response to [username]'s comment on the post titled '[post_title]' with the excerpt '[post_excerpt]'. The user's latest comment is: '[last_comment]'. If applicable, consider the context of the previous conversation: '[parent_comments]'. Keep the response focused on the topic and avoid creating any new information.";
    update_option( 'wpaicg_comment_prompt', $wpaicg_default_comment_prompt );
    update_option( 'wpaicg_search_placeholder', 'Search anything..' );
    update_option( 'wpaicg_search_font_size', '13' );
    update_option( 'wpaicg_search_font_color', '#000' );
    update_option( 'wpaicg_search_border_color', '#cccccc' );
    update_option( 'wpaicg_search_bg_color', '#ffffff' );
    update_option( 'wpaicg_search_width', '100%' );
    update_option( 'wpaicg_search_height', '45px' );
    update_option( 'wpaicg_search_no_result', '5' );
    update_option( 'wpaicg_search_result_font_size', '15' );
    update_option( 'wpaicg_search_result_font_color', '#000000' );
    update_option( 'wpaicg_search_result_bg_color', '#ffffff' );
    update_option( 'wpaicg_search_loading_color', '#cccccc' );
    // openrouter api
    update_option( 'wpaicg_openrouter_api_key', '' );
    $wpaicg_reset_setting_success = true;
}
// Keep this block below the form submission handling
global $wpdb;
$settings_row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wpaicg WHERE name = 'wpaicg_settings'", ARRAY_A );
$current_temperature = ( isset( $settings_row['temperature'] ) ? $settings_row['temperature'] : 1 );
$current_max_tokens = ( isset( $settings_row['max_tokens'] ) ? $settings_row['max_tokens'] : 1500 );
$current_openai_api_key = ( isset( $settings_row['api_key'] ) ? $settings_row['api_key'] : 'sk..' );
$current_top_p = ( isset( $settings_row['top_p'] ) ? $settings_row['top_p'] : 0.01 );
$current_frequency = ( isset( $settings_row['frequency_penalty'] ) ? $settings_row['frequency_penalty'] : 0 );
$current_presence = ( isset( $settings_row['presence_penalty'] ) ? $settings_row['presence_penalty'] : 0 );
$current_best_of = ( isset( $settings_row['best_of'] ) ? $settings_row['best_of'] : 1 );
$current_img_size = ( isset( $settings_row['img_size'] ) ? $settings_row['img_size'] : '1024x1024' );
$image_sizes = \WPAICG\WPAICG_Util::get_instance()->wpaicg_image_sizes;
$currentLanguage = ( isset( $settings_row['wpai_language'] ) ? $settings_row['wpai_language'] : 'en' );
$languages = \WPAICG\WPAICG_Util::get_instance()->wpaicg_languages;
$currentStyle = ( isset( $settings_row['wpai_writing_style'] ) ? $settings_row['wpai_writing_style'] : 'infor' );
$writing_styles = \WPAICG\WPAICG_Util::get_instance()->wpaicg_writing_styles;
$currentTone = ( isset( $settings_row['wpai_writing_tone'] ) ? $settings_row['wpai_writing_tone'] : 'formal' );
$writing_tones = \WPAICG\WPAICG_Util::get_instance()->wpaicg_writing_tones;
$current_number_of_heading = ( isset( $settings_row['wpai_number_of_heading'] ) ? $settings_row['wpai_number_of_heading'] : 3 );
$heading_tags = \WPAICG\WPAICG_Util::get_instance()->wpaicg_heading_tags;
$currentTag = ( isset( $settings_row['wpai_heading_tag'] ) ? $settings_row['wpai_heading_tag'] : 'h1' );
$current_outline_editor = ( isset( $settings_row['wpai_modify_headings'] ) ? $settings_row['wpai_modify_headings'] : 0 );
$current_tagline = ( isset( $settings_row['wpai_add_tagline'] ) ? $settings_row['wpai_add_tagline'] : 0 );
$current_cta_pos = ( isset( $settings_row['wpai_cta_pos'] ) ? $settings_row['wpai_cta_pos'] : 'beg' );
$current_qa = ( isset( $settings_row['wpai_add_faq'] ) ? $settings_row['wpai_add_faq'] : 0 );
$current_bold_keywords = ( isset( $settings_row['wpai_add_keywords_bold'] ) ? $settings_row['wpai_add_keywords_bold'] : 0 );
$current_wpaicg_toc = get_option( 'wpaicg_toc', 0 );
$current_toc_title = get_option( 'wpaicg_toc_title', 'Table of Contents' );
$current_toc_title_tag = get_option( 'wpaicg_toc_title_tag', 'h2' );
$current_wpaicg_intro = ( isset( $settings_row['wpai_add_intro'] ) ? $settings_row['wpai_add_intro'] : 0 );
$current_hide_introduction = get_option( 'wpaicg_hide_introduction', 0 );
$current_intro_title_tag = get_option( 'wpaicg_intro_title_tag', 'h2' );
$current_wpaicg_conclusion = ( isset( $settings_row['wpai_add_conclusion'] ) ? $settings_row['wpai_add_conclusion'] : 0 );
$current_hide_conclusion = get_option( 'wpaicg_hide_conclusion', 0 );
$current_conclusion_title_tag = get_option( 'wpaicg_conclusion_title_tag', 'h2' );
$wpaicg_ai_model = get_option( 'wpaicg_ai_model', 'gpt-3.5-turbo-16k' );
$wpaicg_provider = get_option( 'wpaicg_provider', 'OpenAI' );
// Default to OpenAI
$wpaicg_google_api_key = get_option( 'wpaicg_google_model_api_key', '' );
// Get Google API Key
$wpaicg_google_model_list = get_option( 'wpaicg_google_model_list', ['gemini-pro'] );
// Get Google model list
$wpaicg_google_default_model = get_option( 'wpaicg_google_default_model', 'gemini-pro' );
$wpaicg_openrouter_api_key = get_option( 'wpaicg_openrouter_api_key', '' );
// Get OpenRouter API Key
$wpaicg_openrouter_model_list = get_option( 'wpaicg_openrouter_model_list', [] );
// Get OpenRouter model list
$wpaicg_azure_api_key = get_option( 'wpaicg_azure_api_key', '' );
$wpaicg_azure_endpoint = get_option( 'wpaicg_azure_endpoint', '' );
$wpaicg_azure_deployment = get_option( 'wpaicg_azure_deployment', '' );
$wpaicg_azure_embeddings = get_option( 'wpaicg_azure_embeddings', '' );
$wpaicg_content_custom_prompt_enable = get_option( 'wpaicg_content_custom_prompt_enable', false );
$wpaicg_content_custom_prompt = get_option( 'wpaicg_content_custom_prompt', '' );
if ( empty( $wpaicg_content_custom_prompt ) ) {
    $wpaicg_content_custom_prompt = \WPAICG\WPAICG_Custom_Prompt::get_instance()->wpaicg_default_custom_prompt;
}
// SEO Fields
$_wpaicg_seo_meta_desc = get_option( '_wpaicg_seo_meta_desc', false );
$_wpaicg_seo_meta_tag = get_option( '_wpaicg_seo_meta_tag', false );
$seo_plugins_options = [[
    'plugin'      => 'wordpress-seo/wp-seo.php',
    'option_name' => '_yoast_wpseo_metadesc',
    'label'       => esc_html__( 'Update Yoast Meta', 'gpt3-ai-content-generator' ),
], [
    'plugin'      => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
    'option_name' => '_aioseo_description',
    'label'       => esc_html__( 'Update All In One SEO Meta', 'gpt3-ai-content-generator' ),
], [
    'plugin'      => 'seo-by-rank-math/rank-math.php',
    'option_name' => 'rank_math_description',
    'label'       => esc_html__( 'Update Rank Math Meta', 'gpt3-ai-content-generator' ),
]];
$_yoast_wpseo_metadesc = get_option( '_yoast_wpseo_metadesc', false );
$_aioseo_description = get_option( '_aioseo_description', false );
$rank_math_description = get_option( 'rank_math_description', false );
if ( !get_option( '_wpaicg_shorten_url', false ) ) {
    add_option(
        '_wpaicg_shorten_url',
        false,
        '',
        'no'
    );
}
$_wpaicg_shorten_url = get_option( '_wpaicg_shorten_url', false );
if ( !get_option( '_wpaicg_gen_title_from_keywords', false ) ) {
    add_option(
        '_wpaicg_gen_title_from_keywords',
        false,
        '',
        'no'
    );
}
$_wpaicg_gen_title_from_keywords = get_option( '_wpaicg_gen_title_from_keywords', false );
if ( !get_option( '_wpaicg_original_title_in_prompt', false ) ) {
    add_option(
        '_wpaicg_original_title_in_prompt',
        false,
        '',
        'no'
    );
}
$_wpaicg_original_title_in_prompt = get_option( '_wpaicg_original_title_in_prompt', false );
if ( !get_option( '_wpaicg_focus_keyword_in_url', false ) ) {
    add_option(
        '_wpaicg_focus_keyword_in_url',
        false,
        '',
        'no'
    );
}
$_wpaicg_focus_keyword_in_url = get_option( '_wpaicg_focus_keyword_in_url', false );
if ( !get_option( '_wpaicg_sentiment_in_title', false ) ) {
    add_option(
        '_wpaicg_sentiment_in_title',
        false,
        '',
        'no'
    );
}
$_wpaicg_sentiment_in_title = get_option( '_wpaicg_sentiment_in_title', false );
if ( !get_option( '_wpaicg_power_word_in_title', false ) ) {
    add_option(
        '_wpaicg_power_word_in_title',
        false,
        '',
        'no'
    );
}
$_wpaicg_power_word_in_title = get_option( '_wpaicg_power_word_in_title', false );
if ( !get_option( 'wpaicg_woo_generate_title', false ) ) {
    add_option(
        'wpaicg_woo_generate_title',
        false,
        '',
        'no'
    );
}
$wpaicg_woo_generate_title = get_option( 'wpaicg_woo_generate_title', false );
if ( !get_option( 'wpaicg_woo_generate_description', false ) ) {
    add_option(
        'wpaicg_woo_generate_description',
        false,
        '',
        'no'
    );
}
$wpaicg_woo_generate_description = get_option( 'wpaicg_woo_generate_description', false );
if ( !get_option( 'wpaicg_woo_generate_short', false ) ) {
    add_option(
        'wpaicg_woo_generate_short',
        false,
        '',
        'no'
    );
}
$wpaicg_woo_generate_short = get_option( 'wpaicg_woo_generate_short', false );
if ( !get_option( 'wpaicg_woo_generate_tags', false ) ) {
    add_option(
        'wpaicg_woo_generate_tags',
        false,
        '',
        'no'
    );
}
$wpaicg_woo_generate_tags = get_option( 'wpaicg_woo_generate_tags', false );
if ( !get_option( 'wpaicg_woo_meta_description', false ) ) {
    add_option(
        'wpaicg_woo_meta_description',
        false,
        '',
        'no'
    );
}
$wpaicg_woo_meta_description = get_option( 'wpaicg_woo_meta_description', false );
if ( !get_option( '_wpaicg_shorten_woo_url', false ) ) {
    add_option(
        '_wpaicg_shorten_woo_url',
        false,
        '',
        'no'
    );
}
$_wpaicg_shorten_woo_url = get_option( '_wpaicg_shorten_woo_url', false );
if ( !get_option( 'wpaicg_generate_woo_focus_keyword', false ) ) {
    add_option(
        'wpaicg_generate_woo_focus_keyword',
        false,
        '',
        'no'
    );
}
$wpaicg_generate_woo_focus_keyword = get_option( 'wpaicg_generate_woo_focus_keyword', false );
if ( !get_option( 'wpaicg_enforce_woo_keyword_in_url', false ) ) {
    add_option(
        'wpaicg_enforce_woo_keyword_in_url',
        false,
        '',
        'no'
    );
}
$wpaicg_enforce_woo_keyword_in_url = get_option( 'wpaicg_enforce_woo_keyword_in_url', false );
if ( !get_option( 'wpaicg_woo_custom_prompt', false ) ) {
    add_option(
        'wpaicg_woo_custom_prompt',
        false,
        '',
        'no'
    );
}
$wpaicg_woo_custom_prompt = get_option( 'wpaicg_woo_custom_prompt', false );
$wpaicg_woo_custom_prompt_title = get_option( 'wpaicg_woo_custom_prompt_title', esc_html__( 'Compose an SEO-optimized title in English for the following product: %s. Ensure it is engaging, concise, and includes relevant keywords to maximize its visibility on search engines.', 'gpt3-ai-content-generator' ) );
$wpaicg_woo_custom_prompt_short = get_option( 'wpaicg_woo_custom_prompt_short', esc_html__( 'Provide a compelling and concise summary in English for the following product: %s, highlighting its key features, benefits, and unique selling points.', 'gpt3-ai-content-generator' ) );
$wpaicg_woo_custom_prompt_description = get_option( 'wpaicg_woo_custom_prompt_description', esc_html__( 'Craft a comprehensive and engaging product description in English for: %s. Include specific details, features, and benefits, as well as the value it offers to the customer, thereby creating a compelling narrative around the product.', 'gpt3-ai-content-generator' ) );
$wpaicg_woo_custom_prompt_keywords = get_option( 'wpaicg_woo_custom_prompt_keywords', esc_html__( 'Propose a set of relevant keywords in English for the following product: %s. The keywords should be directly related to the product, enhancing its discoverability. Please present these keywords in a comma-separated format, avoiding the use of symbols such as -, #, etc.', 'gpt3-ai-content-generator' ) );
$wpaicg_woo_custom_prompt_meta = get_option( 'wpaicg_woo_custom_prompt_meta', esc_html__( 'Craft a compelling and concise meta description in English for: %s. Aim to highlight its key features and benefits within a limit of 155 characters, while incorporating relevant keywords for SEO effectiveness.', 'gpt3-ai-content-generator' ) );
$wpaicg_woo_custom_prompt_focus_keyword = get_option( 'wpaicg_woo_custom_prompt_focus_keyword', esc_html__( 'Identify the primary keyword for the following product: %s. Please respond in English. No additional comments, just the keyword.', 'gpt3-ai-content-generator' ) );
$wpaicg_woo_custom_prompt_focus_keyword = str_replace( "\\", '', $wpaicg_woo_custom_prompt_focus_keyword );
$wpaicg_woo_custom_prompt_title = str_replace( "\\", '', $wpaicg_woo_custom_prompt_title );
$wpaicg_woo_custom_prompt_short = str_replace( "\\", '', $wpaicg_woo_custom_prompt_short );
$wpaicg_woo_custom_prompt_description = str_replace( "\\", '', $wpaicg_woo_custom_prompt_description );
$wpaicg_woo_custom_prompt_keywords = str_replace( "\\", '', $wpaicg_woo_custom_prompt_keywords );
$wpaicg_woo_custom_prompt_meta = str_replace( "\\", '', $wpaicg_woo_custom_prompt_meta );
if ( !get_option( 'wpaicg_order_status_token', false ) ) {
    add_option(
        'wpaicg_order_status_token',
        false,
        '',
        'no'
    );
}
$wpaicg_order_status_token = get_option( 'wpaicg_order_status_token', 'completed' );
function get_image_source_options(  $selected_source, $default = ''  ) {
    $options = array(
        'none'     => esc_html__( 'None', 'gpt3-ai-content-generator' ),
        'dalle3hd' => esc_html__( 'DALL-E 3 HD', 'gpt3-ai-content-generator' ),
        'dalle3'   => esc_html__( 'DALL-E 3', 'gpt3-ai-content-generator' ),
        'dalle'    => esc_html__( 'DALL-E 2', 'gpt3-ai-content-generator' ),
        'pexels'   => esc_html__( 'Pexels', 'gpt3-ai-content-generator' ),
        'pixabay'  => esc_html__( 'Pixabay', 'gpt3-ai-content-generator' ),
    );
    if ( empty( $selected_source ) ) {
        $selected_source = $default;
    }
    $html_options = '';
    foreach ( $options as $value => $label ) {
        $selected_attr = selected( $selected_source, $value, false );
        $html_options .= "<option value='" . esc_attr( $value ) . "'{$selected_attr}>{$label}</option>";
    }
    return $html_options;
}

if ( !get_option( 'wpaicg_image_source', false ) ) {
    add_option(
        'wpaicg_image_source',
        false,
        '',
        'no'
    );
}
$wpaicg_image_source = get_option( 'wpaicg_image_source', 'dalle3' );
if ( !get_option( 'wpaicg_featured_image_source', false ) ) {
    add_option(
        'wpaicg_featured_image_source',
        false,
        '',
        'no'
    );
}
$wpaicg_featured_image_source = get_option( 'wpaicg_featured_image_source', 'dalle3' );
if ( !get_option( 'wpaicg_dalle_type', false ) ) {
    add_option(
        'wpaicg_dalle_type',
        false,
        '',
        'no'
    );
}
$wpaicg_dalle_type = get_option( 'wpaicg_dalle_type', 'vivid' );
if ( !get_option( '_wpaicg_image_style', false ) ) {
    add_option(
        '_wpaicg_image_style',
        false,
        '',
        'no'
    );
}
$_wpaicg_image_style = get_option( '_wpaicg_image_style', '' );
$image_style_options = \WPAICG\WPAICG_Util::get_instance()->wpaicg_image_styles;
$wpaicg_art_file = WPAICG_PLUGIN_DIR . 'admin/data/art.json';
$wpaicg_painter_data = file_get_contents( $wpaicg_art_file );
$wpaicg_painter_data = json_decode( $wpaicg_painter_data, true );
$wpaicg_style_data = file_get_contents( $wpaicg_art_file );
$wpaicg_style_data = json_decode( $wpaicg_style_data, true );
$wpaicg_photo_file = WPAICG_PLUGIN_DIR . 'admin/data/photo.json';
$wpaicg_photo_data = file_get_contents( $wpaicg_photo_file );
$wpaicg_photo_data = json_decode( $wpaicg_photo_data, true );
if ( !get_option( 'wpaicg_custom_image_settings', false ) ) {
    add_option(
        'wpaicg_custom_image_settings',
        false,
        '',
        'no'
    );
}
$wpaicg_custom_image_settings = get_option( 'wpaicg_custom_image_settings', [] );
if ( !get_option( 'wpaicg_sd_api_key', false ) ) {
    add_option(
        'wpaicg_sd_api_key',
        false,
        '',
        'no'
    );
}
$wpaicg_sd_api_key = get_option( 'wpaicg_sd_api_key', '' );
if ( !get_option( 'wpaicg_pexels_api', false ) ) {
    add_option(
        'wpaicg_pexels_api',
        false,
        '',
        'no'
    );
}
$wpaicg_pexels_api = get_option( 'wpaicg_pexels_api', '' );
if ( !get_option( 'wpaicg_pexels_orientation', false ) ) {
    add_option(
        'wpaicg_pexels_orientation',
        false,
        '',
        'no'
    );
}
$wpaicg_pexels_orientation = get_option( 'wpaicg_pexels_orientation', '' );
if ( !get_option( 'wpaicg_pexels_size', false ) ) {
    add_option(
        'wpaicg_pexels_size',
        false,
        '',
        'no'
    );
}
$wpaicg_pexels_size = get_option( 'wpaicg_pexels_size', '' );
if ( !get_option( 'wpaicg_pexels_enable_prompt', false ) ) {
    add_option(
        'wpaicg_pexels_enable_prompt',
        false,
        '',
        'no'
    );
}
$wpaicg_pexels_enable_prompt = get_option( 'wpaicg_pexels_enable_prompt', false );
if ( !get_option( 'wpaicg_pexels_custom_prompt', false ) ) {
    add_option(
        'wpaicg_pexels_custom_prompt',
        false,
        '',
        'no'
    );
}
$wpaicg_pexels_custom_prompt = get_option( 'wpaicg_pexels_custom_prompt', 'Extract the most significant keyword from the given title: [title]. Please provide the keyword in the format #keyword, without any additional sentences, words, or characters. Ensure that the keyword consists of a single word, and do not combine or concatenate words or phrases in the keyword.' );
if ( !get_option( 'wpaicg_pixabay_api', false ) ) {
    add_option(
        'wpaicg_pixabay_api',
        false,
        '',
        'no'
    );
}
$wpaicg_pixabay_api = get_option( 'wpaicg_pixabay_api', '' );
if ( !get_option( 'wpaicg_pixabay_language', false ) ) {
    add_option(
        'wpaicg_pixabay_language',
        false,
        '',
        'no'
    );
}
$wpaicg_pixabay_language = get_option( 'wpaicg_pixabay_language', 'en' );
if ( !get_option( 'wpaicg_pixabay_type', false ) ) {
    add_option(
        'wpaicg_pixabay_type',
        false,
        '',
        'no'
    );
}
$wpaicg_pixabay_type = get_option( 'wpaicg_pixabay_type', 'all' );
if ( !get_option( 'wpaicg_pixabay_orientation', false ) ) {
    add_option(
        'wpaicg_pixabay_orientation',
        false,
        '',
        'no'
    );
}
$wpaicg_pixabay_orientation = get_option( 'wpaicg_pixabay_orientation', 'all' );
if ( !get_option( 'wpaicg_pixabay_order', false ) ) {
    add_option(
        'wpaicg_pixabay_order',
        false,
        '',
        'no'
    );
}
$wpaicg_pixabay_order = get_option( 'wpaicg_pixabay_order', 'popular' );
if ( !get_option( 'wpaicg_pixabay_enable_prompt', false ) ) {
    add_option(
        'wpaicg_pixabay_enable_prompt',
        false,
        '',
        'no'
    );
}
$wpaicg_pixabay_enable_prompt = get_option( 'wpaicg_pixabay_enable_prompt', false );
$wpaicg_pixabay_custom_prompt = get_option( 'wpaicg_pixabay_custom_prompt', 'Extract the most significant keyword from the given title: [title]. Please provide the keyword in the format #keyword, without any additional sentences, words, or characters. Ensure that the keyword consists of a single word, and do not combine or concatenate words or phrases in the keyword.' );
if ( !get_option( 'wpaicg_sd_api_version', false ) ) {
    add_option(
        'wpaicg_sd_api_version',
        false,
        '',
        'no'
    );
}
$wpaicg_sd_api_version = get_option( 'wpaicg_sd_api_version', '' );
if ( !get_option( 'wpaicg_editor_button_menus', false ) ) {
    add_option(
        'wpaicg_editor_button_menus',
        false,
        '',
        'no'
    );
}
$wpaicg_editor_button_menus = get_option( 'wpaicg_editor_button_menus', [] );
if ( !get_option( 'wpaicg_editor_change_action', false ) ) {
    add_option(
        'wpaicg_editor_change_action',
        false,
        '',
        'no'
    );
}
$wpaicg_editor_change_action = get_option( 'wpaicg_editor_change_action', 'below' );
if ( !is_array( $wpaicg_editor_button_menus ) || count( $wpaicg_editor_button_menus ) == 0 ) {
    $wpaicg_editor_button_menus = \WPAICG\WPAICG_Editor::get_instance()->wpaicg_edit_default_menus;
}
$wpaicg_default_comment_prompt = "Please generate a relevant and thoughtful response to [username]'s comment on the post titled '[post_title]' with the excerpt '[post_excerpt]'. The user's latest comment is: '[last_comment]'. If applicable, consider the context of the previous conversation: '[parent_comments]'. Keep the response focused on the topic and avoid creating any new information.";
if ( !get_option( 'wpaicg_comment_prompt', false ) ) {
    add_option(
        'wpaicg_comment_prompt',
        false,
        '',
        'no'
    );
}
$wpaicg_comment_prompt = get_option( 'wpaicg_comment_prompt', $wpaicg_default_comment_prompt );
if ( !get_option( 'wpaicg_search_placeholder', false ) ) {
    add_option(
        'wpaicg_search_placeholder',
        false,
        '',
        'no'
    );
}
$wpaicg_search_placeholder = get_option( 'wpaicg_search_placeholder', esc_html__( 'Search anything..', 'gpt3-ai-content-generator' ) );
if ( !get_option( 'wpaicg_search_font_size', false ) ) {
    add_option(
        'wpaicg_search_font_size',
        false,
        '',
        'no'
    );
}
$wpaicg_search_font_size = get_option( 'wpaicg_search_font_size', '13' );
if ( !get_option( 'wpaicg_search_font_color', false ) ) {
    add_option(
        'wpaicg_search_font_color',
        false,
        '',
        'no'
    );
}
$wpaicg_search_font_color = get_option( 'wpaicg_search_font_color', '#000000' );
if ( !get_option( 'wpaicg_search_border_color', false ) ) {
    add_option(
        'wpaicg_search_border_color',
        false,
        '',
        'no'
    );
}
$wpaicg_search_border_color = get_option( 'wpaicg_search_border_color', '#cccccc' );
if ( !get_option( 'wpaicg_search_bg_color', false ) ) {
    add_option(
        'wpaicg_search_bg_color',
        false,
        '',
        'no'
    );
}
$wpaicg_search_bg_color = get_option( 'wpaicg_search_bg_color', '#ffffff' );
if ( !get_option( 'wpaicg_search_width', false ) ) {
    add_option(
        'wpaicg_search_width',
        false,
        '',
        'no'
    );
}
$wpaicg_search_width = get_option( 'wpaicg_search_width', '100%' );
if ( !get_option( 'wpaicg_search_height', false ) ) {
    add_option(
        'wpaicg_search_height',
        false,
        '',
        'no'
    );
}
$wpaicg_search_height = get_option( 'wpaicg_search_height', '45px' );
if ( !get_option( 'wpaicg_search_no_result', false ) ) {
    add_option(
        'wpaicg_search_no_result',
        false,
        '',
        'no'
    );
}
$wpaicg_search_no_result = get_option( 'wpaicg_search_no_result', '5' );
if ( !get_option( 'wpaicg_search_result_font_size', false ) ) {
    add_option(
        'wpaicg_search_result_font_size',
        false,
        '',
        'no'
    );
}
$wpaicg_search_result_font_size = get_option( 'wpaicg_search_result_font_size', '13' );
if ( !get_option( 'wpaicg_search_result_font_color', false ) ) {
    add_option(
        'wpaicg_search_result_font_color',
        false,
        '',
        'no'
    );
}
$wpaicg_search_result_font_color = get_option( 'wpaicg_search_result_font_color', '#000000' );
if ( !get_option( 'wpaicg_search_result_bg_color', false ) ) {
    add_option(
        'wpaicg_search_result_bg_color',
        false,
        '',
        'no'
    );
}
$wpaicg_search_result_bg_color = get_option( 'wpaicg_search_result_bg_color', '#ffffff' );
if ( !get_option( 'wpaicg_search_loading_color', false ) ) {
    add_option(
        'wpaicg_search_loading_color',
        false,
        '',
        'no'
    );
}
$wpaicg_search_loading_color = get_option( 'wpaicg_search_loading_color', '#cccccc' );
$message = '';
if ( $wpaicg_save_setting_success ) {
    $message = esc_html__( 'Settings saved successfully.', 'gpt3-ai-content-generator' );
} elseif ( $wpaicg_reset_setting_success ) {
    $message = esc_html__( 'Settings reset successfully.', 'gpt3-ai-content-generator' );
}
if ( $message !== '' ) {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php 
    echo esc_html( $message );
    ?></p>
    </div>
<?php 
}
?>
<div class="demo-page-master">
  <div class="demo-page-master-navigation">
    <nav>
      <ul>
        <li>
          <a href="#settings">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tool">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
            </svg>
            <?php 
echo esc_html__( 'AI Engine', 'gpt3-ai-content-generator' );
?>
            </a>
        </li>
        <li>
          <a href="#content">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers">
              <polygon points="12 2 2 7 12 12 22 7 12 2" />
              <polyline points="2 17 12 22 22 17" />
              <polyline points="2 12 12 17 22 12" />
            </svg>
            <?php 
echo esc_html__( 'Content Writer', 'gpt3-ai-content-generator' );
?>
        </a>
        </li>
        <li>
          <a href="#seo">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-justify">
              <line x1="21" y1="10" x2="3" y2="10" />
              <line x1="21" y1="6" x2="3" y2="6" />
              <line x1="21" y1="14" x2="3" y2="14" />
              <line x1="21" y1="18" x2="3" y2="18" />
            </svg>
            <?php 
echo esc_html__( 'SEO', 'gpt3-ai-content-generator' );
?>
        </a>
        </li>
        <li>
          <a href="#image">
            <svg xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg)" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-columns">
              <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7m0-18H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7m0-18v18" />
            </svg>
            <?php 
echo esc_html__( 'Image', 'gpt3-ai-content-generator' );
?>
        </a>
        </li>
        <li>
          <a href="#woocommerce">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square">
              <polyline points="9 11 12 14 22 4" />
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg>
            <?php 
echo esc_html__( 'WooCommerce', 'gpt3-ai-content-generator' );
?>
            </a>
        </li>
        <li>
          <a href="#assistant">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-feather">
              <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z" />
              <line x1="16" y1="8" x2="2" y2="22" />
              <line x1="17.5" y1="15" x2="9" y2="15" />
            </svg>
            <?php 
echo esc_html__( 'AI Assistant', 'gpt3-ai-content-generator' );
?>
        </a>
        </li>
        <li>
          <a href="#tools">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layout">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="9" y1="21" x2="9" y2="9"></line>
        </svg>
            <?php 
echo esc_html__( 'Tools', 'gpt3-ai-content-generator' );
?>
        </a>
        </li>
      </ul>
    </nav>
  </div>
  <main class="demo-page-master-content">
    <form action="" method="post">
        <?php 
wp_nonce_field( 'wpaicg_setting_save' );
?>
        
        <!--  AI ENGINE -->
        <section>
            <div class="href-target" id="settings"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tool">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                </svg>
                <?php 
echo esc_html__( 'AI Engine', 'gpt3-ai-content-generator' );
?>
            </h1>

            <!-- PROVIDER -->
            <div class="unique-page-container">
                <div class="nice-form-group">
                    <label for="wpaicg_provider"><?php 
echo esc_html__( 'Provider', 'gpt3-ai-content-generator' );
?></label>
                    <select id="wpaicg_provider" name="wpaicg_provider" class="specific-select">
                        <option value="OpenAI" <?php 
selected( $wpaicg_provider, 'OpenAI' );
?>>OpenAI</option>
                        <option value="Google" <?php 
selected( $wpaicg_provider, 'Google' );
?>>Google</option>
                        <option value="OpenRouter" <?php 
selected( $wpaicg_provider, 'OpenRouter' );
?>>OpenRouter</option>
                        <option value="Azure" <?php 
selected( $wpaicg_provider, 'Azure' );
?>>Microsoft</option>
                    </select>
                    <a href="https://docs.aipower.org/docs/category/ai-engines" target="_blank">?</a>
                </div>
            </div>

            <!-- OPENAI SPECIFIC FIELDS -->
            <div id="openai_specific_fields">
                <!-- OPENAI API KEY -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input id="wpaicg_openai_api_key" name="wpaicg_openai_api_key" type="text" value="<?php 
echo esc_attr( $current_openai_api_key );
?>" onfocus="unmaskValue(this)" onblur="maskValue(this)" class="specific-textfield">
                        <a href="https://beta.openai.com/account/api-keys" target="_blank"><?php 
echo esc_html__( 'Get Your Api Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <!-- OPENAI MODELS -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="wpaicg_ai_model"><?php 
echo esc_html__( 'Model', 'gpt3-ai-content-generator' );
?></label>
                        <select id="wpaicg_ai_model" name="wpaicg_ai_model" class="specific-select">
                            <?php 
$gpt4_models = \WPAICG\WPAICG_Util::get_instance()->openai_gpt4_models;
$gpt35_models = \WPAICG\WPAICG_Util::get_instance()->openai_gpt35_models;
$custom_models_serialized = get_option( 'wpaicg_custom_models', '' );
$custom_models = maybe_unserialize( $custom_models_serialized );
// Initialize $custom_models as an empty array if it's not an array
if ( !is_array( $custom_models ) ) {
    $custom_models = [];
}
$formatted_custom_models = [];
foreach ( $custom_models as $index => $model ) {
    // Use model string as both key and value
    $formatted_custom_models[$model] = $model;
}
$custom_models = $formatted_custom_models;
// Assuming $custom_models is an associative array as well.
$current_model = $wpaicg_ai_model;
// This should be the model currently selected
?>
                            <optgroup label="GPT-4">
                                <?php 
foreach ( $gpt4_models as $value => $name ) {
    ?>
                                    <option value="<?php 
    echo esc_attr( $value );
    ?>"<?php 
    selected( $value, $current_model );
    ?>><?php 
    echo esc_html( $name );
    ?></option>
                                <?php 
}
?>
                            </optgroup>
                            <optgroup label="GPT-3.5">
                                <?php 
foreach ( $gpt35_models as $value => $name ) {
    ?>
                                    <option value="<?php 
    echo esc_attr( $value );
    ?>"<?php 
    selected( $value, $current_model );
    ?>><?php 
    echo esc_html( $name );
    ?></option>
                                <?php 
}
?>
                            </optgroup>
                            <optgroup label="Custom Models">
                                <?php 
foreach ( $custom_models as $value => $name ) {
    ?>
                                        <option value="<?php 
    echo esc_attr( $value );
    ?>" <?php 
    selected( $value, $current_model );
    ?>>
                                            <?php 
    echo esc_html( $name );
    ?>
                                        </option>
                                    <?php 
}
?>
                            </optgroup>
                        </select>
                        <button id="syncButton" class="button button-primary wpaicg_sync_finetune" type="button" style="line-height: 3.5;height: 40px">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- GOOGLE SPECIFIC FIELDS -->
            <div id="google_specific_fields" style="display: none;">
                <!-- GOOGLE API KEY -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input id="wpaicg_google_api_key" name="wpaicg_google_api_key" type="text" value="<?php 
echo esc_attr( $wpaicg_google_api_key );
?>" onfocus="unmaskValue(this)" onblur="maskValue(this)" class="specific-textfield">
                        <a href="https://aistudio.google.com/app/apikey" target="_blank"><?php 
echo esc_html__( 'Get Your Api Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <!-- GOOGLE MODELS -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Model', 'gpt3-ai-content-generator' );
?></label>
                        <select id="wpaicg_google_model" name="wpaicg_google_model" class="specific-select">
                        <?php 
if ( isset( $wpaicg_google_model_list ) && is_array( $wpaicg_google_model_list ) && count( $wpaicg_google_model_list ) > 0 ) {
    foreach ( $wpaicg_google_model_list as $model ) {
        // Define words that trigger disabling the option
        $disabledWords = ['vision'];
        // Add words that should disable the option
        $shouldBeDisabled = false;
        foreach ( $disabledWords as $word ) {
            if ( strpos( $model, $word ) !== false ) {
                $shouldBeDisabled = true;
                break;
                // Break the loop if any word is found
            }
        }
        ?>
                                <option value="<?php 
        echo esc_attr( $model );
        ?>" <?php 
        selected( $model, $wpaicg_google_default_model );
        ?> <?php 
        echo ( $shouldBeDisabled ? 'disabled' : '' );
        ?>>
                                    <?php 
        echo esc_html( ucwords( str_replace( '-', ' ', $model ) ) );
        // Convert save format to display format
        ?>
                                </option>
                            <?php 
    }
} else {
    ?>
                        <option value="gemini-pro" <?php 
    selected( 'gemini-pro', $wpaicg_google_default_model );
    ?>><?php 
    echo esc_html__( 'Gemini Pro', 'gpt3-ai-content-generator' );
    ?></option>
                        <?php 
}
?>
                        </select>
                        <button id="syncGoogleButton" class="button button-primary wpaicg_sync_google_models" type="button" style="line-height: 3.5;height: 40px">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </button>
                        <input type="hidden" name="wpaicg_google_model_list" value="<?php 
echo ( isset( $wpaicg_google_model_list ) && is_array( $wpaicg_google_model_list ) ? esc_attr( implode( ',', $wpaicg_google_model_list ) ) : '' );
?>">
                    </div>
                </div>
            </div>
            <!-- AZURE SPECIFIC FIELDS -->
            <div id="azure_specific_fields" style="display: none;">
                <!-- AZURE API KEY -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" id="wpaicg_azure_api_key" class="specific-textfield" name="wpaicg_azure_api_key" value="<?php 
echo esc_attr( $wpaicg_azure_api_key );
?>" onfocus="unmaskValue(this)" onblur="maskValue(this)" >
                        <a href="https://azure.microsoft.com/en-us/products/ai-services/openai-service" target="_blank"><?php 
echo esc_html__( 'Get Your Api Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <!-- AZURE ENDPOINT -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Endpoint', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" id="wpaicg_azure_endpoint" name="wpaicg_azure_endpoint" value="<?php 
echo esc_attr( $wpaicg_azure_endpoint );
?>" class="specific-textfield">
                        <a href="https://docs.aipower.org/docs/ai-engine/azure-openai" target="_blank">?</a>
                    </div>
                </div>
                <!-- AZURE DEPLOYMENT -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Deployment', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" id="wpaicg_azure_deployment" name="wpaicg_azure_deployment" value="<?php 
echo esc_attr( $wpaicg_azure_deployment );
?>" class="specific-textfield">
                        <a href="https://docs.aipower.org/docs/ai-engine/azure-openai" target="_blank">?</a>
                    </div>
                </div>
                <!-- AZURE EMBEDDINGS -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( '  Embeddings', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" id="wpaicg_azure_embeddings" name="wpaicg_azure_embeddings" value="<?php 
echo esc_attr( $wpaicg_azure_embeddings );
?>" class="specific-textfield">
                        <a href="https://docs.aipower.org/docs/ai-engine/azure-openai" target="_blank">?</a>
                    </div>
                </div>
            </div>
            <!-- OPENROUTER SPECIFIC FIELDS -->
            <div id="openrouter_specific_fields" style="display: none;">
                <!-- OPENROUTER API KEY -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" id="wpaicg_openrouter_api_key" class="specific-textfield" name="wpaicg_openrouter_api_key" value="<?php 
echo esc_attr( $wpaicg_openrouter_api_key );
?>" onfocus="unmaskValue(this)" onblur="maskValue(this)" >
                        <a href="https://openrouter.ai/keys" target="_blank"><?php 
echo esc_html__( 'Get Your Api Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <!-- OPENROUTER MODEL LIST -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Model', 'gpt3-ai-content-generator' );
?></label>
                        <select id="wpaicg_openrouter_model" name="wpaicg_openrouter_model" class="specific-select">
                            <?php 
$openrouter_models = get_option( 'wpaicg_openrouter_model_list', [] );
// Group the models by provider
$grouped_models = [];
foreach ( $openrouter_models as $model ) {
    $provider = explode( '/', $model['id'] )[0];
    // Extract the provider name
    if ( !isset( $grouped_models[$provider] ) ) {
        $grouped_models[$provider] = [];
    }
    $grouped_models[$provider][] = $model;
}
// Sort the providers alphabetically
ksort( $grouped_models );
// Display models grouped by provider
foreach ( $grouped_models as $provider => $models ) {
    echo '<optgroup label="' . esc_attr( $provider ) . '">';
    // Sort the models alphabetically by name within each provider
    usort( $models, function ( $a, $b ) {
        return strcmp( $a["name"], $b["name"] );
    } );
    foreach ( $models as $model ) {
        echo '<option value="' . esc_attr( $model['id'] ) . '" ' . selected( $model['id'], get_option( 'wpaicg_openrouter_default_model' ), false ) . '>' . esc_html( $model['name'] ) . '</option>';
    }
    echo '</optgroup>';
}
?>
                        </select>
                        <button id="syncButton" class="button button-primary wpaicg_sync_openrouter_models" type="button" style="line-height: 3.5;height: 40px">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <p></p>
            <div class="advance-button-container">
                <!-- ADVANCE SETTINGS -->
                <div class="advanced-settings" id="toggleSingleSettings" data-target="single-settings-container">
                    <?php 
echo esc_html__( 'Advance Settings', 'gpt3-ai-content-generator' );
?>
                </div>
                <!-- TOGGLE SAFETY SETTINGS BUTTON -->
                <div class="safety-settings" id="toggleGoogleSafetySettings" data-target="google_safety_settings" style="display: none;">
                    <?php 
echo esc_html__( 'Safety Settings', 'gpt3-ai-content-generator' );
?>
                </div>
            </div>
            <div class="single-settings-container" style="display: none;">
                <!-- MAX TOKENS -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Maximum Length', 'gpt3-ai-content-generator' );
?></label>
                        <input id="wpaicg_max_tokens" name="wpaicg_max_tokens" type="number" class="specific-textfield" value="<?php 
echo esc_attr( $current_max_tokens );
?>">
                        <a href="https://docs.aipower.org/docs/ai-engine/openai/max-tokens#adjusting-the-max-tokens-setting" target="_blank">?</a>
                    </div>
                </div>

                <!-- RATE LIMIT -->
                <?php 
$wpaicg_sleep_time = get_option( 'wpaicg_sleep_time', 1 );
?>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Rate Limit Buffer (in seconds)', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpaicg_sleep_time_range" name="wpaicg_sleep_time" type="range" min="1" max="30" value="<?php 
echo esc_attr( $wpaicg_sleep_time );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $wpaicg_sleep_time );
?></output>
                </div>

                <!-- TEMPERATURE -->
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Temperature', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpaicg_temperature_range" name="wpaicg_temperature" type="range" step="0.01" min="0" max="2" value="<?php 
echo esc_attr( $current_temperature );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $current_temperature );
?></output>
                </div>

                <!-- FREQUENCY PENALTY -->
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Frequency Penalty', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpaicg_frequency_range" name="wpaicg_frequency" type="range" step="0.01" min="0" max="2" value="<?php 
echo esc_attr( $current_frequency );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $current_frequency );
?></output>
                </div>

                <!-- PRESENCE PENALTY -->
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Presence Penalty', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpaicg_presence_range" name="wpaicg_presence" type="range" step="0.01" min="0" max="2" value="<?php 
echo esc_attr( $current_presence );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $current_presence );
?></output>
                </div>

                <!-- TOP_P -->
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Top P', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpaicg_top_p_range" name="wpaicg_top_p" type="range" step="0.01" min="0" max="1" value="<?php 
echo esc_attr( $current_top_p );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $current_top_p );
?></output>
                </div>

                <!-- BEST_OF -->
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Best of', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpaicg_best_of" name="wpaicg_best_of" type="range" min="1" max="20" value="<?php 
echo esc_attr( $current_best_of );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $current_best_of );
?></output>
                </div>
            </div>
            <!-- GOOGLE SAFETY SETTINGS -->
            <div id="google_safety_settings" style="display: none;">
                <?php 
$safety_categories = [
    'HARM_CATEGORY_HARASSMENT'        => 'Harassment',
    'HARM_CATEGORY_HATE_SPEECH'       => 'Hate Speech',
    'HARM_CATEGORY_SEXUALLY_EXPLICIT' => 'Sexually Explicit',
    'HARM_CATEGORY_DANGEROUS_CONTENT' => 'Dangerous Content',
];
$thresholds = [
    'BLOCK_NONE'             => 'Block None',
    'BLOCK_ONLY_HIGH'        => 'Block Few',
    'BLOCK_MEDIUM_AND_ABOVE' => 'Block Some',
    'BLOCK_LOW_AND_ABOVE'    => 'Block Most',
];
$google_safety_settings = get_option( 'wpaicg_google_safety_settings', [
    [
        'category'  => 'HARM_CATEGORY_HARASSMENT',
        'threshold' => 'BLOCK_NONE',
    ],
    [
        'category'  => 'HARM_CATEGORY_HATE_SPEECH',
        'threshold' => 'BLOCK_NONE',
    ],
    [
        'category'  => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
        'threshold' => 'BLOCK_NONE',
    ],
    [
        'category'  => 'HARM_CATEGORY_DANGEROUS_CONTENT',
        'threshold' => 'BLOCK_NONE',
    ]
] );
$google_safety_settings = array_column( $google_safety_settings, 'threshold', 'category' );
?>
                <?php 
foreach ( $safety_categories as $category => $label ) {
    ?>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="<?php 
    echo esc_attr( $category );
    ?>"><?php 
    echo esc_html__( $label, 'gpt3-ai-content-generator' );
    ?></label>
                        <select id="<?php 
    echo esc_attr( $category );
    ?>" name="google_safety_settings[<?php 
    echo esc_attr( $category );
    ?>]" class="specific-select">
                            <?php 
    foreach ( $thresholds as $value => $option ) {
        ?>
                                <option value="<?php 
        echo esc_attr( $value );
        ?>" <?php 
        selected( ( isset( $google_safety_settings[$category] ) ? $google_safety_settings[$category] : 'BLOCK_NONE' ), $value );
        ?>>
                                    <?php 
        echo esc_html__( $option, 'gpt3-ai-content-generator' );
        ?>
                                </option>
                            <?php 
    }
    ?>
                        </select>
                    </div>
                </div>
                <?php 
}
?>
            </div>
            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>

        <!-- CONTENT WRITER -->
        <section>
            <div class="href-target" id="content"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers">
                <polygon points="12 2 2 7 12 12 22 7 12 2" />
                <polyline points="2 17 12 22 22 17" />
                <polyline points="2 12 12 17 22 12" />
                </svg>
                <?php 
echo esc_html__( 'Content Writer', 'gpt3-ai-content-generator' );
?>
            </h1>
            <p><?php 
echo esc_html__( 'This tab allows you to set and save default values for both Express Mode and Auto Content Writer. Changes made here will be applied to both modules.', 'gpt3-ai-content-generator' );
?></p>
            <h1><?php 
echo esc_html__( 'Language, Style and Tone', 'gpt3-ai-content-generator' );
?></h1>
            <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                <!-- LANGUAGE -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Language', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpai_language" name="wpai_language" style="width: 162px;">
                            <?php 
foreach ( $languages as $code => $displayName ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $code );
    ?>" <?php 
    echo ( esc_attr( $code ) === $currentLanguage ? 'selected' : '' );
    ?>><?php 
    echo esc_html( $displayName );
    ?></option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
                <!-- STYLE -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Writing Style', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpai_content_style" name="wpai_content_style" style="width: 162px;">
                            <?php 
foreach ( $writing_styles as $code => $displayName ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $code );
    ?>" <?php 
    echo ( esc_attr( $code ) === $currentStyle ? 'selected' : '' );
    ?>><?php 
    echo esc_html( $displayName );
    ?></option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
                <!-- TONE -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Writing Tone', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpai_content_tone" name="wpai_content_tone" style="width: 162px;">
                            <?php 
foreach ( $writing_tones as $code => $displayName ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $code );
    ?>" <?php 
    echo ( esc_attr( $code ) === $currentTone ? 'selected' : '' );
    ?>><?php 
    echo esc_html( $displayName );
    ?></option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
            </div>
            <p></p>
            <h1><?php 
echo esc_html__( 'Headings', 'gpt3-ai-content-generator' );
?></h1>
            <!-- HEADINGS -->
            <div class="unique-page-container">
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Number of Headings', 'gpt3-ai-content-generator' );
?></label>
                    <input id="wpai_number_of_heading" name="wpai_number_of_heading" type="range" min="1" max="15" value="<?php 
echo esc_attr( $current_number_of_heading );
?>" oninput="this.nextElementSibling.value = this.value">
                    <output><?php 
echo esc_attr( $current_number_of_heading );
?></output>
                </div>
            </div>
            <!-- HEADING TAG -->
            <div class="unique-page-container">
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Heading Tag', 'gpt3-ai-content-generator' );
?></label>
                    <select class="specific-select" id="wpai_heading_tag" name="wpai_heading_tag">
                        <?php 
foreach ( $heading_tags as $tag ) {
    ?>
                            <option value="<?php 
    echo esc_attr( $tag );
    ?>" <?php 
    echo ( $tag === $currentTag ? 'selected' : '' );
    ?>>
                                <?php 
    echo $tag;
    ?>
                            </option>
                        <?php 
}
?>
                    </select>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/headings#heading-tag" target="_blank">?</a>
                </div>
            </div>

            <!-- OPTIONS -->
            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'Options', 'gpt3-ai-content-generator' );
?></legend>
                <!-- TABLE OF CONTENTS -->
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_toc" name="wpaicg_toc" value="1" <?php 
checked( 1, $current_wpaicg_toc );
?> />
                    <label><?php 
echo esc_html__( 'Table of Contents', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/table-of-contents#enable-or-disable-toc" target="_blank">?</a>
                </div>
                <!-- ToC Title-->
                <div class="unique-page-container" id="toc_title_container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'ToC Title', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" id="wpaicg_toc_title" name="wpaicg_toc_title" value="<?php 
echo esc_attr( $current_toc_title );
?>" class="specific-textfield">
                    </div>
                </div>
                <!-- ToC Title TAG -->
                <div class="unique-page-container" id="toc_title_tag_container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'ToC Tag', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpaicg_toc_title_tag" name="wpaicg_toc_title_tag">
                            <?php 
foreach ( $heading_tags as $tag ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $tag );
    ?>" <?php 
    echo ( $tag === $current_toc_title_tag ? 'selected' : '' );
    ?>>
                                    <?php 
    echo $tag;
    ?>
                                </option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
                <!-- Add Intro -->
                <div class="nice-form-group">
                    <input type="checkbox" id="wpai_add_intro" name="wpai_add_intro" value="1" <?php 
checked( 1, $current_wpaicg_intro );
?> />
                    <label><?php 
echo esc_html__( 'Introduction', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/additional-content#enable-or-disable-introduction" target="_blank">?</a>
                </div>
                <!-- Hide Introduction Title -->
                <div class="unique-page-container" id="hide_intro_title_container">
                    <div class="nice-form-group">
                        <input type="checkbox" id="wpaicg_hide_introduction" name="wpaicg_hide_introduction" value="1" <?php 
checked( 1, $current_hide_introduction );
?> />
                        <label><?php 
echo esc_html__( 'Hide Introduction Title', 'gpt3-ai-content-generator' );
?></label>
                    </div>
                </div>
                <div class="unique-page-container" id="intro_title_tag_container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Introduction Tag', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpaicg_intro_title_tag" name="wpaicg_intro_title_tag">
                            <?php 
foreach ( $heading_tags as $tag ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $tag );
    ?>" <?php 
    echo ( $tag === $current_intro_title_tag ? 'selected' : '' );
    ?>>
                                    <?php 
    echo $tag;
    ?>
                                </option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
                <!-- Add Conclusion -->
                <div class="nice-form-group">
                    <input type="checkbox" id="wpai_add_conclusion" name="wpai_add_conclusion" value="1" <?php 
checked( 1, $current_wpaicg_conclusion );
?> />
                    <label><?php 
echo esc_html__( 'Conclusion', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/additional-content#enable-or-disable-conclusion" target="_blank">?</a>
                </div>
                <!-- Hide Conclusion Title -->
                <div class="unique-page-container" id="hide_conclusion_title_container">
                    <div class="nice-form-group">
                        <input type="checkbox" id="wpaicg_hide_conclusion" name="wpaicg_hide_conclusion" value="1" <?php 
checked( 1, $current_hide_conclusion );
?> />
                        <label><?php 
echo esc_html__( 'Hide Conclusion Title', 'gpt3-ai-content-generator' );
?></label>
                    </div>
                </div>
                <div class="unique-page-container" id="conclusion_title_tag_container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Conclusion Tag', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpaicg_conclusion_title_tag" name="wpaicg_conclusion_title_tag">
                            <?php 
foreach ( $heading_tags as $tag ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $tag );
    ?>" <?php 
    echo ( $tag === $current_conclusion_title_tag ? 'selected' : '' );
    ?>>
                                    <?php 
    echo $tag;
    ?>
                                </option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpai_add_tagline" name="wpai_add_tagline" value="1" <?php 
checked( 1, $current_tagline );
?> />
                    <label><?php 
echo esc_html__( 'Tagline', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/additional-content#enable-or-disable-tagline" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpai_modify_headings" name="wpai_modify_headings" value="1" <?php 
checked( 1, $current_outline_editor );
?> />
                    <label><?php 
echo esc_html__( 'Outline Editor', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/headings#outline-editor" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                <?php 
?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
echo esc_html__( 'Q & A', 'gpt3-ai-content-generator' );
?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/qa#enable-or-disable-qa" target="_blank">?</a>
                        <a href="<?php 
echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
?>" class="pro-feature-label"><?php 
echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
?></a>
                        <?php 
?>
                </div>
                <div class="nice-form-group">
                <?php 
?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
echo esc_html__( 'Bold Keywords', 'gpt3-ai-content-generator' );
?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/keywords#add-keywords" target="_blank">?</a>
                        <a href="<?php 
echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
?>" class="pro-feature-label"><?php 
echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
?></a>
                        <?php 
?>
                </div>
            </fieldset>

            <!-- CTA POSITION -->
            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'Call to Action Position', 'gpt3-ai-content-generator' );
?></legend>
                <div class="nice-form-group">
                    <input type="radio" id="cta_pos_beg" name="wpai_cta_pos" value="beg" <?php 
checked( $current_cta_pos, 'beg' );
?>/>
                    <label><?php 
echo esc_html__( 'Beginning', 'gpt3-ai-content-generator' );
?></label>
                </div>
                <div class="nice-form-group">
                    <input type="radio" id="cta_pos_end" name="wpai_cta_pos" value="end" <?php 
checked( $current_cta_pos, 'end' );
?>/>
                    <label><?php 
echo esc_html__( 'End', 'gpt3-ai-content-generator' );
?></label>
                </div>
            </fieldset>
            
            <!-- CUSTOM PROMPT -->
            <div class="nice-form-group">
                <input <?php 
echo ( $wpaicg_content_custom_prompt_enable ? ' checked' : '' );
?> type="checkbox" class="wpaicg_meta_custom_prompt_enable" name="wpaicg_content_custom_prompt_enable">
                <label><?php 
echo esc_html__( 'Custom Prompt', 'gpt3-ai-content-generator' );
?></label>
                <a href="https://docs.aipower.org/docs/content-writer/express-mode/custom-prompt" target="_blank">?</a>
            </div>
            <p></p>
            <div class="wpaicg_meta_custom_prompt_box" style="<?php 
echo ( isset( $wpaicg_content_custom_prompt_enable ) && $wpaicg_content_custom_prompt_enable ? '' : 'display:none' );
?>">
                <textarea rows="20" class="wpaicg_meta_custom_prompt" name="wpaicg_content_custom_prompt"><?php 
echo esc_html( str_replace( "\\", '', $wpaicg_content_custom_prompt ) );
?></textarea>
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <div>
                        <?php 
    echo sprintf(
        esc_html__( 'Make sure to include %s in your prompt. You can also incorporate %s and %s to further customize your prompt.', 'gpt3-ai-content-generator' ),
        '<code>[title]</code>',
        '<code>[keywords_to_include]</code>',
        '<code>[keywords_to_avoid]</code>'
    );
    ?>
                    </div>
                <?php 
} else {
    ?>
                    <div>
                        <?php 
    echo sprintf( esc_html__( 'Ensure %s is included in your prompt.', 'gpt3-ai-content-generator' ), '<code>[title]</code>' );
    ?>
                    </div>
                <?php 
}
?>
                <button style="color: #fff;background: #df0707;border-color: #df0707;" data-prompt="<?php 
echo esc_html( \WPAICG\WPAICG_Custom_Prompt::get_instance()->wpaicg_default_custom_prompt );
?>" class="button wpaicg_meta_custom_prompt_reset" type="button"><?php 
echo esc_html__( 'Reset', 'gpt3-ai-content-generator' );
?></button>
                <div class="wpaicg_meta_custom_prompt_auto_error"></div>
            </div>
            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>

        <!-- SEO -->
        <section>
            <div class="href-target" id="seo"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-justify">
                <line x1="21" y1="10" x2="3" y2="10" />
                <line x1="21" y1="6" x2="3" y2="6" />
                <line x1="21" y1="14" x2="3" y2="14" />
                <line x1="21" y1="18" x2="3" y2="18" />
                </svg>
                <?php 
echo esc_html__( 'SEO', 'gpt3-ai-content-generator' );
?>
            </h1>
            <!-- SEO -->
            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'Meta Description', 'gpt3-ai-content-generator' );
?></legend>
                <div class="nice-form-group">
                    <input type="checkbox" id="_wpaicg_seo_meta_desc" name="_wpaicg_seo_meta_desc" value="1" <?php 
checked( 1, $_wpaicg_seo_meta_desc );
?> />
                    <label><?php 
echo esc_html__( 'Generate Meta Description', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#enable-or-disable-meta-description-generation" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="_wpaicg_seo_meta_tag" name="_wpaicg_seo_meta_tag" value="1" <?php 
checked( 1, $_wpaicg_seo_meta_tag );
?> />
                    <label><?php 
echo esc_html__( 'Include Meta in the Header', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#meta-description-in-html" target="_blank">?</a>
                </div>
                <?php 
foreach ( $seo_plugins_options as $seo_plugin ) {
    if ( is_plugin_active( $seo_plugin['plugin'] ) ) {
        $option_value = get_option( $seo_plugin['option_name'], false );
        ?>
                        <div class="nice-form-group">
                            <input <?php 
        checked( $option_value, true );
        ?> id="<?php 
        echo esc_attr( $seo_plugin['option_name'] );
        ?>" type="checkbox" name="<?php 
        echo esc_attr( $seo_plugin['option_name'] );
        ?>" value="1">
                            <label><?php 
        echo esc_html( $seo_plugin['label'] );
        ?></label>
                            <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#integrations" target="_blank">?</a>
                        </div>
                        <?php 
    }
}
?>
            </fieldset>
            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'SEO Optimization', 'gpt3-ai-content-generator' );
?></legend>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_shorten_url" name="_wpaicg_shorten_url" value="1" <?php 
    checked( 1, $_wpaicg_shorten_url );
    ?> />
                    <label><?php 
    echo esc_html__( 'Shorten URL', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Shorten URL', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_gen_title_from_keywords" name="_wpaicg_gen_title_from_keywords" value="1" <?php 
    checked( 1, $_wpaicg_gen_title_from_keywords );
    ?> onchange="handleTitleFromKeywordsChange(this)" />
                    <label><?php 
    echo esc_html__( 'Generate Title from Keywords', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Generate Title from Keywords', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_original_title_in_prompt" name="_wpaicg_original_title_in_prompt" value="1" <?php 
    checked( 1, $_wpaicg_original_title_in_prompt );
    ?> <?php 
    if ( !$_wpaicg_gen_title_from_keywords ) {
        echo 'disabled';
    }
    ?> />
                    <label><?php 
    echo esc_html__( 'Include Original Title in the Prompt', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Include Original Title in the Prompt', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_focus_keyword_in_url" name="_wpaicg_focus_keyword_in_url" value="1" <?php 
    checked( 1, $_wpaicg_focus_keyword_in_url );
    ?> />
                    <label><?php 
    echo esc_html__( 'Enforce Focus Keyword in URL', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Enforce Focus Keyword in URL', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_sentiment_in_title" name="_wpaicg_sentiment_in_title" value="1" <?php 
    checked( 1, $_wpaicg_sentiment_in_title );
    ?> />
                    <label><?php 
    echo esc_html__( 'Use Sentiment in Title', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Use Sentiment in Title', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_power_word_in_title" name="_wpaicg_power_word_in_title" value="1" <?php 
    checked( 1, $_wpaicg_power_word_in_title );
    ?> />
                    <label><?php 
    echo esc_html__( 'Use Power Word in Title', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Use Power Word in Title', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/content-writer/express-mode/seo#seo-optimization" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
            </fieldset>
            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>

        <!-- WOOCOMMERCE -->
        <section>
            <div class="href-target" id="woocommerce"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square">
                <polyline points="9 11 12 14 22 4" />
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                </svg>
                <?php 
echo esc_html__( 'WooCommerce', 'gpt3-ai-content-generator' );
?>
            </h1>

            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'Product Writer', 'gpt3-ai-content-generator' );
?></legend>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_woo_generate_title" name="wpaicg_woo_generate_title" value="1" <?php 
checked( 1, $wpaicg_woo_generate_title );
?> />
                    <label><?php 
echo esc_html__( 'Product Title', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#woocommerce-product-writer" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_woo_generate_description" name="wpaicg_woo_generate_description" value="1" <?php 
checked( 1, $wpaicg_woo_generate_description );
?> />
                    <label><?php 
echo esc_html__( 'Full Product Description', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#woocommerce-product-writer" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_woo_generate_short" name="wpaicg_woo_generate_short" value="1" <?php 
checked( 1, $wpaicg_woo_generate_short );
?> />
                    <label><?php 
echo esc_html__( 'Short Product Description', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#woocommerce-product-writer" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_woo_generate_tags" name="wpaicg_woo_generate_tags" value="1" <?php 
checked( 1, $wpaicg_woo_generate_tags );
?> />
                    <label><?php 
echo esc_html__( 'Product Tags', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#woocommerce-product-writer" target="_blank">?</a>
                </div>
            </fieldset>

            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'SEO Optimization', 'gpt3-ai-content-generator' );
?></legend>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_woo_meta_description" name="wpaicg_woo_meta_description" value="1" <?php 
checked( 1, $wpaicg_woo_meta_description );
?> />
                    <label><?php 
echo esc_html__( 'Meta Description', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#woocommerce-product-writer" target="_blank">?</a>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="_wpaicg_shorten_woo_url" name="_wpaicg_shorten_woo_url" value="1" <?php 
    checked( 1, $_wpaicg_shorten_woo_url );
    ?> />
                    <label><?php 
    echo esc_html__( 'Shorten Product URL', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#shorten-url" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Shorten Product URL', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/woocommerce#shorten-url" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="wpaicg_generate_woo_focus_keyword" name="wpaicg_generate_woo_focus_keyword" value="1" <?php 
    checked( 1, $wpaicg_generate_woo_focus_keyword );
    ?> />
                    <label><?php 
    echo esc_html__( 'Generate Focus Keyword', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#focus-keyword" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Generate Focus Keyword', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/woocommerce#focus-keyword" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
                <div class="nice-form-group">
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                    <input type="checkbox" id="wpaicg_enforce_woo_keyword_in_url" name="wpaicg_enforce_woo_keyword_in_url" value="1" <?php 
    checked( 1, $wpaicg_enforce_woo_keyword_in_url );
    ?> />
                    <label><?php 
    echo esc_html__( 'Enforce Focus Keyword in URL', 'gpt3-ai-content-generator' );
    ?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#enforce-focus-keyword-in-url" target="_blank">?</a>
                    <?php 
} else {
    ?>
                        <input type="checkbox" value="0" disabled>
                        <label><?php 
    echo esc_html__( 'Enforce Focus Keyword in URL', 'gpt3-ai-content-generator' );
    ?></label>
                        <a href="https://docs.aipower.org/docs/woocommerce#enforce-focus-keyword-in-url" target="_blank">?</a>
                        <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=wpaicg-pricing' ) );
    ?>" class="pro-feature-label"><?php 
    echo esc_html__( 'Pro', 'gpt3-ai-content-generator' );
    ?></a>
                        <?php 
}
?>
                </div>
            </fieldset>

            <fieldset class="nice-form-group">
            <legend><?php 
echo esc_html__( 'Prompt Design', 'gpt3-ai-content-generator' );
?></legend>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_woo_custom_prompt" name="wpaicg_woo_custom_prompt" class="wpaicg_woo_custom_prompt" value="1" <?php 
checked( 1, $wpaicg_woo_custom_prompt );
?> />
                    <label><?php 
echo esc_html__( 'Use Custom Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <a href="https://docs.aipower.org/docs/woocommerce#customizing-prompts" target="_blank">?</a>
                </div>
            </fieldset>

            <div <?php 
echo ( $wpaicg_woo_custom_prompt ? '' : ' style="display:none"' );
?> class="wpaicg_woo_custom_prompts">
                <div class="nice-form-group">
                    <p><?php 
echo esc_html__( 'You can use these shortcodes in your custom prompts:', 'gpt3-ai-content-generator' );
?> </p>
                    <div class="toggle-shortcode-small">[current_short_description]</div>
                    <div class="toggle-shortcode-small">[current_full_description]</div>
                    <div class="toggle-shortcode-small">[current_attributes]</div>
                    <div class="toggle-shortcode-small">[current_categories]</div>
                    <div class="toggle-shortcode-small">[current_price]</div>
                    <div class="toggle-shortcode-small">[current_weight]</div>
                    <div class="toggle-shortcode-small">[current_length]</div>
                    <div class="toggle-shortcode-small">[current_width]</div>
                    <div class="toggle-shortcode-small">[current_height]</div>
                    <div class="toggle-shortcode-small">[current_sku]</div>
                    <div class="toggle-shortcode-small">[current_purchase_note]</div>
                    <div class="toggle-shortcode-small">[current_focus_keywords]</div>
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Title Prompt Template', 'gpt3-ai-content-generator' );
?></label>
                    <select id="titlePromptTemplates">
                        <option value="0">--Select a Template--</option>
                        <option value="1">Incorporate Key Features</option>
                        <option value="2">Highlight Unique Selling Points</option>
                        <option value="3">Engage and Inform</option>
                        <option value="4">Keyword Rich</option>
                        <option value="5">Concise Yet Comprehensive</option>
                    </select>
                </div>
                <p></p>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Title Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <textarea rows="5" type="text" name="wpaicg_woo_custom_prompt_title"><?php 
echo esc_html( $wpaicg_woo_custom_prompt_title );
?></textarea>
                </div>
                <!-- Added Short Description Prompt Templates Dropdown -->
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Short Description Prompt Template', 'gpt3-ai-content-generator' );
?></label>
                    <select id="ShortDescriptionPromptTemplates">
                        <option value="0">--Select a Template--</option>
                        <option value="1">Highlight Features and Benefits</option>
                        <option value="2">Solve a Problem</option>
                        <option value="3">Emphasize Uniqueness</option>
                        <option value="4">Invoke Emotion</option>
                        <option value="5">SEO-Focused</option>
                    </select>
                </div>
                <p></p>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Short Description Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <textarea rows="10" type="text" name="wpaicg_woo_custom_prompt_short"><?php 
echo esc_html( $wpaicg_woo_custom_prompt_short );
?></textarea>
                </div>
                <p></p>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Description Prompt Template', 'gpt3-ai-content-generator' );
?></label>
                    <select id="DescriptionPromptTemplates">
                        <option value="0">--Select a Template--</option>
                        <option value="1">Highlight Key Features and Benefits</option>
                        <option value="2">Emphasize Practicality and Usability</option>
                        <option value="3">Evoke Emotional Connection</option>
                        <option value="4">Showcase Unique Selling Points</option>
                        <option value="5">Concise and Direct</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Description Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <textarea rows="10" type="text" name="wpaicg_woo_custom_prompt_description"><?php 
echo esc_html( $wpaicg_woo_custom_prompt_description );
?></textarea>
                </div>
                <p></p>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Meta Description Prompt Template', 'gpt3-ai-content-generator' );
?></label>
                    <select id="MetaDescriptionPromptTemplates">
                        <option value="0">--Select a Template--</option>
                        <option value="1">Focused on Key Features and Benefits</option>
                        <option value="2">Problem-Solving Angle</option>
                        <option value="3">Emotional Appeal</option>
                        <option value="4">Urgency and Exclusivity</option>
                        <option value="5">Direct and Informative</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Meta Description Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <textarea rows="5" type="text" name="wpaicg_woo_custom_prompt_meta"><?php 
echo esc_html( $wpaicg_woo_custom_prompt_meta );
?></textarea>
                </div>
                <p></p>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Tag Prompt Template', 'gpt3-ai-content-generator' );
?></label>
                    <select id="TagsPromptTemplates">
                        <option value="0">--Select a Template--</option>
                        <option value="1">Highly Relevant and SEO-Optimized</option>
                        <option value="2">Increase Discoverability</option>
                        <option value="3">Describe Features and Benefits</option>
                        <option value="4">Encompass Functionality and Use-Cases</option>
                        <option value="5">SEO-Optimized High-Search-Volume Keywords</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Tag Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <textarea rows="5" type="text" name="wpaicg_woo_custom_prompt_keywords"><?php 
echo esc_html( $wpaicg_woo_custom_prompt_keywords );
?></textarea>
                </div>
                <p></p>
                <?php 
if ( \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ) {
    ?>
                <!-- Added Focus Keyword Prompt Templates Dropdown -->
                <div class="nice-form-group">
                    <label><?php 
    echo esc_html__( 'Focus Keyword Prompt Template', 'gpt3-ai-content-generator' );
    ?></label>
                    <select id="FocusKeywordPromptTemplates">
                        <option value="0">--Select a Template--</option>
                        <option value="1">Generate Single Keyword</option>
                        <option value="2">Generate Multiple Keywords</option>
                        <option value="3">Niche-Specific and Unique</option>
                        <option value="4">Trending or Seasonal</option>
                        <option value="5">Competitor Analysis</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php 
    echo esc_html__( 'Focus Keyword Prompt', 'gpt3-ai-content-generator' );
    ?></label>
                    <textarea rows="5" type="text" name="wpaicg_woo_custom_prompt_focus_keyword"><?php 
    echo esc_html( $wpaicg_woo_custom_prompt_focus_keyword );
    ?></textarea>
                </div>
                <?php 
}
?>
            </div>
            
            <fieldset class="nice-form-group">
                <legend><?php 
echo esc_html__( 'Token Sale', 'gpt3-ai-content-generator' );
?></legend>
                <small><?php 
echo esc_html__( "Automatically credit tokens to the user's account based on the order status.", 'gpt3-ai-content-generator' );
?></small>
                <div class="nice-form-group">
                    <input type="radio" id="order_status_completed" name="wpaicg_order_status_token" value="completed" <?php 
checked( $wpaicg_order_status_token, 'completed' );
?>/>
                    <label for="order_status_completed"><?php 
echo esc_html__( 'Completed', 'gpt3-ai-content-generator' );
?></label>
                </div>
                <div class="nice-form-group">
                    <input type="radio" id="order_status_processing" name="wpaicg_order_status_token" value="processing" <?php 
checked( $wpaicg_order_status_token, 'processing' );
?>/>
                    <label for="order_status_processing"><?php 
echo esc_html__( 'Processing', 'gpt3-ai-content-generator' );
?></label>
                </div>
            </fieldset>
            
            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>
        
        <!-- IMAGE -->
        <section>
            <div class="href-target" id="image"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg)" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-columns">
                <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7m0-18H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7m0-18v18" />
                </svg>
                <?php 
echo esc_html__( 'Image', 'gpt3-ai-content-generator' );
?>
            </h1>
            <div class="unique-page-container">
                <div class="nice-form-group">
                    <label for="image_source"><?php 
echo esc_html__( 'Image Source', 'gpt3-ai-content-generator' );
?></label>
                    <select id="image_source" name="wpaicg_image_source" class="specific-select"><?php 
echo get_image_source_options( $wpaicg_image_source, 'dalle3' );
?></select>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/images#adding-an-image" target="_blank">?</a>
                </div>
            </div>
            <div class="unique-page-container">
                <div class="nice-form-group">
                    <label for="featured_image_source" ><?php 
echo esc_html__( 'Featured Image Source', 'gpt3-ai-content-generator' );
?></label>
                    <select id="featured_image_source" name="wpaicg_featured_image_source" class="specific-select"><?php 
echo get_image_source_options( $wpaicg_featured_image_source, 'dalle3' );
?></select>
                    <a href="https://docs.aipower.org/docs/content-writer/express-mode/images#setting-featured-image" target="_blank">?</a>
                </div>
            </div>
            <p></p>
            <!-- Dall-E Settings Button -->
            <div class="advanced-settings" id="toggleDallESettings" data-target="dalle-settings-container">
                <?php 
echo esc_html__( 'Dall-E', 'gpt3-ai-content-generator' );
?>
            </div>
            <!-- Pexel Settings Button -->
            <div class="advanced-settings" id="togglePexelSettings" data-target="pexel-settings-container">
                <?php 
echo esc_html__( 'Pexels', 'gpt3-ai-content-generator' );
?>
            </div>
            <!-- Pixabay Settings Button -->
            <div class="advanced-settings" id="togglePixabaySettings" data-target="pixabay-settings-container">
                <?php 
echo esc_html__( 'Pixabay', 'gpt3-ai-content-generator' );
?>
            </div>
            <!-- Stable Diffusion Settings Button -->
            <div class="advanced-settings" id="toggleSDSettings" data-target="sd-settings-container">
                <?php 
echo esc_html__( 'Stable Diffusion', 'gpt3-ai-content-generator' );
?>
            </div>
            <!-- Dall-E Settings Container -->
            <div class="dalle-settings-container" style="display: none;">
                <!-- Dall-E Settings Content Here -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="wpaicg_img_size"><?php 
echo esc_html__( 'Size', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpaicg_img_size" name="wpaicg_img_size">
                            <?php 
foreach ( $image_sizes as $code => $displayName ) {
    ?>
                                <option value="<?php 
    echo esc_attr( $code );
    ?>" <?php 
    echo ( esc_attr( $code ) === $current_img_size ? 'selected' : '' );
    ?>><?php 
    echo esc_html( $displayName );
    ?></option>
                            <?php 
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="wpaicg_dalle_type"><?php 
echo esc_html__( 'Type', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="wpaicg_dalle_type" name="wpaicg_dalle_type">
                            <option value="vivid" <?php 
selected( $wpaicg_dalle_type, 'vivid' );
?>><?php 
echo esc_html__( 'Vivid', 'gpt3-ai-content-generator' );
?></option>
                            <option value="natural" <?php 
selected( $wpaicg_dalle_type, 'natural' );
?>><?php 
echo esc_html__( 'Natural', 'gpt3-ai-content-generator' );
?></option>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="_wpaicg_image_style"><?php 
echo esc_html__( 'Style', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" id="label_img_style" name="_wpaicg_image_style" >
                            <?php 
foreach ( $image_style_options as $value => $label ) {
    $selected = ( esc_html( $_wpaicg_image_style ) == $value ? ' selected' : '' );
    echo "<option value=\"{$value}\"{$selected}>{$label}</option>";
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="artist"><?php 
echo esc_html__( 'Artist', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[artist]" id="artist">
                            <?php 
foreach ( $wpaicg_painter_data['painters'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['artist'] ) && $wpaicg_custom_image_settings['artist'] == $value || (!isset( $wpaicg_custom_image_settings['artist'] ) && $value) == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="photography_style"><?php 
echo esc_html__( 'Photography', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[photography_style]" id="photography_style">
                            <?php 
foreach ( $wpaicg_photo_data['photography_style'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['photography_style'] ) && $wpaicg_custom_image_settings['photography_style'] == $value || !isset( $wpaicg_custom_image_settings['photography_style'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="lighting"><?php 
echo esc_html__( 'Lighting', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[lighting]" id="lighting">
                            <?php 
foreach ( $wpaicg_photo_data['lighting'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['lighting'] ) && $wpaicg_custom_image_settings['lighting'] == $value || !isset( $wpaicg_custom_image_settings['lighting'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="subject"><?php 
echo esc_html__( 'Subject', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[subject]" id="subject">
                            <?php 
foreach ( $wpaicg_photo_data['subject'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['subject'] ) && $wpaicg_custom_image_settings['subject'] == $value || !isset( $wpaicg_custom_image_settings['subject'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="camera_settings"><?php 
echo esc_html__( 'Camera', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[camera_settings]" id="camera_settings">
                            <?php 
foreach ( $wpaicg_photo_data['camera_settings'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['camera_settings'] ) && $wpaicg_custom_image_settings['camera_settings'] == $value || !isset( $wpaicg_custom_image_settings['camera_settings'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="composition"><?php 
echo esc_html__( 'Composition', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[composition]" id="composition">
                            <?php 
foreach ( $wpaicg_photo_data['composition'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['composition'] ) && $wpaicg_custom_image_settings['composition'] == $value || !isset( $wpaicg_custom_image_settings['composition'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="resolution"><?php 
echo esc_html__( 'Resolution', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[resolution]" id="resolution">
                            <?php 
foreach ( $wpaicg_photo_data['resolution'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['resolution'] ) && $wpaicg_custom_image_settings['resolution'] == $value || !isset( $wpaicg_custom_image_settings['resolution'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="color"><?php 
echo esc_html__( 'Color', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[color]" id="color">
                            <?php 
foreach ( $wpaicg_photo_data['color'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['color'] ) && $wpaicg_custom_image_settings['color'] == $value || !isset( $wpaicg_custom_image_settings['color'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label for="special_effects"><?php 
echo esc_html__( 'Special Effects', 'gpt3-ai-content-generator' );
?></label>
                        <select class="specific-select" name="wpaicg_custom_image_settings[special_effects]" id="special_effects">
                            <?php 
foreach ( $wpaicg_photo_data['special_effects'] as $key => $value ) {
    echo '<option' . (( isset( $wpaicg_custom_image_settings['special_effects'] ) && $wpaicg_custom_image_settings['special_effects'] == $value || !isset( $wpaicg_custom_image_settings['special_effects'] ) && $value == 'None' ? ' selected' : '' )) . ' value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Pexel Settings Container -->
            <div class="pexel-settings-container" style="display: none;">
                <!-- Pexel Settings Content Here -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input value="<?php 
echo esc_html( $wpaicg_pexels_api );
?>" type="text" name="wpaicg_pexels_api" id="wpaicg_pexels_api" class="specific-textfield" onfocus="unmaskValue(this)" onblur="maskValue(this)">
                        <a href="https://www.pexels.com/api/new/" target="_blank"><?php 
echo esc_html__( 'Get API Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Orientation', 'gpt3-ai-content-generator' );
?></label>
                        <select id="wpaicg_pexels_orientation" name="wpaicg_pexels_orientation" class="specific-select">
                            <option value=""><?php 
echo esc_html__( 'None', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pexels_orientation == 'landscape' ? ' selected' : '' );
?> value="landscape"><?php 
echo esc_html__( 'Landscape', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pexels_orientation == 'portrait' ? ' selected' : '' );
?> value="portrait"><?php 
echo esc_html__( 'Portrait', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pexels_orientation == 'square' ? ' selected' : '' );
?> value="square"><?php 
echo esc_html__( 'Square', 'gpt3-ai-content-generator' );
?></option>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Size', 'gpt3-ai-content-generator' );
?></label>
                        <select id="wpaicg_pexels_size" name="wpaicg_pexels_size" class="specific-select">
                            <option value=""><?php 
echo esc_html__( 'None', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pexels_size == 'large' ? ' selected' : '' );
?> value="large"><?php 
echo esc_html__( 'Large', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pexels_size == 'medium' ? ' selected' : '' );
?> value="medium"><?php 
echo esc_html__( 'Medium', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pexels_size == 'small' ? ' selected' : '' );
?> value="small"><?php 
echo esc_html__( 'Small', 'gpt3-ai-content-generator' );
?></option>
                        </select>
                    </div>
                </div>
                <div class="nice-form-group">
                    <input type="checkbox" id="wpaicg_pexels_enable_prompt" name="wpaicg_pexels_enable_prompt" value="1" <?php 
checked( 1, $wpaicg_pexels_enable_prompt );
?> />
                    <label><?php 
echo esc_html__( 'Use Keyword', 'gpt3-ai-content-generator' );
?></label>
                   <p>
                    <small><?php 
echo esc_html__( 'When enabled, AI picks the main keyword from the title to find relevant images.', 'gpt3-ai-content-generator' );
?></small>
                    </p>
                </div>
                <div class="wpcgai_form_row wpaicg_pexels_custom_prompt" style="display:none">
                    <div class="nice-form-group">
                        <textarea id="wpaicg_pexels_custom_prompt" rows="5" name="wpaicg_pexels_custom_prompt"><?php 
echo esc_html( $wpaicg_pexels_custom_prompt );
?></textarea>
                    </div>
                </div>
            </div>
            <!-- Pixabay Settings Container -->
            <div class="pixabay-settings-container" style="display: none;">
                <!-- Pixabay Settings Content Here -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input value="<?php 
echo esc_html( $wpaicg_pixabay_api );
?>" type="text" name="wpaicg_pixabay_api" id="wpaicg_pixabay_api" class="specific-textfield" onfocus="unmaskValue(this)" onblur="maskValue(this)">
                        <a href="https://pixabay.com/api/docs/" target="_blank"><?php 
echo esc_html__( 'Get API Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Language', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_pixabay_language" id="wpaicg_pixabay_language" class="specific-select">
                            <?php 
foreach ( \WPAICG\WPAICG_Generator::get_instance()->pixabay_languages as $key => $pixabay_language ) {
    echo '<option' . (( $wpaicg_pixabay_language == $key ? ' selected' : '' )) . ' value="' . esc_html( $key ) . '">' . esc_html( $pixabay_language ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Type', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_pixabay_type" id="wpaicg_pixabay_type" class="specific-select">
                            <option <?php 
echo ( $wpaicg_pixabay_type == 'all' ? ' selected' : '' );
?> value="all"><?php 
echo esc_html__( 'All', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pixabay_type == 'photo' ? ' selected' : '' );
?> value="photo"><?php 
echo esc_html__( 'Photo', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pixabay_type == 'illustration' ? ' selected' : '' );
?> value="illustration"><?php 
echo esc_html__( 'Illustration', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pixabay_type == 'vector' ? ' selected' : '' );
?> value="vector"><?php 
echo esc_html__( 'Vector', 'gpt3-ai-content-generator' );
?></option>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Orientation', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_pixabay_orientation" id="wpaicg_pixabay_orientation" class="specific-select">
                            <option <?php 
echo ( $wpaicg_pixabay_orientation == 'all' ? ' selected' : '' );
?> value="all"><?php 
echo esc_html__( 'All', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pixabay_orientation == 'horizontal' ? ' selected' : '' );
?> value="horizontal"><?php 
echo esc_html__( 'Horizontal', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pixabay_orientation == 'vertical' ? ' selected' : '' );
?> value="vertical"><?php 
echo esc_html__( 'Vertical', 'gpt3-ai-content-generator' );
?></option>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Order', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_pixabay_order" id="wpaicg_pixabay_order" class="specific-select">
                            <option <?php 
echo ( $wpaicg_pixabay_order == 'popular' ? ' selected' : '' );
?> value="popular"><?php 
echo esc_html__( 'Popular', 'gpt3-ai-content-generator' );
?></option>
                            <option <?php 
echo ( $wpaicg_pixabay_order == 'latest' ? ' selected' : '' );
?> value="latest"><?php 
echo esc_html__( 'Latest', 'gpt3-ai-content-generator' );
?></option>
                        </select>
                    </div>
                </div>
                <div class="nice-form-group">
                    <input <?php 
echo ( $wpaicg_pixabay_enable_prompt ? ' checked' : '' );
?> type="checkbox" name="wpaicg_pixabay_enable_prompt" value="1" id="wpaicg_pixabay_enable_prompt">
                    <label><?php 
echo esc_html__( 'Use Keyword', 'gpt3-ai-content-generator' );
?></label>
                   <p>
                    <small><?php 
echo esc_html__( 'When enabled, AI picks the main keyword from the title to find relevant images.', 'gpt3-ai-content-generator' );
?></small>
                    </p>
                </div>
                <div class="wpcgai_form_row wpaicg_pixabay_custom_prompt" style="display:none">
                    <div class="nice-form-group">
                        <textarea id="wpaicg_pixabay_custom_prompt" rows="5" name="wpaicg_pixabay_custom_prompt"><?php 
echo esc_html( $wpaicg_pixabay_custom_prompt );
?></textarea>
                    </div>
                </div>
            </div>
            <!-- Stable Diffusion Settings Container -->
            <div class="sd-settings-container" style="display: none;">
                <!-- Stable Diffusion Settings Content Here -->
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Api Key', 'gpt3-ai-content-generator' );
?></label>
                        <input value="<?php 
echo esc_html( $wpaicg_sd_api_key );
?>" type="text" name="wpaicg_sd_api_key" id="wpaicg_sd_api_key" class="specific-textfield" onfocus="unmaskValue(this)" onblur="maskValue(this)">
                        <a href="https://replicate.com/account" target="_blank"><?php 
echo esc_html__( 'Get API Key', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Version', 'gpt3-ai-content-generator' );
?></label>
                        <input value="<?php 
echo esc_html( $wpaicg_sd_api_version );
?>" type="text" name="wpaicg_sd_api_version" class="specific-textfield" placeholder="<?php 
echo esc_html__( 'Leave blank for default', 'gpt3-ai-content-generator' );
?>">
                        <a href="https://replicate.com/stability-ai/stable-diffusion/versions" target="_blank"><?php 
echo esc_html__( 'Get Version', 'gpt3-ai-content-generator' );
?></a>
                    </div>
                </div>
            </div>

            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>
        
        <!-- AI  ASSISTANT -->
        <section>
            <div class="href-target" id="assistant"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-feather">
                <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z" />
                <line x1="16" y1="8" x2="2" y2="22" />
                <line x1="17.5" y1="15" x2="9" y2="15" />
                </svg>
                <?php 
echo esc_html__( 'AI Assistant', 'gpt3-ai-content-generator' );
?>
            </h1>
            <p><?php 
echo esc_html__( 'AI Assistant is a feature that allows you to add a button to the WordPress editor that will help you to create content. It is compatible with both Gutenberg and Classic Editor.', 'gpt3-ai-content-generator' );
?></p>
            <p><?php 
echo esc_html__( 'Use the form below to add, modify, or remove menus as needed.', 'gpt3-ai-content-generator' );
?></p>
            <div class="nice-form-group editor-button-group">
                <?php 
foreach ( $wpaicg_editor_button_menus as $index => $menu ) {
    ?>
                    <a href="#" class="editor-settings editor-settings-link" data-index="<?php 
    echo esc_attr( $index );
    ?>">
                        <?php 
    echo esc_html__( 'Menu', 'gpt3-ai-content-generator' ) . ' ' . esc_html( $index + 1 );
    ?>
                    </a>
                    <div class="assistant-delete-menu-item" data-index="<?php 
    echo esc_attr( $index );
    ?>">x</div>
                    <!-- Hidden inputs to ensure all data is submitted -->
                    <input type="hidden" name="wpaicg_editor_button_menus[<?php 
    echo $index;
    ?>][name]" value="<?php 
    echo esc_attr( $menu['name'] );
    ?>">
                    <input type="hidden" name="wpaicg_editor_button_menus[<?php 
    echo $index;
    ?>][prompt]" value="<?php 
    echo esc_attr( $menu['prompt'] );
    ?>">
                <?php 
}
?>
            </div>
            <div class="nice-form-group newitem">
                <button type="button" class="button button-primary button-large" id="add-assistant-menu-item"><?php 
echo esc_html__( 'Add Item', 'gpt3-ai-content-generator' );
?></button>
                <button type="button" class="button button-primary button-large" id="toggle-delete-menu-item"><?php 
echo esc_html__( 'Delete Item', 'gpt3-ai-content-generator' );
?></button>
            </div>
            <div id="assistant-menu-details" style="display:none;">
                <div class="nice-form-group">
                    <label for="assistant-menu-name"><?php 
echo esc_html__( 'Menu Name', 'gpt3-ai-content-generator' );
?></label>
                    <input type="text" id="assistant-menu-name" name="" value="">
                </div>
                <div class="nice-form-group">
                    <label for="assistant-menu-prompt"><?php 
echo esc_html__( 'Menu Prompt', 'gpt3-ai-content-generator' );
?></label>
                    <textarea rows="10" id="assistant-menu-prompt" name="" value=""></textarea>
                    <small>Make sure to include <code>[text]</code> in your prompt.</small>
                </div>
            </div>
            <div class="nice-form-group">
                <label><?php 
echo esc_html__( 'Content Position', 'gpt3-ai-content-generator' );
?></label>
                <select class="regular-text" name="wpaicg_editor_change_action">
                    <option <?php 
echo ( $wpaicg_editor_change_action == 'below' ? ' selected' : '' );
?> value="below"><?php 
echo esc_html__( 'Below', 'gpt3-ai-content-generator' );
?></option>
                    <option <?php 
echo ( $wpaicg_editor_change_action == 'above' ? ' selected' : '' );
?> value="above"><?php 
echo esc_html__( 'Above', 'gpt3-ai-content-generator' );
?></option>
                </select>
            </div>
            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>
        
        <!-- TOOLS -->
        <section>
            <div class="href-target" id="tools"></div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layout">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
                <?php 
echo esc_html__( 'Tools', 'gpt3-ai-content-generator' );
?>
            </h1>
            <p></p>
            <!-- Comment Settings -->
            <div class="advanced-settings" data-target="comment-settings-container">
                <?php 
echo esc_html__( 'Comment Replier', 'gpt3-ai-content-generator' );
?>
            </div>
            <!-- Search Settings -->
            <div class="advanced-settings" data-target="search-settings-container">
                <?php 
echo esc_html__( 'Semantic Search', 'gpt3-ai-content-generator' );
?>
            </div>

            <!-- Comment Container -->
            <div class="comment-settings-container">
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Prompt for Comment Replier ', 'gpt3-ai-content-generator' );
?><a href="https://docs.aipower.org/docs/content-writer/comment-replier" target="_blank">?</a></label>
                        <textarea rows="10" type="text" name="wpaicg_comment_prompt"><?php 
echo esc_html( str_replace( "\\", '', $wpaicg_comment_prompt ) );
?></textarea>
                        <p><?php 
echo sprintf(
    esc_html__( 'Ensure %s and %s and %s and %s and %s is included in your prompt.', 'gpt3-ai-content-generator' ),
    '<code>[username]</code>',
    '<code>[post_title]</code>',
    '<code>[post_excerpt]</code>',
    '<code>[last_comment]</code>',
    '<code>[parent_comments]</code>'
);
?></p>
                    </div>
                </div>
            </div>

            <!-- Search Container -->
            <div class="search-settings-container" style="display: none;">
                <p></p>
                <p><?php 
echo sprintf( esc_html__( 'Copy the following code and paste it in your page or post where you want to show the search box: %s', 'gpt3-ai-content-generator' ), '<code>[wpaicg_search]</code>' );
?></p>
                <h1><?php 
echo esc_html__( 'Search Box Style', 'gpt3-ai-content-generator' );
?></h1>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Font Size', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_search_font_size" class="specific-select">
                            <?php 
for ($i = 10; $i <= 30; $i++) {
    echo '<option' . (( $wpaicg_search_font_size == $i ? ' selected' : '' )) . ' value="' . esc_html( $i ) . '">' . esc_html( $i ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Placeholder', 'gpt3-ai-content-generator' );
?></label>
                        <input type="text" name="wpaicg_search_placeholder" value="<?php 
echo esc_html( get_option( 'wpaicg_search_placeholder', 'Search anything..' ) );
?>" class="specific-textfield">
                    </div>
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Font Color', 'gpt3-ai-content-generator' );
?></label>
                    <input value="<?php 
echo esc_html( $wpaicg_search_font_color );
?>" type="color" name="wpaicg_search_font_color">
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Border Color', 'gpt3-ai-content-generator' );
?></label>
                    <input value="<?php 
echo esc_html( $wpaicg_search_border_color );
?>" type="color" name="wpaicg_search_border_color">
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Background Color', 'gpt3-ai-content-generator' );
?></label>
                    <input value="<?php 
echo esc_html( $wpaicg_search_bg_color );
?>" type="color" name="wpaicg_search_bg_color">
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Width', 'gpt3-ai-content-generator' );
?></label>
                        <input value="<?php 
echo esc_html( $wpaicg_search_width );
?>" min="100" type="text" name="wpaicg_search_width" class="specific-textfield">
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Height', 'gpt3-ai-content-generator' );
?></label>
                        <input value="<?php 
echo esc_html( $wpaicg_search_height );
?>" min="100" type="text" name="wpaicg_search_height" class="specific-textfield"> 
                    </div>
                </div>
                <p></p>
                <h1><?php 
echo esc_html__( 'Search Results', 'gpt3-ai-content-generator' );
?></h1>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Number of Results', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_search_no_result" class="specific-select">
                            <?php 
for ($i = 1; $i <= 5; $i++) {
    echo '<option' . (( $wpaicg_search_no_result == $i ? ' selected' : '' )) . ' value="' . esc_html( $i ) . '">' . esc_html( $i ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="unique-page-container">
                    <div class="nice-form-group">
                        <label><?php 
echo esc_html__( 'Font Size', 'gpt3-ai-content-generator' );
?></label>
                        <select name="wpaicg_search_result_font_size" class="specific-select">
                            <?php 
for ($i = 10; $i <= 30; $i++) {
    echo '<option' . (( $wpaicg_search_result_font_size == $i ? ' selected' : '' )) . ' value="' . esc_html( $i ) . '">' . esc_html( $i ) . '</option>';
}
?>
                        </select>
                    </div>
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Font Color', 'gpt3-ai-content-generator' );
?></label>
                    <input value="<?php 
echo esc_html( $wpaicg_search_result_font_color );
?>" type="color" name="wpaicg_search_result_font_color">
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Background Color', 'gpt3-ai-content-generator' );
?></label>
                    <input value="<?php 
echo esc_html( $wpaicg_search_result_bg_color );
?>" type="color" name="wpaicg_search_result_bg_color">
                </div>
                <div class="nice-form-group">
                    <label><?php 
echo esc_html__( 'Progress Color', 'gpt3-ai-content-generator' );
?></label>
                    <input value="<?php 
echo esc_html( $wpaicg_search_loading_color );
?>" type="color" name="wpaicg_search_loading_color">
                </div>
            </div>

            <details> 
                <summary>
                    <input type="submit" value="<?php 
echo esc_html__( 'Save', 'gpt3-ai-content-generator' );
?>" name="wpaicg_submit" class="button button-primary button-large">
                    <input type="submit" value="Reset" name="wpaicg_reset" class="button button-secondary button-large">
                </summary>
            </details>
        </section>
    </form>
  </main>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        // 1. TAB NAVIGATION
        const tabs = document.querySelectorAll('.demo-page-master-navigation ul li a');
        const contentSections = document.querySelectorAll('.demo-page-master-content section');

        // Initially hide all sections (This part is handled by CSS now, you can choose to keep or remove these lines)
        contentSections.forEach((section, index) => {
            section.style.display = 'none';
        });

        // Explicitly show the first tab content and set the first tab as active
        if (contentSections.length > 0) {
            contentSections[0].style.display = 'block';
        }
        if (tabs.length > 0) {
            tabs[0].parentElement.classList.add('active');
        }

        // Tab click event
        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href').replace('#', '');
                const targetContent = document.getElementById(targetId);

                contentSections.forEach(section => {
                    section.style.display = 'none';
                });

                targetContent.parentElement.style.display = 'block';

                tabs.forEach(t => {
                    t.parentElement.classList.remove('active');
                });
                this.parentElement.classList.add('active');
            });
        });

        // 2. RESET BUTTON CONFIRMATION
        const resetButtons = document.querySelectorAll('input[name="wpaicg_reset"]');

        // Attach confirmation prompt to each reset button
        resetButtons.forEach(function(button) {
            button.onclick = function() {
                return confirm("This will reset all your settings to default. Are you sure?");
            };
        });

        // 3. ADVANCED SETTINGS TOGGLE
        document.querySelectorAll('.advanced-settings').forEach(button => {
            button.addEventListener('click', function() {
                const targetClass = this.getAttribute('data-target');
                const targetElement = document.querySelector('.' + targetClass);

                // Check if the clicked button's target is already visible
                const isTargetVisible = targetElement && targetElement.style.display === 'block';

                // First, hide all settings containers
                document.querySelectorAll('.advance-settings-container, .dalle-settings-container, .pexel-settings-container, .pixabay-settings-container, .sd-settings-container, .single-settings-container, .comment-settings-container, .search-settings-container').forEach(container => {
                    container.style.display = 'none';
                });

                // If the target was already visible, we're done (it's now hidden). If it wasn't, show it.
                if (!isTargetVisible && targetElement) {
                    targetElement.style.display = 'block';
                }
                // hide google_safety_settings
                document.getElementById('google_safety_settings').style.display = 'none';
            });
        });

        // 4. MASKING API KEYS
        var openAIKeyInput = document.getElementById('wpaicg_openai_api_key');
        var googleApiKeyInput = document.getElementById('wpaicg_google_api_key');
        var azureApiKeyInput = document.getElementById('wpaicg_azure_api_key');
        var pexelsApiKeyInput = document.getElementById('wpaicg_pexels_api');
        var pixabayApiKeyInput = document.getElementById('wpaicg_pixabay_api');
        var sdApiKeyInput = document.getElementById('wpaicg_sd_api_key');
        var openRouterApiKeyInput = document.getElementById('wpaicg_openrouter_api_key');

        if (openAIKeyInput.value) {
            maskValue(openAIKeyInput);
        }

        if (googleApiKeyInput.value) {
            maskValue(googleApiKeyInput);
        }

        if (azureApiKeyInput.value) {
            maskValue(azureApiKeyInput);
        }

        if (pexelsApiKeyInput.value) {
            maskValue(pexelsApiKeyInput);
        }

        if (pixabayApiKeyInput.value) {
            maskValue(pixabayApiKeyInput);
        }

        if (sdApiKeyInput.value) {
            maskValue(sdApiKeyInput);
        }

        if (openRouterApiKeyInput.value) {
            maskValue(openRouterApiKeyInput);
        }

        function maskValue(element) {
            if (element.value.length > 4) {
                // Store the full API key in a data attribute if you need to use it later
                element.setAttribute('data-api-key', element.value);
                // Replace all but the last 4 characters with asterisks
                element.value = '*'.repeat(element.value.length - 4) + element.value.substr(-4);
            }
        }

        function unmaskValue(element) {
            // Restore the full API key from the data attribute if it exists
            if (element.hasAttribute('data-api-key')) {
                element.value = element.getAttribute('data-api-key');
            }
        }

        // 5. PROVIDER SPECIFIC FIELDS VISIBILITY
        function updateFieldVisibility() {
            var provider = document.getElementById('wpaicg_provider').value;
            var openaiFields = document.getElementById('openai_specific_fields');
            var googleFields = document.getElementById('google_specific_fields');
            var azureFields = document.getElementById('azure_specific_fields');
            var googlesafetySettings = document.getElementById('toggleGoogleSafetySettings');
            var googleSafetySettingsDetails = document.getElementById('google_safety_settings');
            var singleSettingsContainer = document.querySelector('.single-settings-container');
            var openRouterFields = document.getElementById('openrouter_specific_fields');

            // Hide all fields initially
            openaiFields.style.display = 'none';
            googleFields.style.display = 'none';
            azureFields.style.display = 'none';
            googlesafetySettings.style.display = 'none';
            googleSafetySettingsDetails.style.display = 'none';
            singleSettingsContainer.style.display = 'none';
            openRouterFields.style.display = 'none';

            // Show fields based on selected provider
            if(provider === 'OpenAI') {
                openaiFields.style.display = 'block';
                googlesafetySettings.style.display = 'none';
                googleSafetySettingsDetails.style.display = 'none';
            } else if(provider === 'Google') {
                googleFields.style.display = 'block';
                googlesafetySettings.style.display = 'inline-block';
            } else if(provider === 'Azure') {
                azureFields.style.display = 'block';
                googlesafetySettings.style.display = 'none';
                googleSafetySettingsDetails.style.display = 'none';
            } else if(provider === 'OpenRouter') {
                openRouterFields.style.display = 'block';
                googlesafetySettings.style.display = 'none';
                googleSafetySettingsDetails.style.display = 'none';
            }
        }

        // Bind the function to the provider select element
        document.getElementById('wpaicg_provider').addEventListener('change', updateFieldVisibility);

        // Call the function once to set the initial state
        updateFieldVisibility();

        var toggleSafetyButton = document.getElementById('toggleGoogleSafetySettings');
        if (toggleSafetyButton) {
            toggleSafetyButton.addEventListener('click', function() {
                var safetySettings = document.getElementById(toggleSafetyButton.getAttribute('data-target'));
                var singleSettingsContainer = document.querySelector('.single-settings-container');
                if (safetySettings.style.display === 'none' || safetySettings.style.display === '') {
                    safetySettings.style.display = 'block';
                    singleSettingsContainer.style.display = 'none';
                } else {
                    safetySettings.style.display = 'none';
                }
            });
        }

        // 6. AZURE FORM SUBMISSION VALIDATION
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            const provider = document.getElementById('wpaicg_provider').value;
            if (provider === 'Azure') {
                const apiKey = document.getElementById('wpaicg_azure_api_key').value;
                const endpoint = document.getElementById('wpaicg_azure_endpoint').value;
                const deployment = document.getElementById('wpaicg_azure_deployment').value;

                if (!apiKey || !endpoint || !deployment) {
                    alert('<?php 
echo esc_js( __( 'Please fill in all the mandatory fields for Azure.', 'gpt3-ai-content-generator' ) );
?>');
                    e.preventDefault();
                }
            }
        });

        // 7. TOC FIELDS VISIBILITY
        function updateToCFieldsVisibility() {
            const tocCheckbox = document.getElementById('wpaicg_toc');
            const tocTitleContainer = document.getElementById('toc_title_container');
            const tocTitleTagContainer = document.getElementById('toc_title_tag_container');

            if (tocCheckbox.checked) {
                tocTitleContainer.style.display = 'block';
                tocTitleTagContainer.style.display = 'block';
            } else {
                tocTitleContainer.style.display = 'none';
                tocTitleTagContainer.style.display = 'none';
            }
        }

        // Bind the visibility update function to the Table of Contents checkbox change event
        document.getElementById('wpaicg_toc').addEventListener('change', updateToCFieldsVisibility);

        // Call the function once to set the initial state based on the current checkbox state
        updateToCFieldsVisibility();

        // 8. INTRO FIELDS VISIBILITY
        function updateIntroFieldsVisibility() {
            const introCheckbox = document.getElementById('wpai_add_intro');
            const hideIntroTitleContainer = document.getElementById('hide_intro_title_container');
            const introTagContainer = document.getElementById('intro_title_tag_container');

            if (introCheckbox.checked) {
                hideIntroTitleContainer.style.display = 'block';
                introTagContainer.style.display = 'block';
            } else {
                hideIntroTitleContainer.style.display = 'none';
                introTagContainer.style.display = 'none';
            }
        }

        // Bind the visibility update function to the Introduction checkbox change event
        document.getElementById('wpai_add_intro').addEventListener('change', updateIntroFieldsVisibility);

        // Call the function once to set the initial state based on the current checkbox state
        updateIntroFieldsVisibility();

        // 9. CONCLUSION FIELDS VISIBILITY
        function updateConclusionFieldsVisibility() {
            const conclusionCheckbox = document.getElementById('wpai_add_conclusion');
            const hideConclusionTitleContainer = document.getElementById('hide_conclusion_title_container');
            const conclusionTagContainer = document.getElementById('conclusion_title_tag_container');

            if (conclusionCheckbox.checked) {
                hideConclusionTitleContainer.style.display = 'block';
                conclusionTagContainer.style.display = 'block';
            } else {
                hideConclusionTitleContainer.style.display = 'none';
                conclusionTagContainer.style.display = 'none';
            }
        }

        // Bind the visibility update function to the Conclusion checkbox change event
        document.getElementById('wpai_add_conclusion').addEventListener('change', updateConclusionFieldsVisibility);

        // Call the function once to set the initial state based on the current checkbox state
        updateConclusionFieldsVisibility();

        // 10. SEO fields
        const yoastMetaDesc = document.querySelector('#_yoast_wpseo_metadesc');
        const aioseoDescription = document.querySelector('#_aioseo_description');
        const wpaicgSeoMetaTag = document.querySelector('#_wpaicg_seo_meta_tag');
        const rankMathDescription = document.querySelector('#rank_math_description');

        if (yoastMetaDesc) {
            yoastMetaDesc.addEventListener('click', function () {
                if (this.checked) {
                    if (wpaicgSeoMetaTag) wpaicgSeoMetaTag.checked = false;
                    if (aioseoDescription) aioseoDescription.checked = false;
                    if (rankMathDescription) rankMathDescription.checked = false;
                }
            });
        }

        if (aioseoDescription) {
            aioseoDescription.addEventListener('click', function () {
                if (this.checked) {
                    if (wpaicgSeoMetaTag) wpaicgSeoMetaTag.checked = false;
                    if (yoastMetaDesc) yoastMetaDesc.checked = false;
                    if (rankMathDescription) rankMathDescription.checked = false;
                }
            });
        }

        if (wpaicgSeoMetaTag) {
            wpaicgSeoMetaTag.addEventListener('click', function () {
                if (this.checked) {
                    if (aioseoDescription) aioseoDescription.checked = false;
                    if (yoastMetaDesc) yoastMetaDesc.checked = false;
                    if (rankMathDescription) rankMathDescription.checked = false;
                }
            });
        }

        if (rankMathDescription) {
            rankMathDescription.addEventListener('click', function () {
                if (this.checked) {
                    if (aioseoDescription) aioseoDescription.checked = false;
                    if (yoastMetaDesc) yoastMetaDesc.checked = false;
                    if (wpaicgSeoMetaTag) wpaicgSeoMetaTag.checked = false;
                }
            });
        }

    });
</script>
<script>
    // 11. Hide Original Title in Prompt when Generate Title from Keywords is unchecked
    function handleTitleFromKeywordsChange(checkbox) {
        const originalTitleCheckbox = document.getElementById('_wpaicg_original_title_in_prompt');
        if (!checkbox.checked) {
            originalTitleCheckbox.checked = false;
            originalTitleCheckbox.disabled = true;
        } else {
            originalTitleCheckbox.disabled = false;
        }
    }
</script>
<script>
    jQuery(document).ready(function($){

        // Function to populate the text area based on the selected template
        function populateTitleTextArea() {
            var selectedTemplate = $(this).val();
            var textarea = $(this).closest('.wpaicg_woo_custom_prompts').find('textarea[name="wpaicg_woo_custom_prompt_title"]');
            
            // Prompt templates
            var templates = {
                '1': "<?php 
echo esc_js( esc_html__( 'Create an SEO-friendly and eye-catching title for the product: %s. The title should emphasize its key features. Use the following for context: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '2': "<?php 
echo esc_js( esc_html__( 'Devise a captivating and SEO-optimized title for the following product: %s that highlights its unique selling points. Use these details for reference: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '3': "<?php 
echo esc_js( esc_html__( 'Craft a product title for %s that is not only SEO-optimized but also engages the customer and informs them why this is the product they’ve been looking for. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '4': "<?php 
echo esc_js( esc_html__( 'Construct an SEO-optimized title for the product: %s that is rich in keywords relevant to the product. Use the following for additional context: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '5': "<?php 
echo esc_js( esc_html__( 'Generate a concise yet comprehensive title for the product: %s that covers all the essential points customers are interested in. Make sure to utilize Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for a better context.', 'gpt3-ai-content-generator' ) );
?>"
            };

            if (templates[selectedTemplate]) {
                textarea.val(templates[selectedTemplate]);
            }
        }

        // Toggle custom prompts
        $('.wpaicg_woo_custom_prompt').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg_woo_custom_prompts').show();
            } else {
                $('.wpaicg_woo_custom_prompts').hide();
            }
        });

        // Attach the change event to the dropdown
        $('#titlePromptTemplates').change(populateTitleTextArea);
        // Attach the change event to dynamically generated dropdowns in the modal
        $(document).on('change', '#titlePromptTemplates', populateTitleTextArea);

        // Function to populate the Description text area
        function populateDescriptionTextArea() {
            var selectedTemplate = $(this).val();
            var textarea = $(this).closest('.wpaicg_woo_custom_prompts').find('textarea[name="wpaicg_woo_custom_prompt_description"]');
            
            // Description prompt templates
            var DescriptionTemplates = {
                '1': "<?php 
echo esc_js( esc_html__( 'Craft an extensive and captivating narrative around the product: %s. Dive deep into its key features, benefits, and value proposition. Use its Attributes: [current_attributes], Short Description: [current_short_description] and Product Categories: [current_categories] to enrich the narrative.', 'gpt3-ai-content-generator' ) );
?>",
                '2': "<?php 
echo esc_js( esc_html__( 'Develop a comprehensive and detailed description for the product: %s that serves as a complete guide for the customer, detailing its functionality, features, and use-cases. Make sure to incorporate its Attributes: [current_attributes], Short Description: [current_short_description] and Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '3': "<?php 
echo esc_js( esc_html__( 'Write a compelling product description for %s that evokes an emotional connection, inspiring the customer to visualize themselves using the product. Leverage its Attributes: [current_attributes], Short Description: [current_short_description] and Product Categories: [current_categories] to add depth and context.', 'gpt3-ai-content-generator' ) );
?>",
                '4': "<?php 
echo esc_js( esc_html__( 'Construct an in-depth description for the product: %s that focuses on its unique selling propositions. Distinguish it from competitors and highlight what makes it a must-have. Use Attributes: [current_attributes], Short Description: [current_short_description] and Product Categories: [current_categories] for a more detailed context.', 'gpt3-ai-content-generator' ) );
?>",
                '5': "<?php 
echo esc_js( esc_html__( 'Compose an SEO-optimized, yet customer-centric, description for the product: %s. Include relevant keywords naturally, and focus on answering any questions a customer might have about the product. Utilize Attributes: [current_attributes], Short Description: [current_short_description] and Product Categories: [current_categories] for richer context.', 'gpt3-ai-content-generator' ) );
?>"
            };


            if (DescriptionTemplates[selectedTemplate]) {
                textarea.val(DescriptionTemplates[selectedTemplate]);
            }
        }

         // Attach the change event to the Short Description dropdown
        $('#DescriptionPromptTemplates').change(populateDescriptionTextArea);
        $(document).on('change', '#DescriptionPromptTemplates', populateDescriptionTextArea);

        // Function to populate the Short Description text area
        function populateShortDescriptionTextArea() {
            var selectedTemplate = $(this).val();
            var textarea = $(this).closest('.wpaicg_woo_custom_prompts').find('textarea[name="wpaicg_woo_custom_prompt_short"]');
            
            // Short Description prompt templates
            var ShortDescriptionTemplates = {
                '1': "<?php 
echo esc_js( esc_html__( 'Compose a short description for the product: %s that succinctly highlights its key features and benefits. Use the following attributes and description for context: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '2': "<?php 
echo esc_js( esc_html__( 'Write a short description for the product: %s that clearly outlines how it solves a specific problem for the customer. Reference these details for a better understanding: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '3': "<?php 
echo esc_js( esc_html__( 'Craft a compelling short description for the product: %s that emphasizes what sets it apart from competitors. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '4': "<?php 
echo esc_js( esc_html__( 'Create an emotive short description for the product: %s that aims to establish an emotional connection with potential buyers. Use the following for context: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '5': "<?php 
echo esc_js( esc_html__( 'Devise an SEO-optimized short description for the product: %s, incorporating relevant keywords without sacrificing readability. Use these details for reference: Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>"
            };

            if (ShortDescriptionTemplates[selectedTemplate]) {
                textarea.val(ShortDescriptionTemplates[selectedTemplate]);
            }
        }

        // Attach the change event to the Short Description dropdown
        $('#ShortDescriptionPromptTemplates').change(populateShortDescriptionTextArea);
        $(document).on('change', '#ShortDescriptionPromptTemplates', populateShortDescriptionTextArea);


        // Function to populate the Meta Description text area
        function populateMetaDescriptionTextArea() {
            var selectedTemplate = $(this).val();
            var textarea = $(this).closest('.wpaicg_woo_custom_prompts').find('textarea[name="wpaicg_woo_custom_prompt_meta"]');
            
            // Meta Description prompt templates
            var MetaDescriptionTemplates = {
                '1': "<?php 
echo esc_js( esc_html__( 'Craft a meta description for the product: %s that succinctly highlights its key features and benefits. Aim to stay within 155 characters. Use Attributes: [current_attributes], Full Description: [current_full_description], Short Description: [current_short_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '2': "<?php 
echo esc_js( esc_html__( 'Compose a compelling 155-character meta description for the product: %s that illustrates how it solves a specific problem for the customer. Reference these details: Attributes: [current_attributes], Full Description: [current_full_description], Short Description: [current_short_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '3': "<?php 
echo esc_js( esc_html__( 'Write a meta description for the product: %s that emotionally engages the potential customer, inspiring them to click and learn more. Limit to 155 characters. Use Attributes: [current_attributes], Full Description: [current_full_description], Short Description: [current_short_description] and Product Categories: [current_categories] for added depth.', 'gpt3-ai-content-generator' ) );
?>",
                '4': "<?php 
echo esc_js( esc_html__( 'Create an SEO-optimized meta description for the product: %s that is rich in keywords, yet readable and engaging. Keep it under 155 characters. Refer to these details: Attributes: [current_attributes], Full Description: [current_full_description], Short Description: [current_short_description], Product Categories: [current_categories].', 'gpt3-ai-content-generator' ) );
?>",
                '5': "<?php 
echo esc_js( esc_html__( 'Devise a straightforward, 155-character meta description for the product: %s that provides just the facts, appealing to a no-nonsense customer base. Use Attributes: [current_attributes], Full Description: [current_full_description], Short Description: [current_short_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>"
            };

            if (MetaDescriptionTemplates[selectedTemplate]) {
                textarea.val(MetaDescriptionTemplates[selectedTemplate]);
            }
        }

        // Attach the change event to the Meta Description dropdown
        $('#MetaDescriptionPromptTemplates').change(populateMetaDescriptionTextArea);
        $(document).on('change', '#MetaDescriptionPromptTemplates', populateMetaDescriptionTextArea);

        // Function to populate the Tags text area
        function populateTagsTextArea() {
            var selectedTemplate = $(this).val();
            var textarea = $(this).closest('.wpaicg_woo_custom_prompts').find('textarea[name="wpaicg_woo_custom_prompt_keywords"]');
            
            // Tags prompt templates
            var TagsTemplates = {
                '1': "<?php 
echo esc_js( esc_html__( 'Generate a set of highly relevant and SEO-optimized tags for the product: %s. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '2': "<?php 
echo esc_js( esc_html__( 'Create a list of tags for the product: %s that will increase its discoverability. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '3': "<?php 
echo esc_js( esc_html__( 'Craft a set of tags for the product: %s that describe its features and benefits. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '4': "<?php 
echo esc_js( esc_html__( 'Compile a group of keywords as tags for the product: %s that encompass its functionality and use-cases. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>",
                '5': "<?php 
echo esc_js( esc_html__( 'Develop an SEO-optimized list of tags for the product: %s, focusing on high-search-volume keywords. Use Attributes: [current_attributes], Short Description: [current_short_description], Full Description: [current_full_description] and Product Categories: [current_categories] for context.', 'gpt3-ai-content-generator' ) );
?>"
            };

            if (TagsTemplates[selectedTemplate]) {
                textarea.val(TagsTemplates[selectedTemplate]);
            }
        }

        // Attach the change event to the Tags dropdown
        $('#TagsPromptTemplates').change(populateTagsTextArea);
        $(document).on('change', '#TagsPromptTemplates', populateTagsTextArea);

        // Function to populate the Focus Keyword text area
        function populateFocusKeywordTextArea() {
            var selectedTemplate = $(this).val();
            var textarea = $(this).closest('.wpaicg_woo_custom_prompts').find('textarea[name="wpaicg_woo_custom_prompt_focus_keyword"]');
            
            // Focus Keyword prompt templates
            var FocusKeywordTemplates = {
                '1': "<?php 
echo esc_js( esc_html__( 'Identify the primary keyword for the following product: %s. Please respond in English. No additional comments, just the keyword.', 'gpt3-ai-content-generator' ) );
?>",
                '2': "<?php 
echo esc_js( esc_html__( 'Generate SEO-optimized and high-volume focus keywords in English for the following product: %s. Keywords should be the main terms you aim to rank for. Avoid using symbols like -, #, etc. Results must be comma-separated.', 'gpt3-ai-content-generator' ) );
?>",
                '3': "<?php 
echo esc_js( esc_html__( 'Generate niche-specific and unique focus keywords in English for the product: %s. Keywords should closely align with the product unique features or niche. Avoid using symbols like -, #, etc. Results must be comma-separated.', 'gpt3-ai-content-generator' ) );
?>",
                '4': "<?php 
echo esc_js( esc_html__( 'Generate trending or seasonally relevant focus keywords in English for the following product: %s. Ensure they directly relate to the product and its features. Avoid using symbols like -, #, etc. Results must be comma-separated.', 'gpt3-ai-content-generator' ) );
?>",
                '5': "<?php 
echo esc_js( esc_html__( 'Generate focus keywords in English for the product: %s. Keywords should fill a gap or seize an opportunity that competitors might have missed but are still highly relevant to the product. Avoid using symbols like -, #, etc. Results must be comma-separated.', 'gpt3-ai-content-generator' ) );
?>"
            };

            if (FocusKeywordTemplates[selectedTemplate]) {
                textarea.val(FocusKeywordTemplates[selectedTemplate]);
            }
        }

        // Attach the change event to the Focus Keyword dropdown
        $('#FocusKeywordPromptTemplates').change(populateFocusKeywordTextArea);
        $(document).on('change', '#FocusKeywordPromptTemplates', populateFocusKeywordTextArea);

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    var copyCodes = document.querySelectorAll('.toggle-shortcode-small'); // Select all elements

    copyCodes.forEach(function(copyCode) { // Iterate over all selected elements
        copyCode.addEventListener('click', function() {
        // Copy text
        navigator.clipboard.writeText(this.textContent).then(() => {
            // Temporarily change the text to indicate copy
            const originalText = this.textContent;
            this.textContent = 'Shortcode copied!';
            setTimeout(() => {
            this.textContent = originalText;
            }, 2000); // Reset text after 2 seconds
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
        });
    });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuDetailsDiv = document.getElementById('assistant-menu-details');
        let newAssistantContainerExists = false; // Flag to check if the new menu item details are already present

        document.querySelectorAll('.editor-settings').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if(newAssistantContainerExists){
                    // If the new assistant container already exists, remove it to avoid duplication
                    const newAssistantContainer = document.getElementById('new-assistant-container');
                    if(newAssistantContainer) newAssistantContainer.remove();
                    newAssistantContainerExists = false; // Reset flag
                }

                var index = this.getAttribute('data-index');
                var nameInput = document.querySelector('input[name="wpaicg_editor_button_menus[' + index + '][name]"]');
                var promptInput = document.querySelector('input[name="wpaicg_editor_button_menus[' + index + '][prompt]"]');
                var menuNameInput = document.getElementById('assistant-menu-name');
                var menuPromptInput = document.getElementById('assistant-menu-prompt');

                // Display menu details and update hidden inputs
                menuNameInput.value = nameInput.value;
                menuPromptInput.value = promptInput.value;

                // When the value of the detail inputs change, update the hidden inputs
                menuNameInput.onchange = function() { nameInput.value = menuNameInput.value; };
                menuPromptInput.onchange = function() { promptInput.value = menuPromptInput.value; };

                // Show the existing menu details
                menuDetailsDiv.style.display = 'block';
            });
        });

        document.getElementById('add-assistant-menu-item').addEventListener('click', function() {
            // Toggle the visibility based on whether a new assistant container exists
            if (!newAssistantContainerExists) {
                // If the container doesn't exist, we create and show it
                let newMenuIndex = document.querySelectorAll('.editor-settings-link').length;
                let html = '<div id="new-assistant-container">';
                html += '<div class="nice-form-group">';
                html += '<label>Menu Name:</label>';
                html += '<input type="text" name="wpaicg_editor_button_menus[' + newMenuIndex + '][name]" value="">';
                html += '</div><div class="nice-form-group">';
                html += '<label>Menu Prompt:</label>';
                html += '<textarea rows="10" name="wpaicg_editor_button_menus[' + newMenuIndex + '][prompt]" value=""></textarea>';
                html += '<small>Make sure to include <code>[text]</code> in your prompt.</small>';
                html += '</div></div>';

                document.querySelector('.newitem').insertAdjacentHTML('afterend', html);
                newAssistantContainerExists = true; // Set the flag to true
                menuDetailsDiv.style.display = 'none'; // Hide the existing menu details
            } else {
                // If the container exists, we simply hide it and reset the flag
                const newAssistantContainer = document.getElementById('new-assistant-container');
                if(newAssistantContainer) newAssistantContainer.remove();
                newAssistantContainerExists = false; // Reset flag
                menuDetailsDiv.style.display = 'none'; // Ensure existing menu details are hidden
            }
        });

        // Reference to the Delete Menu Items button
        const toggleDeleteBtn = document.getElementById('toggle-delete-menu-item');
        // Initially, delete buttons are not visible
        let deleteButtonsVisible = false;

        toggleDeleteBtn.addEventListener('click', function() {
            // Toggle the visibility of the delete buttons
            document.querySelectorAll('.assistant-delete-menu-item').forEach(function(button) {
                if(deleteButtonsVisible) {
                    // If currently visible, hide them
                    button.style.display = 'none';
                } else {
                    // If currently hidden, show them
                    button.style.display = 'inline-block'; // or 'block' depending on your layout
                }
            });

            // Toggle the state
            deleteButtonsVisible = !deleteButtonsVisible;
        });

        // Event listener for delete buttons
        document.querySelectorAll('.assistant-delete-menu-item').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                deleteMenuItem(this.getAttribute('data-index'));
            });
        });

        // Function to delete a menu item
        function deleteMenuItem(index) {
            var menuItem = document.querySelector('input[name="wpaicg_editor_button_menus[' + index + '][name]"]');
            if (menuItem) {
                menuItem.value = '';
                var deleteButton = document.querySelector('input[name="wpaicg_editor_button_menus[' + index + '][delete]"]');
                if (deleteButton) {
                    deleteButton.value = '1';
                }

                var menuItemLink = document.querySelector('.editor-settings.editor-settings-link[data-index="' + index + '"]');
                var deleteButton = document.querySelector('.assistant-delete-menu-item[data-index="' + index + '"]');

                if (menuItemLink) {
                    menuItemLink.style.display = 'none';
                    deleteButton.style.display = 'none';
                }
            }
        }

        
    });
</script>