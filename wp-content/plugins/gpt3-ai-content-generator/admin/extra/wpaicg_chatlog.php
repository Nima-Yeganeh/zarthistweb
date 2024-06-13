<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Verify nonce
if (isset($_GET['wsearch']) && !empty($_GET['wsearch']) && !wp_verify_nonce($_GET['wpaicg_nonce'], 'wpaicg_chatlogs_search_nonce')) {
    die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
}

global $wpdb;

// Check if the chatlogs table exists
$wpaicgChatLogsTable = $wpdb->prefix . 'wpaicg_chatlogs';
if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgChatLogsTable)) != $wpaicgChatLogsTable) {
    echo '<div class="notice notice-info is-dismissible">
        <p>'. esc_html__('The chat logs table does not exist. Please deactivate and then reactivate the plugin to trigger the table creation.', 'gpt3-ai-content-generator') .'</p>
    </div>';
    return; // Exit so that the rest of the code doesn't run
}


$wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');

$azure_deployment_name = get_option('wpaicg_azure_deployment', '');

// Define pricing per 1K tokens
$pricing = \WPAICG\WPAICG_Util::get_instance()->model_pricing;

// Retrieve custom models from WordPress option
$custom_models = get_option('wpaicg_custom_models', array());

// Add custom models to the pricing array with a fixed cost of $0.008
foreach ($custom_models as $custom_model) {
    $pricing[$custom_model] = 0.008;
}

$google_models = get_option('wpaicg_google_model_list', array());

foreach ($google_models as $google_model) {
    $pricing[$google_model] = 0.002;
}

// Add the Azure deployment name to the pricing array
if (!empty($azure_deployment_name)) {
    $pricing[$azure_deployment_name] = 0.004;
}

// Initialize the counters
$total_tokens_today = 0;
$total_tokens_week = 0;
$total_tokens_month = 0;
$total_tokens_overall = 0;

$wpaicg_current_time = strtotime(gmdate("Y-m-d H:i:s"));
$wpaicg_today_start = strtotime(gmdate("Y-m-d 00:00:00"));
$wpaicg_today_end = strtotime(gmdate("Y-m-d 23:59:59"));
$wpaicg_week_start = strtotime('-1 week', $wpaicg_current_time);
$wpaicg_month_start = strtotime('-1 month', $wpaicg_current_time);

// Query to get logs of today
$today_logs_query = "SELECT `data` FROM ".$wpdb->prefix."wpaicg_chatlogs WHERE created_at >= %d AND created_at <= %d";
$today_logs = $wpdb->get_results($wpdb->prepare($today_logs_query, $wpaicg_today_start, $wpaicg_today_end));

// Loop through the logs and extract token values, then sum them up
foreach($today_logs as $log) {
    $data = json_decode($log->data, true);
    foreach($data as $item) {
        if(isset($item['token'])) {
            $total_tokens_today += $item['token'];
        }
    }
}

// Query to get logs of the past week
$week_logs_query = "SELECT `data` FROM ".$wpdb->prefix."wpaicg_chatlogs WHERE created_at >= %d AND created_at <= %d";
$week_logs = $wpdb->get_results($wpdb->prepare($week_logs_query, $wpaicg_week_start, $wpaicg_current_time));

foreach($week_logs as $log) {
    $data = json_decode($log->data, true);
    foreach($data as $item) {
        if(isset($item['token'])) {
            $total_tokens_week += $item['token'];
        }
    }
}

// Query to get logs of the past month
$month_logs_query = "SELECT `data` FROM ".$wpdb->prefix."wpaicg_chatlogs WHERE created_at >= %d AND created_at <= %d";
$month_logs = $wpdb->get_results($wpdb->prepare($month_logs_query, $wpaicg_month_start, $wpaicg_current_time));

foreach($month_logs as $log) {
    $data = json_decode($log->data, true);
    foreach($data as $item) {
        if(isset($item['token'])) {
            $total_tokens_month += $item['token'];
        }
    }
}

// Query to get all logs
$all_logs_query = "SELECT `data` FROM ".$wpdb->prefix."wpaicg_chatlogs";
$all_logs = $wpdb->get_results($all_logs_query);

// Now, calculate costs for various periods
$wpaicg_cost_today = wpaicgcalculateCost($today_logs, $pricing);
$wpaicg_cost_week = wpaicgcalculateCost($week_logs, $pricing);
$wpaicg_cost_month = wpaicgcalculateCost($month_logs, $pricing);
$wpaicg_cost_overall = wpaicgcalculateCost($all_logs, $pricing);

