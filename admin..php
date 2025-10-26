<?php
// Включи когда будет сервер
header('Content-Type: application/json; charset=utf-8');

// Обработка AJAX запросов
if ($_POST['action'] === 'add_news') {
    $newsData = json_decode(file_get_contents('news.json'), true);
    
    $newNews = [
        'id' => time(),
        'date' => $_POST['date'],
        'title' => $_POST['title'],
        'content' => $_POST['content'],
        'image' => $_POST['image'],
        'tags' => explode(',', $_POST['tags'])
    ];
    
    array_unshift($newsData['news'], $newNews);
    
    file_put_contents('news.json', json_encode($newsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo json_encode(['success' => true]);
    exit;
}

if ($_POST['action'] === 'delete_news') {
    $newsData = json_decode(file_get_contents('news.json'), true);
    $newsData['news'] = array_filter($newsData['news'], function($news) {
        return $news['id'] != $_POST['id'];
    });
    
    file_put_contents('news.json', json_encode($newsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель новостей</title>
    <style>
        :root {
            --primary-bg: linear-gradient(to top right, #000000, #001d50);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-white: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.9);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--primary-bg);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: var(--text-white);
            padding: 20px;
        }

        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 30px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Georgia', serif;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .btn {
            background: linear-gradient(135deg, #4a90e2, #2a5298);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e24a4a, #982a2a);
        }

        .btn-success {
            background: linear-gradient(135deg, #4ae24a, #2a982a);
        }

        .news-list {
            margin-top: 40px;
        }

        .news-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .news-item h4 {
            margin-bottom: 5px;
            color: var(--text-white);
        }

        .news-date {
            color: #4a90e2;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .news-actions {
            margin-top: 15px;
        }

        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            display: none;
        }

        .status.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
            display: block;
        }

        .status.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
            display: block;
        }

        .upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 15px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: #4a90e2;
            background: rgba(255, 255, 255, 0.05);
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            margin: 10px 0;
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>📝 Админ-панель новостей</h1>

        <!-- Статус операции -->
        <div id="status" class="status"></div>

        <!-- Форма добавления новости -->
        <div class="form-section">
            <h3>➕ Добавить новую новость</h3>
            
            <div class="form-group">
                <label for="newsTitle">Заголовок:</label>
                <input type="text" id="newsTitle" placeholder="Введите заголовок новости" required>
            </div>

            <div class="form-group">
                <label for="newsDate">Дата:</label>
                <input type="date" id="newsDate" required>
            </div>

            <div class="form-group">
                <label for="newsContent">Текст новости:</label>
                <textarea id="newsContent" placeholder="Введите текст новости..." required></textarea>
            </div>

            <div class="form-group">
                <label for="imageUpload">Изображение:</label>
                <div class="upload-area" onclick="document.getElementById('imageFile').click()">
                    <p>📁 Нажмите для загрузки изображения</p>
                    <input type="file" id="imageFile" style="display: none;" accept="image/*" onchange="previewImage(this)">
                    <img id="imagePreview" class="image-preview" alt="Предпросмотр">
                </div>
                <input type="text" id="newsImage" placeholder="Или введите URL изображения" style="margin-top: 10px;">
            </div>

            <div class="form-group">
                <label for="newsTags">Теги (через запятую):</label>
                <input type="text" id="newsTags" placeholder="Новости, События, Компания">
            </div>

            <button class="btn btn-success" onclick="addNews()">🚀 Опубликовать новость</button>
        </div>

        <!-- Список существующих новостей -->
        <div class="news-list">
            <h3>📰 Существующие новости</h3>
            <div id="newsList">
                <div class="loading">Загрузка новостей...</div>
            </div>
        </div>
    </div>

    <script>
        // Загрузка при старте
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('newsDate').value = new Date().toISOString().split('T')[0];
            loadNews();
        });

        // Предпросмотр изображения
        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    // Здесь можно добавить загрузку на сервер
                }
                reader.readAsDataURL(file);
            }
        }

        // Загрузка новостей
        async function loadNews() {
            try {
                const response = await fetch('news.json');
                const data = await response.json();
                displayNewsList(data.news);
            } catch (error) {
                showStatus('Ошибка загрузки новостей', 'error');
            }
        }

        // Отображение списка новостей
        function displayNewsList(newsArray) {
            const container = document.getElementById('newsList');
            
            if (!newsArray || newsArray.length === 0) {
                container.innerHTML = '<p>Новостей пока нет</p>';
                return;
            }

            const sortedNews = newsArray.sort((a, b) => new Date(b.date) - new Date(a.date));
            
            container.innerHTML = sortedNews.map(news => `
                <div class="news-item">
                    <h4>${escapeHtml(news.title)}</h4>
                    <div class="news-date">📅 ${formatDate(news.date)}</div>
                    <div class="news-content-preview">${escapeHtml(news.content.substring(0, 100))}...</div>
                    ${news.image ? `<div><small>🖼️ Изображение: ${news.image}</small></div>` : ''}
                    ${news.tags && news.tags.length > 0 ? 
                        `<div><small>🏷️ Теги: ${news.tags.join(', ')}</small></div>` : ''}
                    <div class="news-actions">
                        <button class="btn" onclick="editNews(${news.id})">✏️ Редактировать</button>
                        <button class="btn btn-danger" onclick="deleteNews(${news.id})">🗑️ Удалить</button>
                    </div>
                </div>
            `).join('');
        }

        // Добавление новости
        async function addNews() {
            const title = document.getElementById('newsTitle').value.trim();
            const date = document.getElementById('newsDate').value;
            const content = document.getElementById('newsContent').value.trim();
            const image = document.getElementById('newsImage').value.trim();
            const tags = document.getElementById('newsTags').value.split(',').map(tag => tag.trim()).filter(tag => tag);

            if (!title || !date || !content) {
                showStatus('Заполните обязательные поля', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_news');
            formData.append('title', title);
            formData.append('date', date);
            formData.append('content', content);
            formData.append('image', image);
            formData.append('tags', tags.join(','));

            try {
                const response = await fetch('admin.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showStatus('✅ Новость успешно добавлена!', 'success');
                    resetForm();
                    loadNews(); // Перезагружаем список
                } else {
                    showStatus('❌ Ошибка при добавлении новости', 'error');
                }
            } catch (error) {
                showStatus('❌ Ошибка сети: ' + error.message, 'error');
            }
        }

        // Удаление новости
        async function deleteNews(id) {
            if (!confirm('Удалить эту новость?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_news');
            formData.append('id', id);

            try {
                const response = await fetch('admin.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showStatus('✅ Новость удалена!', 'success');
                    loadNews(); // Перезагружаем список
                } else {
                    showStatus('❌ Ошибка при удалении', 'error');
                }
            } catch (error) {
                showStatus('❌ Ошибка сети', 'error');
            }
        }

        // Редактирование новости
        function editNews(id) {
            showStatus('✏️ Редактирование: загрузите новость заново с изменениями', 'success');
        }

        // Сброс формы
        function resetForm() {
            document.getElementById('newsTitle').value = '';
            document.getElementById('newsContent').value = '';
            document.getElementById('newsImage').value = '';
            document.getElementById('newsTags').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('imageFile').value = '';
        }

        // Показ статуса
        function showStatus(message, type) {
            const statusDiv = document.getElementById('status');
            statusDiv.textContent = message;
            statusDiv.className = 'status ' + type;
            
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 5000);
        }

        // Форматирование даты
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('ru-RU');
        }

        // Экранирование HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>