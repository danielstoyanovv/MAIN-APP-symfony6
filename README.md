#Setup Instructions

###System Requirements
- PHP 8
- Symfony 6.2.7
- MySQL 5.7
- Node.js
- NPM
- Composer
- Web server: Apache

###Installation Steps
- run php bin\console doctrine:database:drop --force
- run php bin\console doctrine:database:create
- run php bin\console doctrine:schema:update --force
- run php bin\console doctrine:fixtures:load

###Project's integrations
- Token Generator for rest api calls
    src/Service/TokenGenerator.php

- Custom authenticator integration:
    src/Security/ApiKeyAuthenticator.php

- Notification Service:
    src/Service/NotificationService.php

- Register Service:
    src/Service/RegisterService.php

- Search Service:
    src/Service/SearchService.php

- Integrate Message send to all users command:
    src/Command/MessageSendAllCommand.php

- Integrate Message Send To any specific user command:
    src/Command/MessageSendToCommand.php

- Integrate Message Show Unread List Command:
    src/Command/MessageUnreadListCommand.php

- Rest api integration (based on ApiPlatform):

    curl -X 'POST'
    'YOUR_SITE_URL/api/register'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -d '{
    "email": "string",
    "firstName": "string",
    "lastName": "string",
    "password": "password"
    }'

    code implementation: src/Controller/RegisterController.php

    curl -X 'POST'
    'YOUR_SITE_URL/api/login'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -d '{
    "email": "string",
    "password": "string"
    }'

    code implementation: src/Controller/LoginController.php

    curl -X 'POST'
    'YOUR_SITE_URL/api/search/lastName'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'
    -d '{
    "lastName": "string"
    }'

    code implementation: src/Controller/SearchController.php

    curl -X 'POST'
    'YOUR_SITE_URL/api/search/email'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'
    -d '{
    "email": "string"
    }'

    code implementation: src/Controller/SearchController.php

    curl -X 'POST'
    'YOUR_SITE_URL/api/search/firstName'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'
    -d '{
    "firstName": "string"
    }'

    code implementation: src/Controller/SearchController.php

    curl -X 'POST'
    'YOUR_SITE_URL/api/notification'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'
    -d '{
    "content": "string",
    "type": "private",
    "isRead": false
    }'
    
    code implementation: src/Controller/NotificationController.php

    curl -X 'PATCH'
    'YOUR_SITE_URL/api/notification/YOUR_NOTIFICATION_ID'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'
    -d '{
    "content": "string",
    "type": "private",
    "isRead": true
    }'
    
    code implementation: src/Controller/NotificationController.php

    curl -X 'GET'
    'YOUR_SITE_URL/api/notifications'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'

    code implementation: src/Controller/NotificationController.php

    curl -X 'POST'
    'YOUR_SITE_URL/api/notification'
    -H 'accept: application/ld+json'
    -H 'Content-Type: application/ld+json'
    -H 'X-AUTH-TOKEN: MY_TOKEN'
    -d '{
    "message": "string",
    "to": ANY_USER_ID
    }'
    
    code implementation: src/Controller/NotificationController.php


 