foreach($all_logs as $log) {
    $data = json_decode($log->data, true);
    foreach($data as $item) {
        if(isset($item['token'])) {
            $total_tokens_overall += $item['token'];
        }
    }
}

function wpaicgcalculateCost($logs, $pricing) {
    $total_cost = 0;
    
    foreach($logs as $log) {
        $data = json_decode($log->data, true);
        foreach($data as $item) {
            if(isset($item['token'], $item['request']['model']) && array_key_exists($item['request']['model'], $pricing)) {
                $cost_per_token = $pricing[$item['request']['model']] / 1000; // Cost per token since pricing is per 1K tokens
                $total_cost += $item['token'] * $cost_per_token;
            }
        }
    }

    return $total_cost;
}

$wpaicg_log_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$search = isset($_GET['wsearch']) && !empty($_GET['wsearch']) ? sanitize_text_field($_GET['wsearch']) : '';
$where = '';
if(!empty($search)) {
    $where .= $wpdb->prepare(" AND `data` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}

// Filtering by Date Range
if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = sanitize_text_field($_GET['start_date']);
    $start_timestamp = strtotime($start_date);  // Start of the selected start date
    $where .= $wpdb->prepare(" AND created_at >= %d", $start_timestamp);
}

if(isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = sanitize_text_field($_GET['end_date']);
    $end_of_day_timestamp = strtotime($end_date . ' +1 day');  // Start of the next day after the selected end date
    $where .= $wpdb->prepare(" AND created_at <= %d", $end_of_day_timestamp);
}

$source = '';
if (isset($_GET['source']) && !empty($_GET['source'])) {
    $source = sanitize_text_field($_GET['source']);
}

// Filtering by Source
if (!empty($source)) {
    if ($source == "shortcode") {
        $where .= " AND source LIKE 'Shortcode%'";
    } else {
        $where .= $wpdb->prepare(" AND source = %s", $source);
    }
}


$query = "SELECT * FROM ".$wpdb->prefix."wpaicg_chatlogs WHERE 1=1".$where;
$total_query = "SELECT COUNT(1) FROM ({$query}) AS combined_table";
$total = $wpdb->get_var( $total_query );
$items_per_page = 10;
$offset = ( $wpaicg_log_page * $items_per_page ) - $items_per_page;
$wpaicg_logs = $wpdb->get_results( $wpdb->prepare( $query . " ORDER BY created_at DESC LIMIT %d, %d", $offset, $items_per_page ) );
$totalPage = ceil($total / $items_per_page);

?>
<style>
    .wpaicg_modal{
        top: 5%;
        height: 90%;
        position: relative;
    }
    .wpaicg_modal_content{
        max-height: calc(100% - 103px);
        overflow-y: auto;
    }
    .wpaicg_message code{
        padding: 3px 5px 2px;
        background: rgb(0 0 0 / 20%);
        font-size: 13px;
        font-family: Consolas,Monaco,monospace;
        direction: ltr;
        unicode-bidi: embed;
        display: block;
        margin: 5px 0px;
        border-radius: 4px;
        white-space: pre-wrap;
    }
    .wpaicg-spacing {
        margin-right: 10px; /* adjust this value as per your needs */
    }
    .wpaicg-d-flex .button.wpaicg-spacing {
        margin-right: 10px;
    }
    .wpaicg_button-danger {
    background-color: #dc3232; /* WordPress red color */
    border-color: #a00a0a;    /* Darker red border */
    color: #ffffff;           /* White text */
    text-decoration: none;
    }

    .wpaicg-d-flex .button.wpaicg_button-danger {
        background-color: #dc3232;
        border-color: #a00a0a;
        color: #ffffff;
        text-decoration: none;
    }

    .wpaicg-d-flex .button.wpaicg_button-danger:hover, 
    .wpaicg-d-flex .button.wpaicg_button-danger:focus {
        background-color: #a00a0a; /* Darker red for hover and focus */
        border-color: #a00a0a; 
        color: #ffffff;
        text-decoration: none;
    }
    .wpaicg-tooltip {
    position: relative;
    display: inline-block;
    cursor: pointer;
    color: blue;
    border-bottom: 1px dotted blue;
}

.wpaicg-tooltip .wpaicg-tooltiptext {
    visibility: hidden;
    width: 400px;
    background-color: #f5f5f5; /* Light gray background */
    color: #333; /* Darker font color for contrast */
    text-align: center;
    border-radius: 6px;
    padding: 10px;
    position: absolute;
    z-index: 1;
    left: 50%;
    margin-left: -175px;
    opacity: 0;
    transition: opacity 0.3s;
    top: 30px; /* Shift the tooltip down by 30 pixels */
    }
    .wpaicg-tooltip .wpaicg-tooltiptext table {
        width: 100%;
        border-collapse: collapse;
    }

    .wpaicg-tooltip .wpaicg-tooltiptext th, 
    .wpaicg-tooltip .wpaicg-tooltiptext td {
        border: 1px solid #ccc; /* Slightly lighter border color */
        background-color: #e9e9e9; /* Light background for table headers */
        color: #333; /* Darker font color for table headers and cells */
    }

    .wpaicg-tooltip:hover .wpaicg-tooltiptext {
        visibility: visible;
        opacity: 1;
    }
    .wpaicg-tokens-display {
    display: flex;
    justify-content: space-between; /* Distributes the items evenly */
    align-items: center; /* Vertically center the content */
    margin-bottom: 15px; /* Space between this container and the search box */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    }

    .wpaicg-token-item {
        flex: 1; /* Makes sure each item takes equal width */
        text-align: center;
        position: relative; /* To position the pseudo-element correctly */
    }

    .wpaicg-token-count {
        font-weight: bold;
        font-size: 1.2em;
    }

    .wpaicg-token-label {
        font-size: 0.9em;
        color: #777;
    }

    .wpaicg-tokens-header {
        text-align: center;
        font-size: 1.4em;
        margin-bottom: 10px; /* Spacing between header and items */
        font-weight: bold;
        flex: 0 0 auto; /* This prevents the header from growing and shrinking */
        padding-right: 10px; /* Adds some space between the header and the vertical line */
        position: relative; /* To position the pseudo-element correctly */
    }

    .wpaicg-tokens-header:nth-of-type(2)::before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 80%; /* Adjust this if you want a shorter or longer line */
        width: 1px;
        background-color: #ccc;
    }

