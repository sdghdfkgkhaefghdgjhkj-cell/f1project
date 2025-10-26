<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Обязательные поля
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    
    // Необязательные поля (проверяем существуют ли)
    $phone = isset($_POST["phone"]) ? strip_tags(trim($_POST["phone"])) : "";
    $message = isset($_POST["message"]) ? strip_tags(trim($_POST["message"])) : "";
    
    // Проверка обязательных данных
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Пожалуйста, заполните обязательные поля правильно.";
        exit;
    }
    
    $to = "gogol-1901@mail.ru";
    $subject = "Новое сообщение с сайта от $name";
    $body = "Имя: $name\nEmail: $email\n";
    
    // Добавляем телефон если есть
    if (!empty($phone)) {
        $body .= "Телефон: $phone\n";
    }
    
    // Добавляем сообщение если есть
    if (!empty($message)) {
        $body .= "\nСообщение:\n$message";
    }
    
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    if (mail($to, $subject, $body, $headers)) {
        http_response_code(200);
        echo "Спасибо! Ваше сообщение отправлено.";
    } else {
        http_response_code(500);
        echo "Ошибка при отправке. Попробуйте позже.";
    }
} else {
    http_response_code(403);
    echo "Ошибка доступа.";
}
?>