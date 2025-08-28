# User Auth API - Авторизация пользователей и управление сессиями

Этот документ описывает методы `AuthClient` для работы с User Auth API - авторизацией пользователей по email и управлением токенами сессий для checkout процесса.

## Обзор

User Auth API предназначен для сценариев, когда пользователь уже авторизован в системе клиента и нужно создать токен сессии для перехода на страницу оплаты без повторной авторизации.

### Основные возможности

- **Авторизация по email** - создание пользователя и токена сессии
- **Управление сессиями** - валидация, продление, завершение
- **Получение URL для оплаты** - формирование ссылки на checkout с токеном
- **Информация о сессии** - получение данных текущей сессии

## Методы AuthClient

### authenticateByEmail()

Авторизует пользователя по email и создает токен сессии для checkout процесса.

```php
$response = $client->auth->authenticateByEmail([
    'email' => 'user@example.com',
    'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000', // опционально
    'name' => 'Имя пользователя', // опционально
    'password' => 'password123' // опционально
]);

// Ответ:
[
    'success' => true,
    'message' => 'Пользователь успешно авторизован',
    'data' => [
        'session_token' => 'abc123...xyz789', // 64-символьный токен
        'user' => [
            'id' => 123,
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'name' => 'Имя пользователя',
            'email' => 'user@example.com'
        ],
        'project_user' => [
            'id' => 456,
            'uuid' => '550e8400-e29b-41d4-a716-446655440002',
            'status' => 'active'
        ],
        'expires_at' => '2024-01-15T14:30:00.000000Z'
    ]
]
```

**Параметры:**
- `email` (string, обязательно) - Email пользователя
- `project_uuid` (string, обязательно) - UUID проекта
- `plan_uuid` (string, опционально) - UUID плана подписки
- `name` (string, опционально) - Имя пользователя
- `password` (string, опционально) - Пароль пользователя

### validateSession()

Проверяет валидность токена сессии.

```php
$response = $client->auth->validateSession('abc123...xyz789');

// Ответ:
[
    'success' => true,
    'message' => 'Токен сессии валиден',
    'data' => [
        'user' => [
            'id' => 123,
            'email' => 'user@example.com',
            'name' => 'Имя пользователя'
        ],
        'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000',
        'expires_at' => '2024-01-15T14:30:00.000000Z'
    ]
]
```

### getPaymentUrl()

Формирует URL для checkout страницы с токеном сессии.

```php
$response = $client->auth->getPaymentUrl(
    'abc123...xyz789', // session_token
    '987f6543-e21c-34d5-b678-987654321000' // plan_uuid
);

// Ответ:
[
    'success' => true,
    'message' => 'URL для оплаты сформирован',
    'data' => [
        'checkout_url' => 'https://example.com/checkout/987f6543-e21c-34d5-b678-987654321000?session_token=abc123...xyz789',
        'plan' => [
            'uuid' => '987f6543-e21c-34d5-b678-987654321000',
            'name' => 'Базовый план',
            'price' => 1000,
            'currency' => 'RUB',
            'description' => 'Описание плана'
        ],
        'user' => [
            'id' => 123,
            'email' => 'user@example.com',
            'name' => 'Имя пользователя'
        ]
    ]
]
```

### extendSession()

Продлевает время действия токена сессии.

```php
$response = $client->auth->extendSession(
    'abc123...xyz789', // session_token
    4 // hours (опционально, по умолчанию 2)
);

// Ответ:
[
    'success' => true,
    'message' => 'Сессия успешно продлена',
    'data' => [
        'expires_at' => '2024-01-15T18:30:00.000000Z'
    ]
]
```

### destroySession()

Завершает сессию (logout).

```php
$response = $client->auth->destroySession('abc123...xyz789');

// Ответ:
[
    'success' => true,
    'message' => 'Сессия успешно завершена'
]
```

### getSessionInfo()

Получает подробную информацию о текущей сессии.

```php
$response = $client->auth->getSessionInfo('abc123...xyz789');

// Ответ:
[
    'success' => true,
    'data' => [
        'user' => [
            'id' => 123,
            'email' => 'user@example.com',
            'name' => 'Имя пользователя'
        ],
        'project_user_id' => 456,
        'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000',
        'authenticated_at' => '2024-01-15T12:30:00.000000Z',
        'expires_at' => '2024-01-15T14:30:00.000000Z',
        'ip_address' => '192.168.1.1'
    ]
]
```

## Примеры использования

### Полный сценарий авторизации и оплаты

```php
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Config;

// Инициализация клиента
$config = Config::fromArray([
    'api_key' => 'YOUR_API_KEY',
    'base_url' => 'https://your-genesis-api.com/api/'
]);
$client = GenesisClient::fromConfig($config);

try {
    // 1. Авторизация пользователя
    $authResponse = $client->auth->authenticateByEmail([
        'email' => 'user@example.com',
        'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Иван Иванов'
    ]);
    
    if (!$authResponse['success']) {
        throw new Exception('Ошибка авторизации: ' . $authResponse['message']);
    }
    
    $sessionToken = $authResponse['data']['session_token'];
    echo "Пользователь авторизован, токен сессии: " . substr($sessionToken, 0, 8) . "...\n";
    
    // 2. Получение URL для оплаты
    $paymentResponse = $client->auth->getPaymentUrl(
        $sessionToken,
        '987f6543-e21c-34d5-b678-987654321000'
    );
    
    if (!$paymentResponse['success']) {
        throw new Exception('Ошибка получения URL: ' . $paymentResponse['message']);
    }
    
    $checkoutUrl = $paymentResponse['data']['checkout_url'];
    echo "URL для оплаты: {$checkoutUrl}\n";
    
    // 3. Перенаправление пользователя на checkout
    header("Location: {$checkoutUrl}");
    exit;
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
```

