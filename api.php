<?php
// api.php
header('Content-Type: application/json');

// --- Helper function to get environment variables ---
function get_env($key, $default = null) {
    $path = __DIR__ . '/.env';
    if (!file_exists($path)) {
        return $default; // Если .env не найден, возвращаем дефолтное значение
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Пропускаем строки с комментариями
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === $key) {
            return $value; // Нашли ключ в .env
        }
    }
    return $default; // Ключ не найден в .env, возвращаем дефолт
}

// --- Main Logic ---
// 1. Получаем API-ключ (с дефолтным значением, если .env отсутствует или ключ не указан)
$apiKey = get_env('GEMINI_API_KEY', 'sk-Hml0aR9tSiqYqQFtjDaqX6RsUm2Vz8');
if (empty($apiKey)) { // Проверяем, что ключ не пустой
    echo json_encode(['reply' => 'Ошибка: API-ключ не найден. Проверьте дефолтное значение в коде или добавьте ключ в .env.']);
    exit;
}

// 2. Получаем и валидируем JSON-запрос пользователя
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['reply' => 'Ошибка: Некорректный формат JSON в запросе.']);
    exit;
}

$userMessage = trim($input['message'] ?? '');
if (empty($userMessage)) {
    echo json_encode(['reply' => 'Пожалуйста, введите сообщение.']);
    exit;
}

// 3. Загружаем базу знаний
$knowledgeBasePath = __DIR__ . '/db/knowledge_base.txt';
if (!file_exists($knowledgeBasePath)) {
    echo json_encode(['reply' => 'Ошибка: База знаний не найдена. Создайте файл db/knowledge_base.txt.']);
    exit;
}
$knowledgeBase = file_get_contents($knowledgeBasePath);

// 4. Подготавливаем подсказку (prompt) для AI
$prompt = "Ты — ИИ-ассистент, специалист по внутренней системе управления складом под названием HUB. Твоя задача — отвечать на вопросы пользователя, основываясь ИСКЛЮЧИТЕЛЬНО на предоставленной базе знаний. Не придумывай ничего от себя. Если ответа в базе знаний нет, вежливо сообщи, что ты можешь отвечать только на вопросы, связанные с системой HUB.\n\nВот база знаний:\n---\n" . $knowledgeBase . "\n---\n\nВопрос пользователя: \"" . $userMessage . "\"";

// 5. Вызываем Gemini API с корректной структурой запроса
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $apiKey;
$data = [
    'model' => 'gemini-pro', // Указываем модель
    'prompt' => ['text' => $prompt], // Ключевой часть prompt
    'temperature' => 0.7 // Регулирует случайность ответа (0.0 — детерминированный, 1.0 — случайный)
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Для извлечения ошибок API
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$http_response_header = $http_response_header ?? [];

// Извлекаем HTTP-статус из заголовков ответа
$status_line = $http_response_header[0] ?? 'HTTP/1.1 500 Internal Server Error';
preg_match('/HTTP\/\S+\s(\d+)/', $status_line, $match);
$status = $match[1] ?? 500;

// Обрабатываем ошибки подключения (например, отсутствие интернета)
if ($response === FALSE) {
    $error_msg = 'Не удалось связаться с API. ';
    $last_error = error_get_last();
    if ($last_error) {
        $error_msg .= $last_error['message'];
    }
    error_log("Gemini API Error: Status $status, Error: $error_msg");
    echo json_encode(['reply' => "Ошибка подключения к ИИ-сервису. Статус: $status. Подробности: $error_msg. Проверьте настройки сервера."]);
    exit;
}

// Распарсываем ответ API и проверяем на JSON-ошибки
$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Gemini API Error: Неверный JSON в ответе. Ответ: $response");
    echo json_encode(['reply' => 'Ошибка: Не удалось разобрать ответ ИИ-сервиса как JSON.']);
    exit;
}

// Обрабатываем ошибки API (например, некорректный ключ)
if (isset($result['error'])) {
    $error_code = $result['error']['code'] ?? 'Неопределен';
    $error_message = $result['error']['message'] ?? 'Неизвестная ошибка';
    error_log("Gemini API Error: Код $error_code, Сообщение: $error_message");
    echo json_encode(['reply' => "Ошибка ИИ-сервиса. Код: $error_code. Сообщение: $error_message."]);
    exit;
}

// Извлекаем и выводим ответ AI
if (isset($result['candidates'][0]['content']['text'])) {
    $reply = trim($result['candidates'][0]['content']['text']);
    echo json_encode(['reply' => $reply]);
} else {
    error_log("Gemini API: Непредвиденная структура ответа. Ответ: " . print_r($result, true));
    echo json_encode(['reply' => 'ИИ-сервис вернул неожиданный ответ. Проверьте API-ключ и базу знаний.']);
}
?>