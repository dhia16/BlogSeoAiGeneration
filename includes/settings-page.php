<?php
// Function to add the settings page
function openai_settings_page() {
    ?>
    <div class="wrap">
        <h1>OpenAI Settings</h1>
        
        <form method="post" action="options.php">
            <?php settings_fields('openai-settings-group'); ?>
            <?php do_settings_sections('openai-settings-group'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">OpenAI API Key</th>
                    <td><input type="text" name="openai_api_key" value="<?php echo esc_attr(get_option('openai_api_key')); ?>" /></td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <form method="post">
            <button type="submit" name="test_api_key" class="button button-primary">Test API Key</button>
        </form>
        
        <?php
        // Test the API key if the button is pressed
        if (isset($_POST['test_api_key'])) {
            $is_valid = verify_openai_api_key();
            if ($is_valid) {
                echo '<div class="updated"><p>API Key is valid!</p></div>';
            } else {
                echo '<div class="error"><p>❌ API Key is invalid!</p></div>';
            }
        }
        ?>
    </div>
    <?php
}