### Проверка и продление сессии

```php
// Проверяем валидность токена
$validateResponse = $client->auth->validateSession($sessionToken);

if ($validateResponse['success']) {
    $sessionData = $validateResponse['data'];
    echo "Сессия валидна для пользователя: {$sessionData['user']['email']}\n";
    echo "Истекает: {$sessionData['expires_at']}\n";
    
    // Если сессия скоро истечет, продлеваем её
    $expiresAt = new DateTime($sessionData['expires_at']);
    $now = new DateTime();
    $diff = $expiresAt->getTimestamp() - $now->getTimestamp();
    
    if ($diff < 1800) { // меньше 30 минут
        echo "Сессия скоро истечет, продлеваем...\n";
        
        $extendResponse = $client->auth->extendSession($sessionToken, 2);
        if ($extendResponse['success']) {
            echo "Сессия продлена до: {$extendResponse['data']['expires_at']}\n";
        }
    }
} else {
    echo "Сессия недействительна: {$validateResponse['message']}\n";
    // Требуется повторная авторизация
}
```

### Получение информации о сессии

```php
$sessionInfo = $client->auth->getSessionInfo($sessionToken);

if ($sessionInfo['success']) {
    $data = $sessionInfo['data'];
    
    echo "Информация о сессии:\n";
    echo "- Пользователь: {$data['user']['name']} ({$data['user']['email']})\n";
    echo "- Проект: {$data['project_uuid']}\n";
    echo "- План: {$data['plan_uuid']}\n";
    echo "- Авторизован: {$data['authenticated_at']}\n";
    echo "- Истекает: {$data['expires_at']}\n";
    echo "- IP адрес: {$data['ip_address']}\n";
}
```

### Завершение сессии

```php
// Завершаем сессию после успешной оплаты или по запросу пользователя
$destroyResponse = $client->auth->destroySession($sessionToken);

if ($destroyResponse['success']) {
    echo "Сессия успешно завершена\n";
} else {
    echo "Ошибка завершения сессии\n";
}
```

## Обработка ошибок

Все методы могут возвращать ошибки в следующем формате:

```php
[
    'success' => false,
    'message' => 'Описание ошибки',
    'code' => 'ERROR_CODE', // опционально
    'errors' => [...] // детали валидации, опционально
]
```

### Основные коды ошибок

- `PROJECT_NOT_FOUND` - Проект не найден
- `PLAN_NOT_FOUND` - План подписки не найден  
- `SESSION_NOT_FOUND` - Токен сессии не найден
- `SESSION_EXPIRED` - Токен сессии истек
- `AUTHENTICATION_ERROR` - Общая ошибка авторизации

### Пример обработки ошибок

```php
$response = $client->auth->authenticateByEmail($data);

if (!$response['success']) {
    switch ($response['code'] ?? null) {
        case 'PROJECT_NOT_FOUND':
            echo "Проект не найден. Проверьте project_uuid.\n";
            break;
        case 'PLAN_NOT_FOUND':
            echo "План подписки не найден. Проверьте plan_uuid.\n";
            break;
        default:
            echo "Ошибка авторизации: {$response['message']}\n";
            break;
    }
    
    // Логирование ошибок валидации
    if (isset($response['errors'])) {
        foreach ($response['errors'] as $field => $messages) {
            echo "Поле {$field}: " . implode(', ', $messages) . "\n";
        }
    }
}
```

## Безопасность

### Рекомендации по использованию

1. **Храните токены безопасно** - не передавайте токены в URL параметрах, используйте POST запросы
2. **Проверяйте срок действия** - регулярно валидируйте токены перед использованием
3. **Завершайте сессии** - всегда вызывайте `destroySession()` после завершения процесса
4. **Используйте HTTPS** - все запросы должны идти по защищенному соединению
5. **Логируйте операции** - ведите аудит всех операций с сессиями

### Время жизни токенов

- **По умолчанию**: 2 часа
- **Максимальное продление**: 24 часа за один раз
- **Автоматическое удаление**: токены автоматически удаляются при истечении

## Интеграция с Laravel

Если вы используете Laravel пакет `streeboga/genesis-laravel`, все методы доступны через фасад:

```php
use Streeboga\GenesisLaravel\Facades\Genesis;

// Авторизация пользователя
$session = Genesis::auth()->authenticateByEmail([
    'email' => 'user@example.com',
    'project_uuid' => config('genesis.project_uuid')
]);

// Получение URL для оплаты
$paymentUrl = Genesis::auth()->getPaymentUrl(
    $session['data']['session_token'],
    $planUuid
);
```
