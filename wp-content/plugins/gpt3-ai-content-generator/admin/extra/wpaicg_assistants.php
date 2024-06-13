<?php
if (!defined('ABSPATH')) exit;

// Retrieve assistants data from wp_options table
$assistants = get_option('wpaicg_assistants', array());

// Sort assistants by descending order of 'created_at'
usort($assistants, function ($a, $b) {
    return $b['created_at'] <=> $a['created_at'];
});

// Pagination settings
$per_page = 5;
$total_assistants = count($assistants);
$total_pages = ceil($total_assistants / $per_page);

// Get current page from URL, default is 1
$current_page = isset($_GET['page_num']) ? max((int)$_GET['page_num'], 1) : 1;
$current_page = min($current_page, $total_pages); // Don't exceed total pages

// Calculate the start index
$start_index = ($current_page - 1) * $per_page;

// Slice the assistants array for the current page display
$display_assistants = array_slice($assistants, $start_index, $per_page);

?>
<style>
    .wpaicg_spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #2271b1;
        border-top: 3px solid #2271b1; /* Black color for the top border */
        animation: wpaicg_spin 1s ease-in-out infinite;
        -webkit-animation: wpaicg_spin 1s ease-in-out infinite;
        box-shadow: 0 0 3px #2271b1; /* Optional: Adding a subtle shadow for more depth */
    }

    @keyframes wpaicg_spin {
        to { -webkit-transform: rotate(360deg); }
    }
    @-webkit-keyframes wpaicg_spin {
        to { -webkit-transform: rotate(360deg); }
    }

    /* Additional styles for editable fields */
    .wp-list-table .editable-field input,
    .wp-list-table .editable-field textarea,
    .wp-list-table .editable-field select {
        max-width: 100%; /* Ensures the element does not exceed the column width */
        box-sizing: border-box; /* Includes padding and border in the element's total width and height */
    }

    .wp-list-table .editable-field textarea {
        width: 100%; /* Full width of the column */
        height: 100px; /* Adjustable height */
    }

    .wp-list-table .editable-field select {
        width: 100%; /* Full width of the column */
    }
    .message-spacing {
        margin-left: 10px; /* Adjust the value as needed for desired spacing */
    }
</style>

