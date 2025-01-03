<?php

// Function to verify OpenAI API Key
if (!function_exists('verify_openai_api_key')) {
    function verify_openai_api_key() {
        $api_key = get_option('openai_api_key');
        if (!$api_key) {
            return false; // No API key found
        }

        // Use the chat completions endpoint for key verification
        $url = 'https://api.openai.com/v1/chat/completions';

        // Prepare dummy data to send in the request for verification
        $data = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array('role' => 'user', 'content' => 'Hello, world!')
            ),
            'max_tokens' => 5,
        );

        // Send the request to OpenAI API
        $response = send_request_to_openai($url, $api_key, $data);

        if (is_wp_error($response)) {
            error_log('API request error: ' . $response->get_error_message());
            return false;
        }

        // Decode and check the response
        $body = json_decode($response, true);

        // Log the response for debugging
        error_log('API validation response: ' . print_r($body, true));

        if (isset($body['choices'][0]['message']['content'])) {
            return true; // API key is valid
        }

        return false; // API key is invalid
    }
}

// Function to send the request to OpenAI API
if (!function_exists('send_request_to_openai')) {
    function send_request_to_openai($url, $api_key, $data) {
        // Convert data to JSON
        $json_data = json_encode($data);

        // Set up cURL options
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $api_key",
                "Content-Type: application/json"
            ),
        );

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        // Execute cURL request and get the response
        $response = curl_exec($ch);

        // Check for errors in the cURL request
        if (curl_errno($ch)) {
            error_log('cURL error: ' . curl_error($ch));
            curl_close($ch);
            return new WP_Error('curl_error', curl_error($ch));
        }

        // Close the cURL session
        curl_close($ch);

        // Return the response
        return $response;
    }
}

/**
 * Function to generate content using OpenAI API
 * Placeholder function: Adjust and define function logic as needed
 */
if (!function_exists('openai_generate_content')) {
    function openai_generate_content($prompt, $model = 'gpt-3.5-turbo', $max_tokens = 150) {
        $api_key = get_option('openai_api_key');
        if (!$api_key) {
            error_log('No API key found for content generation.');
            return false;
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $data = array(
            'model' => $model,
            'messages' => array(
                array('role' => 'user', 'content' => $prompt)
            ),
            'max_tokens' => $max_tokens,
        );

        $response = send_request_to_openai($url, $api_key, $data);

        if (is_wp_error($response)) {
            error_log('Content generation error: ' . $response->get_error_message());
            return false;
        }

        // Decode and return the generated content
        $body = json_decode($response, true);
        return $body['choices'][0]['message']['content'] ?? false;
    }
}

/**
 * Function to generate an image associated with a topic using Unsplash API
 */
if (!function_exists('generate_unsplash_image')) {
    function generate_unsplash_image($topic) {
        $access_key = 'BrzKr19O0NRWNXJpVNfFtL2II2dqmfWXHL5xaTnQ3j0'; // Replace with your Unsplash API Access Key
        
        if (!$access_key) {
            error_log('No Unsplash access key found for image generation.');
            return false;
        }

        // Unsplash image search endpoint
        $url = 'https://api.unsplash.com/photos/random?query=' . urlencode($topic) . '&client_id=' . $access_key;

        // Send the request to Unsplash API
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log('Image generation error: ' . $response->get_error_message());
            return false;
        }

        // Decode the response to extract the image URL
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Log the response for debugging
        error_log('Image generation response: ' . print_r($body, true));

        // Check if image URL exists in the response and return it
        if (isset($body[0]['urls']['regular'])) {
            return $body[0]['urls']['regular']; // Return the regular size image URL
        }

        return false; // Return false if image generation failed
    }
}
?>
