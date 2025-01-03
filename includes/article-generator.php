<?php
// Function to generate articles for each topic
if (!function_exists('openai_generate_articles')) {
    function openai_generate_articles($topics) {
        foreach ($topics as $topic) {
            // Generate article content
            $content = openai_generate_content("Write a detailed article about: $topic");
            if (!$content) {
                error_log("Failed to generate content for topic: $topic");
                continue; // Skip to the next topic
            }

            // Generate meta description
            $meta_description = openai_generate_content("Write a meta description for: $topic", 'gpt-3.5-turbo', 100);

            // Generate image URL (ensure this function is defined properly)
            $image_url = generate_unsplash_image($topic); // Ensure the correct function is used
            error_log('Generated Image URL: ' . $image_url); // Log the image URL

            // Insert the post into WordPress
            $post_id = wp_insert_post([
                'post_title'   => $topic,
                'post_content' => $content,
                'post_status'  => 'draft',
                'post_type'    => 'post',
            ]);

            if (!is_wp_error($post_id)) {
                // Update Yoast meta fields
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
                update_post_meta($post_id, '_yoast_wpseo_title', $topic);

                // Handle image if URL is provided
                if ($image_url) {
                    // Download the image and handle the upload process
                    $image_tmp = download_url($image_url);

                    // Check for errors in downloading the image
                    if (is_wp_error($image_tmp)) {
                        error_log('Failed to download image: ' . $image_tmp->get_error_message());
                    } else {
                        error_log('Image downloaded successfully to: ' . $image_tmp);

                        // Prepare the file for sideloading
                        $file_array = array(
                            'name' => basename($image_url),
                            'tmp_name' => $image_tmp,
                        );
                        error_log('File Array: ' . print_r($file_array, true)); // Log the file array

                        // Upload the image to WordPress media library
                        $attachment_id = media_handle_sideload($file_array, $post_id);

                        // Check for errors in handling the image
                        if (is_wp_error($attachment_id)) {
                            error_log('Failed to upload image: ' . $attachment_id->get_error_message());
                        } else {
                            error_log('Image uploaded successfully, attachment ID: ' . $attachment_id);

                            // Set the uploaded image as the post's featured image
                            $set_thumbnail = set_post_thumbnail($post_id, $attachment_id);
                            if (!$set_thumbnail) {
                                error_log('Failed to set the featured image for post ID ' . $post_id);
                            } else {
                                error_log('Successfully set the featured image for post ID ' . $post_id);
                            }
                        }
                    }
                }
            }
        }
    }
}

// Add a custom menu page in WordPress Dashboard
if (!function_exists('openai_add_dashboard_page')) {
    function openai_add_dashboard_page() {
        add_menu_page(
            'Generate Articles',           // Page Title
            'Generate Articles',           // Menu Title
            'manage_options',              // Capability
            'openai_generate_articles',    // Menu Slug
            'openai_dashboard_page',       // Callback Function to render page
            'dashicons-edit',              // Icon
            6                              // Position in the menu
        );
    }
    add_action('admin_menu', 'openai_add_dashboard_page');
}

// Render the custom admin page
if (!function_exists('openai_dashboard_page')) {
    function openai_dashboard_page() {
        ?>
        <div class="wrap">
            <h1>Generate Articles from Topics</h1>
    
            <!-- Form to input topics -->
            <form method="post">
                <?php wp_nonce_field('openai_generate_articles_nonce', 'openai_generate_articles_nonce_field'); ?>
                <textarea name="topics" rows="10" cols="50" placeholder="Enter each topic on a new line"><?php echo isset($_POST['topics']) ? esc_textarea($_POST['topics']) : ''; ?></textarea>
                <p><em>Enter each topic on a new line.</em></p>
                <?php submit_button('Generate Articles'); ?>
            </form>
    
            <?php
            // Check if the form is submitted and validate nonce
            if (isset($_POST['topics']) && isset($_POST['openai_generate_articles_nonce_field']) && wp_verify_nonce($_POST['openai_generate_articles_nonce_field'], 'openai_generate_articles_nonce')) {
                $topics = array_map('trim', explode("\n", $_POST['topics'])); // Split input into an array of topics
                $topics = array_filter($topics); // Remove empty lines
    
                if (!empty($topics)) {
                    openai_generate_articles($topics); // Call the function to generate articles
                    echo '<div class="updated"><p>Articles are being generated for the topics!</p></div>';
                } else {
                    echo '<div class="error"><p>No valid topics entered!</p></div>';
                }
            }
            ?>
        </div>
        <?php
    }
}
?>