</style>
<?php
// Check for the "logs deleted" transient
if (get_transient('wpaicg_logs_deleted')) {
    echo '<div class="notice notice-success is-dismissible">
            <p>'.esc_html__('All logs have been deleted.', 'gpt3-ai-content-generator').'</p>
          </div>';
    delete_transient('wpaicg_logs_deleted');
}
// Check for the "log deleted" transient
if (get_transient('wpaicg_log_deleted')) {
    echo '<div class="notice notice-success is-dismissible">
            <p>' . esc_html__('The log has been deleted.', 'gpt3-ai-content-generator') . '</p>
          </div>';
    delete_transient('wpaicg_log_deleted');
}

// Check for the "logs exported" transient
$file_url = get_transient('wpaicg_logs_exported_url');
if ($file_url) {
    echo '<div class="notice notice-success is-dismissible">
            <p>' . esc_html__('Logs have been successfully exported. ', 'gpt3-ai-content-generator') . '<a href="' . esc_url($file_url) . '" download>Click here to download</a></p>
          </div>';
    delete_transient('wpaicg_logs_exported_url');
}
?>
<div class="wpaicg-d-flex mb-5">
    <?php 
    // Check if any filter is active
    if (isset($_GET['wsearch']) || isset($_GET['start_date']) || isset($_GET['end_date']) || isset($_GET['source'])): 
    ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php 
                if ($total == 0 || $total == 1) {
                    echo sprintf(esc_html__('%d record found', 'gpt3-ai-content-generator'), $total);
                } else {
                    echo sprintf(esc_html__('%d records found', 'gpt3-ai-content-generator'), $total);
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>
<!-- HTML for the tokens display -->
<div class="wpaicg-tokens-display">
    <div class="wpaicg-tokens-header"><?php echo esc_html__('Estimated Cost:', 'gpt3-ai-content-generator'); ?></div>
    
    <div class="wpaicg-token-item">
        <div class="wpaicg-token-count">$<?php echo number_format($wpaicg_cost_today, 4); ?></div>
        <div class="wpaicg-token-label"><?php echo esc_html__('Today', 'gpt3-ai-content-generator'); ?></div>
    </div>

    <div class="wpaicg-token-item">
        <div class="wpaicg-token-count">$<?php echo number_format($wpaicg_cost_week, 4); ?></div>
        <div class="wpaicg-token-label"><?php echo esc_html__('This Week', 'gpt3-ai-content-generator'); ?></div>
    </div>

    <div class="wpaicg-token-item">
        <div class="wpaicg-token-count">$<?php echo number_format($wpaicg_cost_month, 4); ?></div>
        <div class="wpaicg-token-label"><?php echo esc_html__('This Month', 'gpt3-ai-content-generator'); ?></div>
    </div>

    <div class="wpaicg-token-item">
        <div class="wpaicg-token-count">$<?php echo number_format($wpaicg_cost_overall, 4); ?></div>
        <div class="wpaicg-token-label"><?php echo esc_html__('Overall', 'gpt3-ai-content-generator'); ?></div>
    </div>
</div>
<form action="" method="get">
    <input type="hidden" name="page" value="wpaicg_chatgpt">
    <input type="hidden" name="action" value="logs">
    <?php wp_nonce_field('wpaicg_chatlogs_search_nonce', 'wpaicg_nonce'); ?>
    <?php wp_nonce_field('wpaicg_chatlogs_delete_nonce', 'wpaicg_delete_nonce'); ?>
    <div class="wpaicg-d-flex mb-5">
        <!-- Search -->
        <input style="width: 100%" value="<?php echo esc_html($search)?>" class="regular-text wpaicg-spacing" name="wsearch" type="text" placeholder="<?php echo esc_html__('Enter search term','gpt3-ai-content-generator')?>">
        
        <!-- Start Date -->
        <input style="width: 15%" type="date" class="wpaicg-spacing" name="start_date" value="<?php echo isset($_GET['start_date']) ? esc_html($_GET['start_date']) : ''; ?>">
        <!-- End Date -->
        <input style="width: 15%" type="date" class="wpaicg-spacing" name="end_date" value="<?php echo isset($_GET['end_date']) ? esc_html($_GET['end_date']) : ''; ?>">
        
        <!-- Type -->
        <select name="source" class="wpaicg-spacing">
            <option value=""><?php echo esc_html__('Select Type', 'gpt3-ai-content-generator'); ?></option>
            <option value="widget" <?php selected(isset($_GET['source']) ? $_GET['source'] : '', 'widget'); ?>><?php echo esc_html__('Widget','gpt3-ai-content-generator'); ?></option>
            <option value="shortcode" <?php selected(isset($_GET['source']) ? $_GET['source'] : '', 'shortcode'); ?>><?php echo esc_html__('Shortcode','gpt3-ai-content-generator'); ?></option>
        </select>
        
        <button class="button button-primary wpaicg-spacing"><?php echo esc_html__('Search','gpt3-ai-content-generator')?></button>
        <?php
            // Generate a nonce
            $export_nonce = wp_create_nonce('wpaicg_export_logs_nonce');
        ?>
        <!-- Export Logs Button -->
        <button id="wpaicg_export_btn" class="button button-primary wpaicg-spacing" data-nonce="<?php echo esc_attr($export_nonce); ?>">
            <?php echo esc_html__('Export','gpt3-ai-content-generator')?>
        </button>
        <!-- Delete All Logs Button -->
        <?php if($total > 0): ?>
            <button type="submit" name="wpaicg_delete_all_logs" id="deleteAllLogsButton" class="button wpaicg_button-danger wpaicg-spacing" onclick="return wpaicgConfirmDelete();">
                <?php echo esc_html__('Delete All', 'gpt3-ai-content-generator'); ?>
            </button>
        <?php endif; ?>
    </div>
</form>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
    <tr>
        <th><?php echo esc_html__('ID','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Date','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Username','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Message','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('AI','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Page','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Type','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Token','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Estimated','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Question','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('IP','gpt3-ai-content-generator')?></th>
        <?php
            $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');  // Fetching the provider

            if (\WPAICG\wpaicg_util_core()->wpaicg_is_pro() && $wpaicg_provider === 'OpenAI'):
            ?>
                <th><?php echo esc_html__('Moderation','gpt3-ai-content-generator')?></th>
            <?php
            endif;
        ?>
        <th><?php echo esc_html__('Action','gpt3-ai-content-generator')?></th>
    </tr>
    </thead>
    <tbody class="wpaicg-builder-list">
    <?php
    if($wpaicg_logs && is_array($wpaicg_logs) && count($wpaicg_logs)){
        foreach ($wpaicg_logs as $wpaicg_log){
            $estimated_total = 0; // Initialize the total estimated cost
            $wpaicg_flagged = false;
            $last_user_message = '';
            $ip_address = '';
            $last_ai_message = '';
            $all_messages = json_decode($wpaicg_log->data,true);
            $all_messages = $all_messages && is_array($all_messages) ? $all_messages : array();

            $username = null; // Initialize as null
            foreach(array_reverse($all_messages) as $item) {
                if(isset($item['type']) && $item['type'] === 'user' && isset($item['username'])) {
                    $username = $item['username'];
                    break; // Exit the loop once we find the username
                }
            }
            $tokens = 0;

            $total_tokens = 0; // Initialize the variable here
            foreach($all_messages as $item) {
                if(isset($item['token']) && !empty($item['token']) && isset($item['request']['model'])) {
                    $model = $item['request']['model'];

                    // Sum up the token values
                    $total_tokens += $item['token'];
            
                    if(isset($pricing[$model])) {
                        $estimated_total += ($item['token'] / 1000) * $pricing[$model];
                    }
                }
            }

            $question_count = 0; // Initialize the count for each log entry
            
            foreach($all_messages as $item) {
                if(isset($item['type']) && $item['type'] == 'user') {
                    $question_count++;
                }
            }

            $breakdown = "<table style='width:100%; border-collapse: collapse;'>";
            $breakdown .= "<thead><tr><th>" . esc_html__('Question', 'gpt3-ai-content-generator') . "</th><th>" . esc_html__('Provider', 'gpt3-ai-content-generator') . "</th><th>" . esc_html__('Model', 'gpt3-ai-content-generator') . "</th><th>" . esc_html__('Token', 'gpt3-ai-content-generator') . "</th><th>" . esc_html__('Cost', 'gpt3-ai-content-generator') . "</th></tr></thead>";
            $breakdown .= "<tbody>";
            
            $question_number = 0; // Initialize the question counter
            $displayed_records = 0; // Initialize counter for displayed records
            
            foreach($all_messages as $item) {
                if(isset($item['token']) && !empty($item['token']) && isset($item['request']['model'])) {
                    $model = $item['request']['model'];
                    $tokens = $item['token'];
                    // Check if the model exists in the pricing array
                    $pricePerToken = isset($pricing[$model]) ? $pricing[$model] : 0.004;

                    $cost = ($tokens / 1000) * $pricePerToken;

                    $question_number++; // Increment the question counter for each entry
            
                    // Only append to the breakdown string if tokens were used for that model
                    if ($tokens > 0) {
                        $breakdown .= "<tr>";
                        $breakdown .= "<td>" . esc_html($question_number) . "</td>";  // Display the row number
                        $provider = isset($item['request']['provider']) ? $item['request']['provider'] : '';  // Set a default value if 'provider' is not set
                        $breakdown .= "<td>" . esc_html($provider) . "</td>";
                        $breakdown .= "<td>" . esc_html($model) . "</td>";
                        $breakdown .= "<td>" . esc_html($tokens) . "</td>";
                        $breakdown .= "<td>$" . esc_html(number_format($cost, 6)) . "</td>";
                        $breakdown .= "</tr>";

                        $displayed_records++; // Increment the counter for displayed records
                    }

                    // Break the loop if 10 records have been displayed
                    if($displayed_records >= 10) {
                        break;
                    }
                }
            }
            $breakdown .= "</tbody></table>";
            
            // Append the notice if 10 records have been displayed
            if ($displayed_records >= 10) {
                $breakdown .= "<div style='text-align: center; font-size: 12px; margin-top: 10px;'>" . esc_html__('Only first 10 records being shown', 'gpt3-ai-content-generator') . "</div>";
            }
            
            foreach(array_reverse($all_messages) as $item){
                if(isset($item['flag']) && !empty($item['flag'])){
                    $wpaicg_flagged = $item['flag'];
                }
            }
            foreach(array_reverse($all_messages) as $item){
                if(
                    isset($item['type'])
                    && $item['type'] == 'user'
                    && empty($last_user_message)
                ){
                    $last_user_message = $item['message'];
                    $ip_address = isset($item['ip']) ? $item['ip'] : '';
                }

                if(
                    isset($item['type'])
                    && $item['type'] == 'ai'
                    && empty($last_ai_message)
                ){
                    $last_ai_message = $item['message'];
                }
                if(!empty($last_ai_message) && !empty($last_user_message)){
                    break;
                }
                if(isset($item['token']) && !empty($item['token'])){
                    $tokens += $item['token'];
                }

            }
            $estimated = number_format($estimated_total, 6); // Calculate the total estimated cost for the log entry
            ?>
            <tr>
                <td><?php echo esc_html($wpaicg_log->id)?></td>
                <td><?php echo esc_html(gmdate('d.m.Y H:i',$wpaicg_log->created_at))?></td>
                <td><?php echo $username ? esc_html($username) : 'Guest' ?></td>
                <td><?php echo esc_html(substr($last_user_message, 0, 70)) . (strlen($last_user_message) > 70 ? '...' : '') ?></td>
                <td><?php echo esc_html(substr($last_ai_message, 0, 70)) . (strlen($last_ai_message) > 70 ? '...' : '') ?></td>
                <td><?php echo esc_html($wpaicg_log->page_title)?></td>
                <td><?php echo $wpaicg_log->source == 'widget' ? esc_html__('Widget','gpt3-ai-content-generator') : ($wpaicg_log->source == 'shortcode' ? esc_html__('Shortcode','gpt3-ai-content-generator') : esc_html($wpaicg_log->source))?></td>
                <td><?php echo $total_tokens > 0 ? esc_html($total_tokens) : '--'?></td>
                <td>
                    <?php echo $estimated > 0 ? '$'.esc_html($estimated) : '--'?>
                    <span class="wpaicg-tooltip">
                        ?
                        <span class="wpaicg-tooltiptext">
                            <?php echo $breakdown; ?>
                        </span>
                    </span>
                </td>
                <td><?php echo esc_html($question_count)?></td>
                <td><?php echo esc_html($ip_address)?></td>
                <?php
                    $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');  // Fetching the provider

                    if (\WPAICG\wpaicg_util_core()->wpaicg_is_pro() && $wpaicg_provider === 'OpenAI'):
                    ?>
                        <td><?php echo $wpaicg_flagged ? '<span style="font-weight: bold;color: #f00;">'.esc_html__('Flagged','gpt3-ai-content-generator').'</span>':'<span style="font-weight: bold;color: #47a700;">'.esc_html__('Passed','gpt3-ai-content-generator').'</span>'?></td>
                    <?php
                    endif;
                    ?>
                <td>
                    <button class="button button-secondary button-small wpaicg-log-messages" data-messages="<?php echo esc_html(htmlspecialchars(json_encode($all_messages),ENT_QUOTES, 'UTF-8'))?>" title="View">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=wpaicg_chatgpt&action=logs&delete_log_id=' . esc_attr($wpaicg_log->id) . '&wpaicg_delete_nonce=' . wp_create_nonce('wpaicg_chatlogs_delete_nonce')); ?>" class="button wpaicg_button-danger button-small" onclick="return confirm('Are you sure you want to delete this log? This action cannot be undone.');" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z"/></svg>
                    </a>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
<div class="wpaicg-paginate">
<?php
if($totalPage > 1){
    echo paginate_links( array(
        'base'         => admin_url('admin.php?page=wpaicg_chatgpt&action=logs&wpage=%#%'),
        'total'        => $totalPage,
        'current'      => $wpaicg_log_page,
        'format'       => '?wpage=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
}
?>
</div>
<script>
    jQuery(document).ready(function ($){
        function htmlEntities(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        $('.wpaicg_modal_close').click(function (){
            $('.wpaicg_modal_close').closest('.wpaicg_modal').hide();
            $('.wpaicg-overlay').hide();
        });
        function wpaicgReplaceStr(str) {
            str = str.replace(/\\n/g,'---NEWLINE---');
            str = str.replace(/\n/g,'---NEWLINE---');
            str = str.replace(/\t/g,'---NEWTAB---');
            str = str.replace(/\\t/g,'---NEWTAB---');
            str = str.replace(/\\/g,'');
            str = str.replace(/---NEWLINE---/g,"\n");
            str = str.replace(/---NEWTAB---/g,"\t");
            return str;
        };
        function wpaicgConfirmDelete() {
            let wpaicgBaseMessage = 'Are you sure you want to delete the following logs? This action cannot be undone.\n\n';
            let wpaicgDetails = '';
            
            // Check for search term
            let wsearchValue = $('[name="wsearch"]').val();
            if (wsearchValue !== '') {
                wpaicgDetails += '- Logs containing the search term: ' + wsearchValue + '\n';
            }
            
            // Check for date range
            let startDateValue = $('[name="start_date"]').val();
            if (startDateValue !== '') {
                wpaicgDetails += '- Logs after: ' + startDateValue + '\n';
            }
            let endDateValue = $('[name="end_date"]').val();
            if (endDateValue !== '') {
                wpaicgDetails += '- Logs before: ' + endDateValue + '\n';
            }
            
            // Check for source/type
            let sourceValue = $('[name="source"]').val();
            if (sourceValue !== '') {
                let sourceText = $('[name="source"] option:selected').text();
                wpaicgDetails += '- Logs of type: ' + sourceText + '\n';
            }

            if (wpaicgDetails === '') {
                return confirm('Are you sure you want to delete all logs? This action cannot be undone.');
            } else {
                return confirm(wpaicgBaseMessage + wpaicgDetails);
            }
        }

        // Binding the function to the button's click event
        $('#deleteAllLogsButton').click(function(e){
            // If the user does not confirm, prevent the default action
            if (!wpaicgConfirmDelete()) {
                e.preventDefault();
            }
        });

        // Export logs
        $('#wpaicg_export_btn').click(function(e) {
            e.preventDefault();
            var nonce = $(this).data('nonce');
            $.post(ajaxurl, {
                action: 'wpaicg_export_logs',
                nonce: nonce // Corrected the placement of nonce here
            }, function(response) {
                // Redirect to the provided URL
                window.location.href = response;
            });
        });

        $('.wpaicg-log-messages').click(function (){
            var wpaicg_messages = $(this).attr('data-messages');
            if(wpaicg_messages !== ''){
                wpaicg_messages = JSON.parse(wpaicg_messages);
                var html = '';
                $('.wpaicg_modal_title').html('<?php echo esc_html__('View Chat Log','gpt3-ai-content-generator')?>');
                $.each(wpaicg_messages, function (idx, item){
                    html += '<div class="wpaicg_message" style="margin-bottom: 10px;">';
                    if(item.type === 'ai'){
                        html += '<strong><?php echo esc_html__('AI','gpt3-ai-content-generator')?>:</strong>&nbsp;';
                    }
                    else{
                        html += '<strong><?php echo esc_html__('User','gpt3-ai-content-generator')?>:</strong>&nbsp;';
                    }
                    let html_Entities = htmlEntities(item.message);
                    html_Entities = html_Entities.replace(/\\/g,'');
                    html += html_Entities.replace(/```([\s\S]*?)```/g,'<code>$1</code>');
                    if(typeof item.flag !== "undefined" && item.flag !== '' && item.flag !== false){
                        html += '<span style="display: inline-block;font-size: 12px;font-weight: bold;background: #b71a1a;padding: 1px 5px;border-radius: 3px;color: #fff;margin-left: 5px;"><?php echo esc_html__('Flagged as','gpt3-ai-content-generator')?> '+item.flag+'<span>';
                    }
                    if(typeof item.request !== "undefined" && typeof item.request === 'object'){
                        html += '<a href="javascript:void(0)" class="show_message_request">[<?php echo esc_html__('details','gpt3-ai-content-generator')?>]</a>';
                        html += '<div class="wpaicg_request" style="display: none;padding: 10px;background: #e9e9e9;border-radius: 4px;"><pre style="white-space: pre-wrap">'+wpaicgReplaceStr(JSON.stringify(item.request,undefined, 4))+'</pre></div>';
                    }
                    html += '</div>';
                })
                $('.wpaicg_modal_content').html(html);
                $('.wpaicg-overlay').show();
                $('.wpaicg_modal').show();
            }
        });
        $(document).on('click','.show_message_request', function (e){
            let el = $(e.currentTarget);
            if(el.hasClass('activeated')){
                el.removeClass('activeated');
                el.html('[<?php echo esc_html__('details','gpt3-ai-content-generator')?>]');
                el.closest('.wpaicg_message').find('.wpaicg_request').slideUp();
            }
            else{
                el.addClass('activeated');
                el.html('[<?php echo esc_html__('hide','gpt3-ai-content-generator')?>]');
                el.closest('.wpaicg_message').find('.wpaicg_request').slideDown();
            }
        })
    })
</script>