<div class="wrap">
    <h1><?php echo esc_html__('Assistants', 'gpt3-ai-content-generator'); ?></h1>
    <form method="post" style="margin-bottom: 20px;">
        <?php wp_nonce_field('wpaicg_list_assistants_action', 'wpaicg_list_assistants_nonce'); ?>
        <input type="submit" id="wpaicg_syncButton" name="wpaicg_list_assistants" class="button button-primary" value="<?php echo esc_attr__('Sync', 'gpt3-ai-content-generator'); ?>">
        <span id="wpaicg_syncSpinner" class="wpaicg_spinner" style="display: none;"></span>
        <button type="button" id="wpaicg_show_create_form" class="button button-secondary"><?php echo esc_attr__('Create New', 'gpt3-ai-content-generator'); ?></button>
    </form>
    <!-- Create New Assistant Form -->
    <div id="wpaicg_create_assistant_form_section" style="margin-top: 20px; display:none;">
        <form id="wpaicg_create_assistant_form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('wpaicg_create_assistant_action', 'wpaicg_create_assistant_nonce'); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="wpaicg_assistant_name"><?php echo esc_html__('Assistant Name', 'gpt3-ai-content-generator'); ?></label></th>
                        <td><input type="text" id="wpaicg_assistant_name" name="wpaicg_assistant_name" required maxlength="256"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpaicg_assistant_instructions"><?php echo esc_html__('Instructions', 'gpt3-ai-content-generator'); ?></label></th>
                        <td>
                            <textarea id="wpaicg_assistant_instructions" name="wpaicg_assistant_instructions" required style="width: 50%; height: 150px;" maxlength="32768"></textarea>
                        </td>
                    </tr>
                    <tr>
                    <th scope="row"><label for="wpaicg_assistant_model"><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label></th>
                        <td>
                            <select id="wpaicg_assistant_model" name="wpaicg_assistant_model" required>
                                <optgroup label="GPT-4">
                                    <option value="gpt-4">gpt-4</option>
                                    <option value="gpt-4-1106-preview" selected>gpt-4-1106-preview</option>
                                    <option value="gpt-4-0613">gpt-4-0613</option>
                                    <option value="gpt-4-0314">gpt-4-0314</option>
                                </optgroup>
                                <optgroup label="GPT-3.5">
                                    <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
                                    <option value="gpt-3.5-turbo-16k">gpt-3.5-turbo-16k</option>
                                    <option value="gpt-3.5-turbo-1106">gpt-3.5-turbo-1106</option>
                                    <option value="gpt-3.5-turbo-0613">gpt-3.5-turbo-0613</option>
                                    <option value="gpt-3.5-turbo-16k-0613">gpt-3.5-turbo-16k-0613</option>
                                </optgroup>
                            </select>
                        </td>
                    </tr>
                    <tr>
                    <tr>
                    <th scope="row"><?php echo esc_html__('Tools (Optional)', 'gpt3-ai-content-generator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="wpaicg_assistant_tool_code" name="wpaicg_assistant_tool_code" value="code_interpreter"> Code Interpreter
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" id="wpaicg_assistant_tool_retrieval" name="wpaicg_assistant_tool_retrieval" value="retrieval"> Retrieval
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpaicg_assistant_file"><?php echo esc_html__('Upload File (Optional)', 'gpt3-ai-content-generator'); ?></label></th>
                        <td><input type="file" id="wpaicg_assistant_file" name="wpaicg_assistant_file"></td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="submit" class="button button-secondary" value="<?php echo esc_attr__('Create', 'gpt3-ai-content-generator'); ?>">
                            <span id="wpaicg_create_spinner" class="wpaicg_spinner" style="display: none;"></span>
                            <span id="wpaicg_create_message" class="message-spacing" style="display: none;">Please wait...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    <div id="wpaicg_success_message" style="display: none;" class="notice notice-success is-dismissible"></div>
    <div id="wpaicg_error_message" style="display: none;" class="notice notice-error is-dismissible"></div>
    <div style="margin-top: 30px;">
    <?php if (!empty($assistants)): ?>
        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Name', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('ID', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('Instructions', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('Created At', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('Tools', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('File IDs', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php echo esc_html__('Action', 'gpt3-ai-content-generator'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($display_assistants as $assistant): ?>
                    <tr>
                        <td class="editable-field" data-field-name="name"><?php echo esc_html($assistant['name'] ?? __('Unnamed Assistant', 'gpt3-ai-content-generator')); ?></td>
                        <td><?php echo esc_html($assistant['id']); ?></td>
                        <td class="editable-field" data-field-name="model"><?php echo esc_html($assistant['model']); ?></td>
                        <td class="editable-field" data-field-name="instructions">
                            <?php
                            $instructions = esc_html($assistant['instructions'] ?? '');
                            if (strlen($instructions) > 100) {
                                echo substr($instructions, 0, 100) . '... ';
                                echo '<a href="#" class="wpaicg_show_full" data-fulltext="' . esc_attr($instructions) . '">Show All</a>';
                            } else {
                                echo $instructions;
                            }
                            ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i:s', $assistant['created_at']); ?></td>
                        <td class="editable-field" data-field-name="tools">
                            <?php 
                            if (!empty($assistant['tools'])) {
                                $toolTypes = array_map(function($tool) {
                                    $type = $tool['type'] ?? 'unknown_type';
                                    $type = str_replace('_', ' ', $type); // Replace underscores with spaces
                                    $type = ucwords($type); // Capitalize each word
                                    return $type;
                                }, $assistant['tools']);
                                echo esc_html(implode(', ', $toolTypes));
                            } else {
                                echo __('N/A', 'gpt3-ai-content-generator');
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo !empty($assistant['file_ids']) ? esc_html(implode(', ', $assistant['file_ids'])) : __('N/A', 'gpt3-ai-content-generator'); ?>
                        </td>
                        <td>
                            <!-- Edit button -->
                            <button class="wpaicg_edit_assistant button button-secondary" data-assistant-id="<?php echo esc_attr($assistant['id']); ?>">
                                <?php echo esc_attr__('Edit', 'gpt3-ai-content-generator'); ?>
                            </button>
                            <!-- Save button, initially hidden -->
                            <button class="wpaicg_save_assistant button button-primary" data-assistant-id="<?php echo esc_attr($assistant['id']); ?>" style="display: none;">
                                <?php echo esc_attr__('Save', 'gpt3-ai-content-generator'); ?>
                            </button>
                            <span class="wpaicg_save_spinner wpaicg_spinner" style="display: none;"></span>
                            <button class="wpaicg_delete_assistant button button-secondary" data-assistant-id="<?php echo esc_attr($assistant['id']); ?>">
                                <?php echo esc_attr__('Delete', 'gpt3-ai-content-generator'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($assistants !== null): ?>
        <p><?php echo esc_html__('No assistants found.', 'gpt3-ai-content-generator'); ?></p>
    <?php endif; ?>
</div>

</div>
<?php
if ($total_pages > 1) {
    echo paginate_links(array(
        'base'      => add_query_arg('page_num', '%#%'),
        'format'    => '?page_num=%#%',
        'current'   => $current_page,
        'total'     => $total_pages,
        'prev_next' => false,
        'type'      => 'plain'
    ));
}
?>
<script>
    jQuery(document).ready(function ($) {

    // Function to toggle the "Retrieval" checkbox based on the model
    function toggleToolsBasedOnModel(modelSelect) {
        var selectedModel = $(modelSelect).val();
        var isRetrievalCompatible = selectedModel === 'gpt-3.5-turbo-1106' || selectedModel === 'gpt-4-1106-preview';
        $(modelSelect).closest('tr').find('input[name="tools[]"][value="retrieval"]').prop('disabled', !isRetrievalCompatible);
        if (!isRetrievalCompatible) {
            $(modelSelect).closest('tr').find('input[name="tools[]"][value="retrieval"]').prop('checked', false);
        }
    }

    // Logic to make fields editable
    $('.wpaicg_edit_assistant').on('click', function () {
        var $row = $(this).closest('tr');
        
        // Store original text for each field
        $row.find('.editable-field').each(function () {
            var text = $(this).text();
            $(this).data('original-text', text);
        });

        var assistantId = $(this).data('assistant-id');

        // Fetch the current tools of the assistant
        var currentTools = [];
        <?php foreach ($assistants as $assistant): ?>
            if ('<?php echo $assistant['id']; ?>' === assistantId) {
                <?php if (!empty($assistant['tools'])): ?>
                    <?php foreach ($assistant['tools'] as $tool): ?>
                        currentTools.push('<?php echo $tool['type']; ?>');
                    <?php endforeach; ?>
                <?php endif; ?>
            }
        <?php endforeach; ?>

        // Convert each field to an editable input
        $row.find('.editable-field').each(function () {
            var text = $(this).text();
            var fieldName = $(this).data('field-name');

            if (fieldName === 'tools') {
                // Special handling for checkboxes (tools)
                var tools = text.split(', ').map(function(tool) { return tool.trim(); });
                var toolsHtml = '';

                // List of all possible tools - adjust this list based on your actual tools
                var allTools = ['code_interpreter', 'retrieval']; // Add more if needed

                // Generating checkboxes for each tool, each in a new line
                allTools.forEach(function(tool) {
                    var isChecked = currentTools.includes(tool) ? ' checked' : '';
                    toolsHtml += '<div><label><input type="checkbox" name="tools[]" value="' + tool + '"' + isChecked + '> ' + tool + '</label></div>';
                });

                $(this).html(toolsHtml);
            } else if (fieldName === 'instructions') {
                // Convert instructions field to a textarea
                $(this).html('<textarea name="wpaicg_assistant_instructions" style="width: 100%; height: 150px;" maxlength="32768">' + text.trim() + '</textarea>');
            }
            else if (fieldName === 'model') {
                // Convert model field to a dropdown
                var currentModel = $(this).text().trim();
                var modelHtml = '<select name="wpaicg_assistant_model">';
                var models = {
                    "GPT-4": ["gpt-4", "gpt-4-1106-preview", "gpt-4-0613", "gpt-4-0314"],
                    "GPT-3.5": ["gpt-3.5-turbo", "gpt-3.5-turbo-16k", "gpt-3.5-turbo-1106", "gpt-3.5-turbo-0613", "gpt-3.5-turbo-16k-0613"]
                };

                for (var group in models) {
                    modelHtml += '<optgroup label="' + group + '">';
                    models[group].forEach(function(model) {
                        modelHtml += '<option value="' + model + '"' + (model === currentModel ? ' selected' : '') + '>' + model + '</option>';
                    });
                    modelHtml += '</optgroup>';
                }

                modelHtml += '</select>';
                $(this).html(modelHtml);
                // Add change event listener for the model dropdown
                $(this).find('select[name="wpaicg_assistant_model"]').on('change', function() {
                    toggleToolsBasedOnModel(this);
                });

                // Invoke the function to set the correct initial state for tool checkboxes
                toggleToolsBasedOnModel($(this).find('select[name="wpaicg_assistant_model"]'));
            }

            else {
                // For other fields, simply convert to text input
                $(this).html('<input type="text" value="' + text + '" name="' + fieldName + '">');
            }
        });

        // Add a Cancel button next to Save button
        var $cancelButton = $('<button class="wpaicg_cancel_edit button button-secondary" type="button">Cancel</button>');
        $(this).siblings('.wpaicg_save_assistant').after($cancelButton).show();
        $(this).hide();
        $row.find('.wpaicg_delete_assistant').hide();
        
    });

    // When the Save button is clicked
    $('.wpaicg_save_assistant').on('click', function () {
        var $row = $(this).closest('tr');
        var assistantId = $(this).data('assistant-id');

        var $spinner = $row.find('.wpaicg_save_spinner');
        var $editButton = $row.find('.wpaicg_edit_assistant');
        var $deleteButton = $row.find('.wpaicg_delete_assistant');

        // Show spinner
        $spinner.show();

        // Collect tools data
        var tools = [];
        $row.find('input[name="tools[]"]:checked').each(function () {
            tools.push($(this).val());
        });

        // Collect data from editable fields
        var data = {
            action: 'wpaicg_modify_assistant',
            assistant_id: assistantId,
            _wpnonce: '<?php echo wp_create_nonce('wpaicg_modify_assistant_action'); ?>',
            assistant_name: $row.find('[name="name"]').val(),
            assistant_instructions: $row.find('[name="wpaicg_assistant_instructions"]').val(),
            assistant_model: $row.find('[name="wpaicg_assistant_model"]').val(),
            tools: tools // Initialize tools array
        };

        // AJAX call to save the changes
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    $('#wpaicg_success_message').text(response.data.message).show();
                    location.reload(); // Reload to update the list
                } else {
                    $('#wpaicg_error_message').text(response.data.message).show();
                }
            },
            error: function (jqXHR) {
                var errorMessage = "An error occurred";
                if(jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message && jqXHR.responseJSON.data.message.message) {
                    errorMessage = jqXHR.responseJSON.data.message.message;
                }
                $('#wpaicg_error_message').text(errorMessage).show();
            },
            complete: function () {
                $spinner.hide();
                $editButton.show();
                $deleteButton.show();
            }
        });

        // Remove the Cancel button
        $(this).siblings('.wpaicg_cancel_edit').remove();
        // Restore Edit and Delete buttons
        $(this).hide();
        $(this).siblings('.wpaicg_edit_assistant').show();
        $(this).closest('tr').find('.wpaicg_delete_assistant').show();
    });

    // When the Cancel button is clicked
    $(document).on('click', '.wpaicg_cancel_edit', function () {
        var $row = $(this).closest('tr');
        $row.find('.editable-field').each(function () {
            // Restore original text
            var originalText = $(this).data('original-text');
            $(this).html(originalText);
        });

        // Hide Cancel and Save buttons, show Edit and Delete buttons
        $(this).siblings('.wpaicg_save_assistant').hide();
        $(this).siblings('.wpaicg_edit_assistant').show();
        $row.find('.wpaicg_delete_assistant').show();
        $(this).remove();
    });


    $('#wpaicg_syncButton').on('click', function (e) {
        e.preventDefault(); // Prevent default form submission

        var $button = $(this);
        var $spinner = $('#wpaicg_syncSpinner');

        // Show spinner
        $button.prop('disabled', true);
        $spinner.show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaicg_sync_assistants',
                _wpnonce: '<?php echo wp_create_nonce('wpaicg_sync_assistants_action'); ?>'
            },
            success: function (response) {
                if (response.success) {
                    // Display the success message
                    $('#wpaicg_success_message').text(response.data.message).show();
                    // Optional: Reload page or update the table of assistants
                    location.reload();
                } else {
                    // Handle error
                    $('#wpaicg_error_message').text(response.data.message).show();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ', textStatus, ', ', errorThrown);
            },
            complete: function () {
                // Hide spinner and re-enable button
                $spinner.hide();
                $button.prop('disabled', false);
            }
        });
    });

    $('.wpaicg_delete_assistant').on('click', function () {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this assistant?', 'gpt3-ai-content-generator')); ?>')) {
            return;
        }

        var assistantId = $(this).data('assistant-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpaicg_delete_assistant',
                assistant_id: assistantId,
                _wpnonce: '<?php echo wp_create_nonce('wpaicg_delete_assistant_action'); ?>'
            },
            success: function (response) {
                if (response.success) {
                    $('#wpaicg_success_message').text(response.data.message).show();
                    location.reload(); // Reload to update the list
                } else {
                    // Safely access the nested message property
                    var errorMessage = 'An unknown error occurred';
                    if (response.data && response.data.message && response.data.message.message) {
                        errorMessage = response.data.message.message;
                    }
                    $('#wpaicg_error_message').text(errorMessage).show();
                }
            },
            error: function (jqXHR) {
                var errorMessage = "An error occurred";
                if(jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message && jqXHR.responseJSON.data.message.message) {
                    errorMessage = jqXHR.responseJSON.data.message.message;
                }
                $('#wpaicg_error_message').text(errorMessage).show();
            }
        });
    });

    // Function to check the model and toggle the "Retrieval" checkbox
    function toggleRetrievalCheckbox() {
        var selectedModel = $('#wpaicg_assistant_model').val();
        var isRetrievalCompatible = selectedModel === 'gpt-3.5-turbo-1106' || selectedModel === 'gpt-4-1106-preview';
        $('#wpaicg_assistant_tool_retrieval').prop('disabled', !isRetrievalCompatible);
        if (!isRetrievalCompatible) {
            $('#wpaicg_assistant_tool_retrieval').prop('checked', false);
        }
    }

    // Call the function on page load and on change of the model dropdown
    toggleRetrievalCheckbox();
    $('#wpaicg_assistant_model').on('change', toggleRetrievalCheckbox);
    // Toggle form visibility on button click
    $('#wpaicg_show_create_form').on('click', function () {
        $('#wpaicg_create_assistant_form_section').toggle();
    });

    // Handle form submission
    $('#wpaicg_create_assistant_form').on('submit', function (e) {
        e.preventDefault();

        // Show spinner and message
        $('#wpaicg_create_spinner').show();
        $('#wpaicg_create_message').text('Please wait...').show();

        var formData = new FormData(this);
        formData.append('action', 'wpaicg_create_assistant');
        formData.append('_wpnonce', '<?php echo wp_create_nonce('wpaicg_create_assistant_action'); ?>');

        // Append tools data
        if ($('#wpaicg_assistant_tool_code').is(':checked')) {
            formData.append('assistant_tools[]', 'code_interpreter');
        }
        if ($('#wpaicg_assistant_tool_retrieval').is(':checked')) {
            formData.append('assistant_tools[]', 'retrieval');
        }
        // Log formData contents for debugging
        for (let pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]); 
        }
        console.log('Form data: ', formData);
        // Perform AJAX request to create a new assistant

        $.ajax({
            url: ajaxurl, // Replace with your AJAX URL
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    // Display the success message
                    $('#wpaicg_success_message').text(response.data.message).show();
                    // Optional: Reload page or update the table of assistants
                    location.reload();
                } else {
                    // Handle error
                    $('#wpaicg_error_message').text(response.data.message).show();
                }

            },
            error: function () {
                $('#wpaicg_error_message').text('An error occurred during creation.').show();
            },
            complete: function () {
                // Hide spinner and message
                $('#wpaicg_create_spinner').hide();
                $('#wpaicg_create_message').hide();
            }
        });
    });
});
</script>
<script>
jQuery(document).ready(function ($) {
    $('.wpaicg_show_full').on('click', function (e) {
        e.preventDefault();
        var fullText = $(this).data('fulltext');
        $(this).parent().text(fullText);
    });
});
</script>
<script>
jQuery(document).ready(function ($) {
    $('#wpaicg_assistant_file').on('change', function () {
        var file = this.files[0];
        if (file) {
            var maxFileSize = 512 * 1024 * 1024; // 512MB in bytes

            if (file.size > maxFileSize) {
                alert("File size exceeds 512MB. Please choose a smaller file.");
                $(this).val(''); // Clear the file input
            }
        }
    });
});
</script>
