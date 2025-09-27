<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Logistic Koresh</title>
    <meta name="description" content="AI Assistant for HUB System">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph -->
    <meta property="og:title" content="Your Logistic Koresh">
    <meta property="og:description" content="AI Assistant for HUB System">
    <meta property="og:type" content="website">
    <meta property="og:url" content=""> <!-- Will be the current URL -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="chat-container">
        <header class="chat-header">
            <h1>Your Logistic Koresh</h1>
            <p class="text-muted">AI Assistant for HUB System</p>
        </header>

        <main class="chat-messages" id="chat-messages">
            <!-- Messages will be appended here by JS -->
            <div class="message bot-message">
                <div class="message-content">
                    <p>Привет. Я — твой логистический кореш. Я здесь, чтобы помочь тебе с вопросами по системе HUB. Спрашивай, не стесняйся.</p>
                </div>
            </div>
        </main>

        <footer class="chat-input-area">
            <form id="chat-form" class="d-flex">
                <input type="text" id="message-input" class="form-control" placeholder="Введите ваш вопрос..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary" id="send-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </form>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>