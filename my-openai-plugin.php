<?php
/**
 * Plugin Name: My OpenAI Plugin
 * Description: A plugin to integrate OpenAI API and generate content/articles.
 * Version: 1.0
 * Author: Dhia Abedraba
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
include_once plugin_dir_path(__FILE__) . 'includes/api-functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/article-generator.php';

// Register the settings page
function openai_add_settings_page() {
    add_options_page(
        'OpenAI Settings', 
        'OpenAI Settings', 
        'manage_options', 
        'my-openai-plugin', 
        'openai_settings_page'
    );
}
add_action('admin_menu', 'openai_add_settings_page');

// Register plugin settings
function openai_register_settings() {
    register_setting('openai-settings-group', 'openai_api_key');
}
add_action('admin_init', 'openai_register_settings');

// Enqueue plugin styles and scripts
function openai_plugin_enqueue_styles() {
    wp_enqueue_style('openai-plugin-style', plugins_url('css/style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'openai_plugin_enqueue_styles');
