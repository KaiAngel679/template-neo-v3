<?php if (isset($_SESSION['user_admin'])): ?>
    <?php

    if (!class_exists('app\modules\module_page_notifications\ext\NotificationsCore')) {
        require_once MODULES . 'module_page_notifications/ext/NotificationsCore.php';
    }
    ?>

    <div class="notifications-header">
        <h3><?= $Translate->get_translate_module_phrase('module_page_notifications', '_SendNotification') ?></h3>
        <p>Отправка системных уведомлений пользователям через веб-интерфейс</p>
        <svg><use href="/resources/img/sprite.svg#bell"></use></svg>
    </div>

    <div class="main-content-wrapper">

        <div class="form-section">

            <form id="sendNotificationForm" class="form-horizontal" onsubmit="sendNotification(event)">

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Тип отправки
                    </label>
                    <div class="col-sm-9 type_notifications">
                        <div class="radio">
                            <label>
                                <input type="radio" name="send_type" value="single" checked onclick="toggleRecipientFields()">
                                Отправить конкретному пользователю
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="send_type" value="online" onclick="toggleRecipientFields()">
                                Отправить всем онлайн на сайте
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="send_type" value="recent" onclick="toggleRecipientFields()">
                                Отправить активным за месяц
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="send_type" value="all" onclick="toggleRecipientFields()">
                                Отправить всем зарегистрированным
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="steamidField">
                    <label class="col-sm-3 control-label">
                        SteamID получателя
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="steamid" id="steamid" 
                               placeholder="STEAM_0:1:12345678 или 7656119xxxxxxxxxx">
                        <small class="text-muted">
                            Оставьте пустым для отправки себе
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Заголовок уведомления
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="title" id="title" required 
                               placeholder="Введите текст на русском (добавится в переводы автоматически)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Текст уведомления
                    </label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="text" id="text" rows="4" required 
                                  placeholder="Введите текст на русском (добавится в переводы автоматически)"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Иконка
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control" name="icon" id="icon">
                            <option value="info">📝 Информация</option>
                            <option value="store">🛒 Магазин</option>
                            <option value="pay">💳 Оплата</option>
                            <option value="request">📨 Запрос</option>
                            <option value="ms">💬 Сообщения</option>
                            <option value="punish">⚠️ Наказание</option>
                            <option value="profile">👤 Профиль</option>
                            <option value="reports">🚨 Жалобы</option>
                            <option value="challenges">🏆 Испытания</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Ссылка (необязательно)
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="url" id="url" 
                               placeholder="/store или https://site.com/page">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Текст кнопки (необязательно)
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="button" id="button" 
                               placeholder="Например: Перейти в магазин">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">
                        Переменные для подстановки
                    </label>
                    <div class="col-sm-9">

                        <textarea class="form-control" name="variables" id="variables" rows="3" 
                                  placeholder="username=Игрок
