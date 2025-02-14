<?php
/*
Plugin Name: N8N ChatBot
Plugin URI: https://aiservers.com.br/
Description: ChatGPT-style interface for N8N AI Agent
Version: 1.0
Author: Paschoal Diniz
Text Domain: n8n-chatbot
*/

// Security checks
defined('ABSPATH') or die('No script kiddies please!');

// Define constants
define('OACB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OACB_PLUGIN_URL', plugin_dir_url(__FILE__));

// Register activation hook
register_activation_hook(__FILE__, 'oacb_check_requirements');
function oacb_check_requirements() {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        wp_die('This plugin requires PHP 7.4 or higher.');
    }
    if (!extension_loaded('curl')) {
        wp_die('This plugin requires cURL extension.');
    }
}

// Enqueue assets
add_action('wp_enqueue_scripts', 'oacb_enqueue_assets');
function oacb_enqueue_assets() {
    wp_enqueue_style(
        'oacb-chat-style',
        OACB_PLUGIN_URL . 'public/chatbot-style.css',
        [],
        filemtime(OACB_PLUGIN_DIR . 'public/chatbot-style.css')
    );

    wp_enqueue_script(
        'oacb-chat-script',
        OACB_PLUGIN_URL . 'public/chatbot-script.js',
        ['jquery'],
        filemtime(OACB_PLUGIN_DIR . 'public/chatbot-script.js'),
        true
    );

    wp_localize_script('oacb-chat-script', 'oacbConfig', [
        'apiUrl' => rest_url('oacb/v1/send-message'),
        'nonce' => wp_create_nonce('wp_rest'),
        'assetsUrl' => OACB_PLUGIN_URL . 'public/'
    ]);
}

// Register REST API endpoint
add_action('rest_api_init', 'oacb_register_api_endpoints');
function oacb_register_api_endpoints() {
    register_rest_route('oacb/v1', '/send-message', [
        'methods' => 'POST',
        'callback' => 'oacb_handle_message',
        'permission_callback' => '__return_true',
        'args' => [
            'message' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'nonce' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return wp_verify_nonce($param, 'wp_rest');
                }
            ]
        ]
    ]);
}

// Handle message processing
function oacb_handle_message(WP_REST_Request $request) {
    try {
        $webhook_url = 'https://webhooks.aiservers.com.br/webhook/7ca83640-6c8c-4993-9917-0418bbaf4ab0';
        $message = sanitize_text_field($request['message']);

        $response = wp_remote_post($webhook_url, [
            'timeout' => 30,
            'body' => json_encode([
                'message' => $message,
                'session_id' => oacb_generate_session_id()
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-WP-Referer' => esc_url_raw(home_url())
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception(__('Service unavailable', 'openai-chatbot'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return [
            'success' => true,
            'response' => wp_kses_post($data['response'] ?? __('No response received', 'openai-chatbot'))
        ];

    } catch (Exception $e) {
        return new WP_Error('api_error', $e->getMessage(), ['status' => 500]);
    }
}

// Generate session ID
function oacb_generate_session_id() {
    if (!isset($_COOKIE['oacb_session_id'])) {
        $session_id = bin2hex(random_bytes(16));
        setcookie('oacb_session_id', $session_id, time() + 3600, '/', '', is_ssl(), true);
        return $session_id;
    }
    return $_COOKIE['oacb_session_id'];
}

// Add chat interface to footer
add_action('wp_footer', 'oacb_render_chat_interface');
function oacb_render_chat_interface() {
    include OACB_PLUGIN_DIR . 'public/chat-interface.html';
}