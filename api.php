<?php
// api.php

header('Content-Type: application/json');

// --- Helper function to get environment variables ---
function get_env($key, $default = null) {
    $path = __DIR__ . '/.env';
    if (!file_exists($path)) {
        return $default;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === $key) {
            return $value;
        }
    }
    return $default;
}

// --- Main Logic ---

// 1. Get API Key
$apiKey = get_env('GEMINI_API_KEY');
if (!$apiKey) {
    echo json_encode(['reply' => 'Ошибка: API-ключ не найден. Пожалуйста, добавьте его в файл .env']);
    exit;
}

// 2. Get user message from POST request
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Пожалуйста, введите сообщение.']);
    exit;
}

// 3. Load the knowledge base
$knowledgeBasePath = __DIR__ . '/db/knowledge_base.txt';
if (!file_exists($knowledgeBasePath)) {
    echo json_encode(['reply' => 'Ошибка: База знаний не найдена.']);
    exit;
}
$knowledgeBase = file_get_contents($knowledgeBasePath);

// 4. Prepare the prompt for the AI
$prompt = "Ты — ИИ-ассистент, специалист по внутренней системе управления складом под названием HUB. Твоя задача — отвечать на вопросы пользователя, основываясь ИСКЛЮЧИТЕЛЬНО на предоставленной базе знаний. Не придумывай ничего от себя. Если ответа в базе знаний нет, вежливо сообщи, что ты можешь отвечать только на вопросы, связанные с системой HUB.\n\nВот база знаний:\n---\n" . $knowledgeBase . "\n---\n\nВопрос пользователя: \"" . $userMessage . "\"";

// 5. Call the Gemini API
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $apiKey;

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // To see the error message from the API
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$http_response_header = $http_response_header ?? [];

// 6. Process the response
if ($response === FALSE) {
    $error = 'Не удалось связаться с API. ';
    // Check for more specific errors if possible
    $last_error = error_get_last();
    if ($last_error) {
        $error .= $last_error['message'];
    }
     // Try to get the HTTP status and response body
    $status_line = $http_response_header[0] ?? 'HTTP/1.1 500 Internal Server Error';
    preg_match('{HTTP/\S+\s(\d+)}', $status_line, $match);
    $status = $match[1] ?? 500;
    
    error_log("Gemini API Error: Status $status, Response: $response");
    
    echo json_encode(['reply' => "Ошибка при обращении к сервису ИИ. Статус: $status. Пожалуйста, проверьте ключ API и настройки сервера."]);

} else {
    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $result['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['reply' => $reply]);
    } else {
        // Log the actual error response from the API for debugging
        error_log("Gemini API - Unexpected response structure: " . $response);
        echo json_encode(['reply' => 'Получен неожиданный ответ от сервиса ИИ. Возможно, проблема с конфигурацией или ключом API.']);
    }
}