balance=1000
server=My Server"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            Отправить уведомление
                        </button>
                        <div id="notificationResult" class="mt-2"></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="info-section">
            <?php
            try {
                $NotificationsCore = new app\modules\module_page_notifications\ext\NotificationsCore($Db, $Translate);
                $stats = $NotificationsCore->getStats();

                $recentStats = $NotificationsCore->getRecentStats();

                $translationsOk = $NotificationsCore->checkTranslationsFile();
                if (!$translationsOk) {
                    echo '<div class="alert alert-warning">Файл переводов не найден, создан новый</div>';
                }
            } catch (Exception $e) {
                $stats = ['online' => 0, 'total' => 0];
                $recentStats = ['recent' => 0];
                echo '<div class="alert alert-danger">Ошибка инициализации: ' . $e->getMessage() . '</div>';
            }
            ?>

            <div class="alert alert-info">
                <strong>📊 Статистика пользователей</strong><br>
                <div class="info-2">
                <strong>Онлайн на сайте:</strong> <?= $stats['online'] ?> пользователей<br>
                <strong>Всего зарегистрировано:</strong> <?= $stats['total'] ?> пользователей<br>
                <strong>Активных за последний месяц:</strong> <?= $recentStats['recent'] ?> пользователей
                </div>
            </div>

        </div>
    </div>

    <script>
    // Показать/скрыть поле SteamID в зависимости от выбора
    function toggleRecipientFields() {
        var steamidField = document.getElementById('steamidField');
        var recentInfo = document.getElementById('recentInfo');
        var sendType = document.querySelector('input[name="send_type"]:checked').value;
        var submitBtn = document.getElementById('submitBtn');

        if (sendType === 'single') {
            steamidField.style.display = 'block';
            recentInfo.style.display = 'none';
            submitBtn.innerHTML = 'Отправить пользователю';
        } else if (sendType === 'online') {
            steamidField.style.display = 'none';
            recentInfo.style.display = 'none';
            submitBtn.innerHTML = 'Отправить всем онлайн (' + <?= $stats['online'] ?> + ' чел.)';
        } else if (sendType === 'recent') {
            steamidField.style.display = 'none';
            recentInfo.style.display = 'block';
            submitBtn.innerHTML = 'Отправить 1000 активным пользователям';
        } else if (sendType === 'all') {
            steamidField.style.display = 'none';
            recentInfo.style.display = 'none';
            submitBtn.innerHTML = 'Отправить всем зарегистрированным (' + <?= $stats['total'] ?> + ' чел.)';
        }
    }

    // Инициализация при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        toggleRecipientFields();

        // Примеры текстов для быстрого тестирования
        document.getElementById('title').addEventListener('focus', function() {
            if (!this.value) {
                this.value = 'Новое уведомление от администрации';
            }
        });

        document.getElementById('text').addEventListener('focus', function() {
            if (!this.value) {
                this.value = 'Привет, %username%!\n\nНа сервере обновление. Зайдите и проверьте новые возможности!';
            }
        });

        document.getElementById('variables').addEventListener('focus', function() {
            if (!this.value) {
                this.value = 'username=Игрок\nserver=My Server\ndate=' + new Date().toLocaleDateString();
            }
        });
    });

    function sendNotification(event) {
        event.preventDefault();

        var form = document.getElementById('sendNotificationForm');
        var resultDiv = document.getElementById('notificationResult');
        var submitBtn = document.getElementById('submitBtn');

        // Сбор данных формы
        var formData = new FormData(form);
        var data = {};

        formData.forEach(function(value, key) {
            data[key] = value;
        });

        // Получаем тип отправки
        var sendType = document.querySelector('input[name="send_type"]:checked').value;
        data.send_type = sendType;

        // Если SteamID пустой для single - отправляем себе
        if (sendType === 'single' && !data.steamid) {
            data.steamid = '<?= $_SESSION["steamid"] ?? "" ?>';
        }

        // Парсинг переменных
        if (data.variables) {
            var vars = {};
            var lines = data.variables.split('\n');
            lines.forEach(function(line) {
                var parts = line.split('=');
                if (parts.length === 2) {
                    vars[parts[0].trim()] = parts[1].trim();
                }
            });
            data.variables = JSON.stringify(vars);
        }

        // Показать загрузку
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Отправка уведомления и добавление в переводы...</div>';
        submitBtn.disabled = true;

        // Отправка AJAX запроса
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= $General->arr_general['site'] ?>notifications/send/');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            submitBtn.disabled = false;

            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        resultDiv.innerHTML = '<div class="alert alert-success">' + 
                            '<strong>✓ Успешно!</strong><br>' + 
                            response.message + 
                            '<br><small>Текст добавлен в файл переводов автоматически</small>' + 
                            '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
                    }
                } catch(e) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Ошибка обработки ответа: ' + e.message + '</div>';
                }
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger">Ошибка сервера: ' + xhr.status + '</div>';
            }
        };

        xhr.onerror = function() {
            submitBtn.disabled = false;
            resultDiv.innerHTML = '<div class="alert alert-danger">Ошибка соединения с сервером</div>';
        };

        // Преобразование данных в строку для отправки
        var params = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
        }

        xhr.send(params.join('&'));
    }
    </script>
<?php endif; ?>