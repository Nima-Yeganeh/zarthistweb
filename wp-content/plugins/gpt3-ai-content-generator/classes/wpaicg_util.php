<?php

namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_Util')) {
    class WPAICG_Util
    {
        private static  $instance = null ;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        // Function to get the default AI provider and model
        public function get_default_ai_provider() {
            $provider = get_option('wpaicg_provider', 'OpenAI');
            $model = '';

            switch ($provider) {
                case 'OpenAI':
                    $model = get_option('wpaicg_ai_model', 'gpt-3.5-turbo-16k');
                    break;
                case 'Azure':
                    $model = get_option('wpaicg_azure_deployment', '');
                    break;
                case 'Google':
                    $model = get_option('wpaicg_google_default_model', 'gemini-pro');
                    break;
                case 'OpenRouter':
                    $model = get_option('wpaicg_openrouter_default_model', 'openai/gpt-4o');
                    break;
                default:
                    $model = 'gpt-3.5-turbo-16k';
            }

            return array('provider' => $provider, 'model' => $model);
        }

        public function initialize_ai_engine($provider = null) {
            $wpaicg_provider = $provider ? $provider : get_option('wpaicg_provider', 'OpenAI');
            $ai_engine = WPAICG_OpenAI::get_instance()->openai();
    
            switch ($wpaicg_provider) {
                case 'OpenAI':
                    $ai_engine = WPAICG_OpenAI::get_instance()->openai();
                    break;
                case 'Azure':
                    $ai_engine = WPAICG_AzureAI::get_instance()->azureai();
                    break;
                case 'Google':
                    $ai_engine = WPAICG_Google::get_instance();
                    break;
                case 'OpenRouter':
                    $ai_engine = WPAICG_OpenRouter::get_instance()->openai();
                    break;
                default:
                    $ai_engine = WPAICG_OpenAI::get_instance()->openai();
            }
    
            if (!$ai_engine) {
                throw new \Exception(esc_html__('Enter your API key in the Settings.', 'gpt3-ai-content-generator'));
            }
    
            return $ai_engine;
        }

        public function initialize_embedding_engine($selected_embedding_provider = null, $wpaicg_provider = null) {
            if (!$selected_embedding_provider) {
                $selected_embedding_provider = $wpaicg_provider ? $wpaicg_provider : get_option('wpaicg_provider', 'OpenAI'); // default to OpenAI
            }
        
            switch ($selected_embedding_provider) {
                case 'OpenAI':
                    $ai_engine = WPAICG_OpenAI::get_instance()->openai();
                    break;
                case 'Azure':
                    $ai_engine = WPAICG_AzureAI::get_instance()->azureai();
                    break;
                case 'Google':
                    $ai_engine = WPAICG_Google::get_instance();
                    break;
                default:
                    $ai_engine = WPAICG_OpenAI::get_instance()->openai();
            }
        
            if (!$ai_engine) {
                throw new \Exception(esc_html__('Unable to initialize the AI engine. Please check your API key settings.', 'gpt3-ai-content-generator'));
            }
        
            return $ai_engine;
        }
        

        public function get_embedding_models() {
            $models = array(
                'OpenAI' => array(
                    'text-embedding-3-small' => 1536,
                    'text-embedding-3-large' => 3072,
                    'text-embedding-ada-002' => 1536
                ),
                'Google' => array(
                    'embedding-001' => 768,
                    'text-embedding-004' => 768,
                )
            );
        
            // Check if 'wpaicg_azure_embeddings' exists in the options table
            $azure_embeddings = get_option('wpaicg_azure_embeddings');
            if (!empty($azure_embeddings)) {
                $models['Azure'] = array($azure_embeddings => 1536);
            }
        
            return $models;
        }
        
        public function seo_plugin_activated()
        {
            $activated = false;
            if(is_plugin_active('wordpress-seo/wp-seo.php')){
                $activated = '_yoast_wpseo_metadesc';
            }
            elseif(is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') || is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php')){
                $activated = '_aioseo_description';
            }
            elseif(is_plugin_active('seo-by-rank-math/rank-math.php')){
                $activated = 'rank_math_description';
            }
            return $activated;
        }

        public function wpaicg_random($length = 10) {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[wp_rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        public function wpaicg_is_pro()
        {
            return wpaicg_gacg_fs()->is_plan__premium_only( 'pro' );
        }

        public function sanitize_text_or_array_field($array_or_string)
        {
            if (is_string($array_or_string)) {
                $array_or_string = sanitize_text_field($array_or_string);
            } elseif (is_array($array_or_string)) {
                foreach ($array_or_string as $key => &$value) {
                    if (is_array($value)) {
                        $value = $this->sanitize_text_or_array_field($value);
                    } else {
                        $value = sanitize_text_field(str_replace('%20','+',$value));
                    }
                }
            }

            return $array_or_string;
        }

        public function wpaicg_get_meta_keys($post_type = false)
        {
            if (empty($post_type)) return array();

            $post_type = ($post_type == 'product' and class_exists('WooCommerce')) ? array('product') : array($post_type);

            global $wpdb;
            $table_prefix = $wpdb->prefix;

            $post_type = array_map(function($item) use ($wpdb) {
                return $wpdb->prepare('%s', $item);
            }, $post_type);

            $post_type_in = implode(',', $post_type);

            $meta_keys = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT {$table_prefix}postmeta.meta_key FROM {$table_prefix}postmeta, {$table_prefix}posts WHERE {$table_prefix}postmeta.post_id = {$table_prefix}posts.ID AND {$table_prefix}posts.post_type IN ({$post_type_in}) AND {$table_prefix}postmeta.meta_key NOT LIKE '_edit%' AND {$table_prefix}postmeta.meta_key NOT LIKE '_oembed_%' LIMIT 1000"));

            $_existing_meta_keys = array();
            if ( ! empty($meta_keys)){
                $exclude_keys = array('_first_variation_attributes', '_is_first_variation_created');
                foreach ($meta_keys as $meta_key) {
                    if ( strpos($meta_key->meta_key, "_tmp") === false && strpos($meta_key->meta_key, "_v_") === false && ! in_array($meta_key->meta_key, $exclude_keys))
                        $_existing_meta_keys[] = 'wpaicgcf_'.$meta_key->meta_key;
                }
            }
            return $_existing_meta_keys;
        }

        public function wpaicg_existing_taxonomies($post_type = false)
        {
            if (empty($post_type)) return array();

            $post_taxonomies = array_diff_key($this->wpaicg_get_taxonomies_by_object_type(array($post_type), 'object'), array_flip(array('post_format')));
            $_existing_taxonomies = array();
            if ( ! empty($post_taxonomies)){
                foreach ($post_taxonomies as $tx) {
                    if (strpos($tx->name, "pa_") !== 0)
                        $_existing_taxonomies[] = array(
                            'name' => empty($tx->label) ? $tx->name : $tx->label,
                            'label' => 'wpaicgtx_'.$tx->name,
                            'type' => 'cats'
                        );
                }
            }
            return $_existing_taxonomies;
        }

        function wpaicg_get_taxonomies_by_object_type($object_type, $output = 'names') {
            global $wp_taxonomies;

            is_array($object_type) or $object_type = array($object_type);
            $field = ('names' == $output) ? 'name' : false;
            $filtered = array();
            foreach ($wp_taxonomies as $key => $obj) {
                if (array_intersect($object_type, $obj->object_type)) {
                    $filtered[$key] = $obj;
                }
            }
            if ($field) {
                $filtered = wp_list_pluck($filtered, $field);
            }
            return $filtered;
        }

        public function wpaicg_tabs($prefix, $menus, $selected = false)
        {
            foreach($menus as $key=>$menu){
                $capability = $prefix;
                if(is_string($key)){
                    $capability .= '_'.$key;
                }
                if($capability == 'wpaicg_finetune_fine-tunes'){
                    $capability = 'wpaicg_finetune_file-tunes';
                }
                if(current_user_can($capability) || in_array('administrator', (array)wp_get_current_user()->roles)){
                    $url = admin_url('admin.php?page='.$prefix);
                    if(is_string($key)){
                        $url .= '&action='.$key;
                    }
                    ?>
                    <a class="nav-tab<?php echo $key === $selected ? ' nav-tab-active':''?>" href="<?php echo esc_html($url)?>">
                        <?php
                        echo esc_html($menu);
                        if($key == 'pdf' && $prefix == 'wpaicg_embeddings' && !$this->wpaicg_is_pro()){
                            ?>
                            <span style="color: #000;padding: 2px 5px;font-size: 12px;background:#ffba00;border-radius: 2px;"><?php echo esc_html__('Pro','gpt3-ai-content-generator')?></span>
                            <?php
                        }
                        ?>
                    </a>
                    <?php
                }
            }
        }

        public $wpaicg_languages = [
            'en' => 'English',
            'af' => 'Afrikaans',
            'ar' => 'Arabic',
            'an' => 'Armenian',
            'bs' => 'Bosnian',
            'bg' => 'Bulgarian',
            'zh' => 'Chinese (Simplified)',
            'zt' => 'Chinese (Traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'et' => 'Estonian',
            'fil' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'vi' => 'Vietnamese'
        ];

        public $chat_language_options = array(
            'en' => 'English',
            'af' => 'Afrikaans',
            'ar' => 'Arabic',
            'bg' => 'Bulgarian',
            'zh' => 'Chinese',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'et' => 'Estonian',
            'fil' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'sv' => 'Swedish',
            'es' => 'Spanish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'vi' => 'Vietnamese',
        );
    
        public $chat_profession_options = array(
            'none' => 'None',
            'accountant' => 'Accountant',
            'advertisingspecialist' => 'Advertising Specialist',
            'architect' => 'Architect',
            'artist' => 'Artist',
            'blogger' => 'Blogger',
            'businessanalyst' => 'Business Analyst',
            'businessowner' => 'Business Owner',
            'carexpert' => 'Car Expert',
            'consultant' => 'Consultant',
            'counselor' => 'Counselor',
            'cryptocurrencytrader' => 'Cryptocurrency Trader',
            'cryptocurrencyexpert' => 'Cryptocurrency Expert',
            'customersupport' => 'Customer Support', 
            'designer' => 'Designer',
            'digitalmarketinagency' => 'Digital Marketing Agency',
            'editor' => 'Editor',
            'engineer' => 'Engineer',
            'eventplanner' => 'Event Planner',
            'freelancer' => 'Freelancer',
            'insuranceagent' => 'Insurance Agent',
            'insurancebroker' => 'Insurance Broker',
            'interiordesigner' => 'Interior Designer',
            'journalist' => 'Journalist',
            'marketingagency' => 'Marketing Agency',
            'marketingexpert' => 'Marketing Expert',
            'marketingspecialist' => 'Marketing Specialist',
            'photographer' => 'Photographer',
            'programmer' => 'Programmer',
            'publicrelationsagency' => 'Public Relations Agency',
            'publisher' => 'Publisher',
            'realestateagent' => 'Real Estate Agent',
            'recruiter' => 'Recruiter',
            'reporter' => 'Reporter',
            'salesperson' => 'Sales Person',
            'salerep' => 'Sales Representative',
            'seoagency' => 'SEO Agency',
            'seoexpert' => 'SEO Expert',
            'socialmediaagency' => 'Social Media Agency',
            'student' => 'Student',
            'teacher' => 'Teacher',
            'technicalsupport' => 'Technical Support',
            'trainer' => 'Trainer',
            'travelagency' => 'Travel Agency',
            'videographer' => 'Videographer', 
            'webdesignagency' => 'Web Design Agency',
            'webdesignexpert' => 'Web Design Expert',
            'webdevelopmentagency' => 'Web Development Agency', 
            'webdevelopmentexpert' => 'Web Development Expert',
            'webdesigner' => 'Web Designer', 
            'webdeveloper' => 'Web Developer',
            'writer' => 'Writer'
        );        

        public $chat_tone_options = array(
            'friendly' => 'Friendly',
            'professional' => 'Professional',
            'sarcastic' => 'Sarcastic',
            'humorous' => 'Humorous',
            'cheerful' => 'Cheerful',
            'anecdotal' => 'Anecdotal'
        );

        public $wpaicg_writing_styles = array(
            'infor' => 'Informative',
            'acade' => 'Academic',
            'analy' => 'Analytical',
            'anect' => 'Anecdotal',
            'argum' => 'Argumentative',
            'artic' => 'Articulate',
            'biogr' => 'Biographical',
            'blog' => 'Blog',
            'casua' => 'Casual',
            'collo' => 'Colloquial',
            'compa' => 'Comparative',
            'conci' => 'Concise',
            'creat' => 'Creative',
            'criti' => 'Critical',
            'descr' => 'Descriptive',
            'detai' => 'Detailed',
            'dialo' => 'Dialogue',
            'direct' => 'Direct',
            'drama' => 'Dramatic',
            'evalu' => 'Evaluative',
            'emoti' => 'Emotional',
            'expos' => 'Expository',
            'ficti' => 'Fiction',
            'histo' => 'Historical',
            'journ' => 'Journalistic',
            'lette' => 'Letter',
            'lyric' => 'Lyrical',
            'metaph' => 'Metaphorical',
            'monol' => 'Monologue',
            'narra' => 'Narrative',
            'news' => 'News',
            'objec' => 'Objective',
            'pasto' => 'Pastoral',
            'perso' => 'Personal',
            'persu' => 'Persuasive',
            'poeti' => 'Poetic',
            'refle' => 'Reflective',
            'rheto' => 'Rhetorical',
            'satir' => 'Satirical',
            'senso' => 'Sensory',
            'simpl' => 'Simple',
            'techn' => 'Technical',
            'theore' => 'Theoretical',
            'vivid' => 'Vivid',
            'busin' => 'Business',
            'repor' => 'Report',
            'resea' => 'Research'
        );
    
        public $wpaicg_writing_tones = array(
            'formal' => 'Formal',
            'asser' => 'Assertive',
            'authoritative' => 'Authoritative',
            'cheer' => 'Cheerful',
            'confident' => 'Confident',
            'conve' => 'Conversational',
            'factual' => 'Factual',
            'friendly' => 'Friendly',
            'humor' => 'Humorous',
            'informal' => 'Informal',
            'inspi' => 'Inspirational',
            'neutr' => 'Neutral',
            'nostalgic' => 'Nostalgic',
            'polite' => 'Polite',
            'profe' => 'Professional',
            'romantic' => 'Romantic',
            'sarca' => 'Sarcastic',
            'scien' => 'Scientific',
            'sensit' => 'Sensitive',
            'serious' => 'Serious',
            'sincere' => 'Sincere',
            'skept' => 'Skeptical',
            'suspenseful' => 'Suspenseful',
            'sympathetic' => 'Sympathetic',
            'curio' => 'Curious',
            'disap' => 'Disappointed',
            'encou' => 'Encouraging',
            'optim' => 'Optimistic',
            'surpr' => 'Surprised',
            'worry' => 'Worried'
        );
      
        public $wpaicg_heading_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');

        public $wpaicg_image_sizes = [
            '256x256' => 'Small (256x256)',
            '512x512' => 'Medium (512x512)',
            '1024x1024' => 'Big (1024x1024)',
            '1792x1024' => 'Wide (1792x1024)',
            '1024x1792' => 'Tall (1024x1792)',
        ];

        public $wpaicg_image_styles = [
            '' => 'None',
            'abstract' => 'Abstract',
            'modern' => 'Modern',
            'impressionist' => 'Impressionist',
            'popart' => 'Pop Art',
            'cubism' => 'Cubism',
            'surrealism' => 'Surrealism',
            'contemporary' => 'Contemporary',
            'cantasy' => 'Fantasy',
            'graffiti' => 'Graffiti',
        ];

        public $playground_categories = [
            '' => 'Select a category',
            'wordpress' => 'WordPress',
            'blogging' => 'Blogging',
            'writing' => 'Writing',
            'ecommerce' => 'E-commerce',
            'online_business' => 'Online Business',
            'entrepreneurship' => 'Entrepreneurship',
            'seo' => 'SEO',
            'social_media' => 'Social Media',
            'digital_marketing' => 'Digital Marketing',
            'woocommerce' => 'WooCommerce',
            'content_strategy' => 'Content Strategy',
            'keyword_research' => 'Keyword Research',
            'product_listing' => 'Product Listing',
            'customer_relationship_management' => 'Customer Relationship Management',
        ];

        public $openai_gpt4_models = [
            'gpt-4' => 'GPT-4',
            'gpt-4o' => 'GPT-4o',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4-vision-preview' => 'GPT-4 Vision'
        ];

        public $openai_gpt35_models = [
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16K',
            'gpt-3.5-turbo-instruct' => 'GPT-3.5 Turbo Instruct'
        ];

        public $model_pricing = [
            'gpt-4' => 0.06,
            'gpt-4o' => 0.015,
            'gpt-4-32k' => 0.12,
            'gpt-4-1106-preview' => 0.06,
            'gpt-4-turbo' => 0.03,
            'gpt-4-vision-preview' => 0.06,
            'gpt-3.5-turbo' => 0.0015,
            'gpt-4-turbo-preview' => 0.03,
            'gpt-3.5-turbo-instruct' => 0.002,
            'gpt-3.5-turbo-16k' => 0.004,
            'text-davinci-003' => 0.02,
            'text-curie-001' => 0.002,
            'text-babbage-001' => 0.0005,
            'text-ada-001' => 0.0004,
            'gemini-pro' => 0.000375,
            'openrouter/auto' => 0,
            'nousresearch/nous-capybara-7b:free' => 0,
            'mistralai/mistral-7b-instruct:free' => 0,
            'openchat/openchat-7b:free' => 0,
            'gryphe/mythomist-7b:free' => 0,
            'undi95/toppy-m-7b:free' => 0,
            'openrouter/cinematika-7b:free' => 0,
            'google/gemma-7b-it:free' => 0,
            'meta-llama/llama-3-8b-instruct:free' => 0,
            'koboldai/psyfighter-13b-2' => 0.000002,
            'intel/neural-chat-7b' => 0.00001,
            'pygmalionai/mythalion-13b' => 0.00000225,
            'xwin-lm/xwin-lm-70b' => 0.0000075,
            'alpindale/goliath-120b' => 0.00001875,
            'neversleep/noromaid-20b' => 0.00000375,
            'gryphe/mythomist-7b' => 0.00000075,
            'sophosympatheia/midnight-rose-70b' => 0.000018,
            'sao10k/fimbulvetr-11b-v2' => 0.000001875,
            'neversleep/llama-3-lumimaid-8b' => 0.000001325625,
            'undi95/remm-slerp-l2-13b:extended' => 0.00000225,
            'gryphe/mythomax-l2-13b:extended' => 0.00000225,
            'meta-llama/llama-3-8b-instruct:extended' => 0.000001325625,
            'neversleep/llama-3-lumimaid-8b:extended' => 0.000001325625,
            'mancer/weaver' => 0.000004125,
            'nousresearch/nous-capybara-7b' => 0.00000036,
            'meta-llama/codellama-34b-instruct' => 0.00000144,
            'codellama/codellama-70b-instruct' => 0.00000162,
            'phind/phind-codellama-34b' => 0.00000144,
            'open-orca/mistral-7b-openorca' => 0.00000036,
            'teknium/openhermes-2-mistral-7b' => 0.00000036,
            'undi95/remm-slerp-l2-13b' => 0.00000054,
            'openrouter/cinematika-7b' => 0.00000036,
            '01-ai/yi-34b-chat' => 0.00000144,
            '01-ai/yi-34b' => 0.00000144,
            '01-ai/yi-6b' => 0.000000252,
            'togethercomputer/stripedhyena-nous-7b' => 0.00000036,
            'togethercomputer/stripedhyena-hessian-7b' => 0.00000036,
            'mistralai/mixtral-8x7b' => 0.00000108,
            'nousresearch/nous-hermes-yi-34b' => 0.00000144,
            'nousresearch/nous-hermes-2-mixtral-8x7b-sft' => 0.00000108,
            'nousresearch/nous-hermes-2-mistral-7b-dpo' => 0.00000036,
            'meta-llama/llama-3-8b' => 0.00000036,
            'meta-llama/llama-3-70b' => 0.00000162,
            'meta-llama/llama-guard-2-8b' => 0.00000036,
            'databricks/dbrx-instruct' => 0.00000216,
            'allenai/olmo-7b-instruct' => 0.00000036,
            'snowflake/snowflake-arctic-instruct' => 0.00000432,
            'qwen/qwen-110b-chat' => 0.00000324,
            'qwen/qwen-32b-chat' => 0.00000144,
            'qwen/qwen-14b-chat' => 0.00000054,
            'qwen/qwen-7b-chat' => 0.00000036,
            'qwen/qwen-4b-chat' => 0.00000018,
            'mistralai/mixtral-8x7b-instruct:nitro' => 0.00000108,
            'openai/gpt-3.5-turbo' => 0.000002,
            'openai/gpt-3.5-turbo-0125' => 0.000002,
            'openai/gpt-3.5-turbo-1106' => 0.000003,
            'openai/gpt-3.5-turbo-0613' => 0.000003,
            'openai/gpt-3.5-turbo-0301' => 0.000003,
            'openai/gpt-3.5-turbo-16k' => 0.000007,
            'openai/gpt-4o' => 0.00002,
            'openai/gpt-4o-2024-05-13' => 0.00002,
            'openai/gpt-4-turbo' => 0.00004,
            'openai/gpt-4-turbo-preview' => 0.00004,
            'openai/gpt-4-1106-preview' => 0.00004,
            'openai/gpt-4' => 0.00009,
            'openai/gpt-4-0314' => 0.00009,
            'openai/gpt-4-32k' => 0.00018,
            'openai/gpt-4-32k-0314' => 0.00018,
            'openai/gpt-4-vision-preview' => 0.00004,
            'openai/gpt-3.5-turbo-instruct' => 0.0000035,
            'google/palm-2-chat-bison' => 0.00000075,
            'google/palm-2-codechat-bison' => 0.00000075,
            'google/palm-2-chat-bison-32k' => 0.00000075,
            'google/palm-2-codechat-bison-32k' => 0.00000075,
            'google/gemini-pro' => 0.0000005,
            'google/gemini-pro-vision' => 0.0000005,
            'google/gemini-pro-1.5' => 0.00001,
            'google/gemini-flash-1.5' => 0.000001,
            'perplexity/llama-3-sonar-small-32k-chat' => 0.0000004,
            'perplexity/llama-3-sonar-small-32k-online' => 0.0000004,
            'perplexity/llama-3-sonar-large-32k-chat' => 0.000002,
            'perplexity/llama-3-sonar-large-32k-online' => 0.000002,
            'fireworks/firellava-13b' => 0.0000004,
            'anthropic/claude-3-opus' => 0.00009,
            'anthropic/claude-3-sonnet' => 0.000018,
            'anthropic/claude-3-haiku' => 0.0000015,
            'anthropic/claude-2' => 0.000032,
            'anthropic/claude-2.0' => 0.000032,
            'anthropic/claude-2.1' => 0.000032,
            'anthropic/claude-instant-1' => 0.0000032,
            'anthropic/claude-3-opus:beta' => 0.00009,
            'anthropic/claude-3-sonnet:beta' => 0.000018,
            'anthropic/claude-3-haiku:beta' => 0.0000015,
            'anthropic/claude-2:beta' => 0.000032,
            'anthropic/claude-2.0:beta' => 0.000032,
            'anthropic/claude-2.1:beta' => 0.000032,
            'anthropic/claude-instant-1:beta' => 0.0000032,
            'openai/gpt-3.5' => 0.000002,
            'openai/gpt-3.5-1106' => 0.000003,
            'openai/gpt-3.5-16k' => 0.000007,
            'openai/gpt-4-1106' => 0.00004,
            'openai/gpt-4-32k-1106' => 0.00018,
            'openai/gpt-4-vision' => 0.00004,
            'openai/gpt-4-turbo-1106' => 0.00004,
            'openai/gpt-4-32k-1106-preview' => 0.00018,
        ];

        public $max_token_values = [
            'gpt-4' => 8192,
            'gpt-4o' => 8192,
            'gpt-4-32k'=> 32768,
            'gpt-3.5-turbo'=> 4096,
            'gpt-4-turbo'=> 4096,
            'gpt-4-vision-preview'=> 4096,
            'gpt-3.5-turbo-16k' => 16384,
            'gpt-3.5-turbo-instruct'=> 4096,
            'text-davinci-003'=> 4000,
            'text-curie-001'=> 2048,
            'text-babbage-001'=> 2048,
            'text-ada-001'=> 2048,
            'gemini-pro' => 2048,
            'gemini-1.0-pro' => 2048,
            'gemini-1.0-pro-001' => 2048,
            'gemini-1.0-pro-latest' => 2048,
            'gemini-1.0-pro-vision-latest' => 2048,
            'gemini-1.5-flash-latest' => 8191,
            'gemini-1.5-pro-latest' => 8191,
            'gemini-pro-vision' => 2048,
        ];
    }
}
if(!function_exists(__NAMESPACE__.'\wpaicg_util_core')){
    function wpaicg_util_core(){
        return WPAICG_Util::get_instance();
    }
}
