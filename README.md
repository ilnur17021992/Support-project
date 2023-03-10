<div align="center"><b style="color:#D9534F; font-size:50px;">Support Project</b></div>

# Описание

Данный проект представляет собой систему обработки обращений пользователей, которая включает в себя Web-интерфейс, Телеграм бота и REST API для максимальной гибкости взаимодействия. В рамках проекта были реализованы функциональные возможности для обеспечения взаимодействия со сторонними сервисами, в том числе с использованием API Telegram. Для интеграции с другими сервисами было реализовано свое API. Важной составляющей проекта является логирование входящих API запросов и ответов, которое позволяет отслеживать работу приложения и обнаруживать ошибки.

В проекте также был применен шаблон DTO (Data Transfer Object). Сервисы взаимодействуют между собой с помощью объектов DTO-классов, что упрощает передачу данных и облегчает понимание структуры приложения.

Общая архитектура проекта и использование современных подходов позволили достичь высокой производительности и расширяемости приложения.

# Роли и права доступа

В системе подразумевается три уровня доступа: Администратор, Тех. поддержка и Пользователь. Каждая роль имеет различные уровни доступа. Администратор может полностью управлять системой, в том числе создавать, редактировать и удалять пользователей и роли, а также обрабатывать тикеты. Тех. поддержка имеет ограниченные права и может только обрабатывать тикеты. Пользователи могут только создавать тикеты и отправлять сообщения.

![Роли](https://opengpt.online/public/storage/files/other/roles.png)

# Способы взаимодействия

На данный момент реализовано три способа взаимодействия с тикет-системой: web-сайт, телеграм бот и REST API. Но ничего не мешает подключить как модули дополнительные возможности коммуникации.

**Web-сайт** обладает удобным интерфейсом и позволяет пользователям создавать тикеты и просматривать их статусы. Для администраторов и тех. поддержки доступны дополнительные функции, включая CRUD операции с пользователями и тикетами.

![Web-интерфейс](https://opengpt.online/public/storage/files/other/web.png)

**Телеграм бот** обеспечивает быстрое и удобное общение с тех. поддержкой. Клиенты могут создавать тикеты и получать ответы через бота, а тех. поддержка может прямо из чата ответить на тикет.

![Телеграм бот](https://opengpt.online/public/storage/files/other/telegram.png)

**REST API** обеспечивает гибкость и возможность интеграции с другими приложениями. В документации OpenAPI и коллекции Postman представлены все необходимые команды и примеры запросов.

![REST API](https://opengpt.online/public/storage/files/other/api.png)

# Особенности

-   **Простота.** Для создания обращения в техническую поддержку достаточно просто написать сообщение боту (регистрация не требуется).
-   **Гибкость.** Реализовано несколько способов для взаимодействия с системой: web-сайт, телеграм бот, REST API.
-   **Масштабируемость.** Есть возможность подключить дополнительные способы связи такие как Slack, WhatsApp и другие.

# Принцип работы

-   Созданное обращение клиента автоматически пересылается в телеграм чат тех. поддержки, а также в уведомление на сайте (колокольчик).
-   Для ответа на тикет достаточно процитировать сообщение бота. Этот ответ будет отправлен клиенту в телеграм чат и в уведомление на сайте.
-   Клиент может закрыть обращение нажав на зеленую кнопку "Проблема решена". В противном случае тикет будет автоматически закрыт, если после ответа тех. поддержки прошло более 10 минут.

# Установка

1. `cp .env.example .env` - создание env-файла.
2. `php artisan migrate` - запуск миграций (в консоли).
3. `php artisan role:create` - создание ролей: admin, support, user (в консоли).
4. `/start` - запуск телеграм бота, создание первого пользователя (в телеграм боте).
5. `php artisan orchid:admin --id=1` - создание Администратора, выдача максимальных прав (в консоли).

# Ссылки

-   https://opengpt.online - web-интерфейс проекта "Support".
-   https://t.me/myidbot - бот для получения ID телеграм для регистрации через сайт.
-   https://t.me/this_support_bot - бот для взаимодействия с тех. поддержкой через телеграм.
-   https://t.me/+C-LIdN9nHzxkNjky - чат тех. поддержки, в который стекаются обращения клиентов.
-   https://miro.com/app/board/uXjVPnvVxiE=/?share_link_id=40133349729 - визуальная схема проекта.
-   https://opengpt.online/storage/files/other/api-documentation.zip - документация OpenAPI и Postman.