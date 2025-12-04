<?php
/**
 * ChatGPT Web Interface Reverse Proxy
 * 
 * This proxy forwards requests to ChatGPT's web interface (chat.openai.com)
 * without using the OpenAI API. Authentication is handled through session cookies.
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load the proxy class
require_once __DIR__ . '/ChatGPTProxy.php';

// Initialize proxy
$proxy = new ChatGPTProxy();

// Handle the request
$proxy->handleRequest();
