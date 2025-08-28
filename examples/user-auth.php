<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;

// Конфигурация
$config = Config::fromArray([
    'api_key' => $_ENV['GENESIS_API_KEY'] ?? 'your-api-key',
    'base_url' => $_ENV['GENESIS_BASE_URL'] ?? 'https://your-genesis-api.com/api/'
]);

$client = GenesisClient::fromConfig($config);

echo "=== Genesis User Auth API Example ===\n\n";

try {
    // 1. Авторизация пользователя по email
    echo "1. Авторизация пользователя...\n";
    
    $authResponse = $client->auth->authenticateByEmail([
        'email' => 'test@example.com',
        'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000',
        'name' => 'Тестовый Пользователь'
    ]);
    
    if (!$authResponse['success']) {
        throw new Exception('Ошибка авторизации: ' . $authResponse['message']);
    }
    
    $sessionToken = $authResponse['data']['session_token'];
    $user = $authResponse['data']['user'];
    
    echo "✅ Пользователь авторизован:\n";
    echo "   - ID: {$user['id']}\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Имя: {$user['name']}\n";
    echo "   - Токен сессии: " . substr($sessionToken, 0, 8) . "...\n";
    echo "   - Истекает: {$authResponse['data']['expires_at']}\n\n";
    
    // 2. Проверка валидности токена
    echo "2. Проверка валидности токена...\n";
    
    $validateResponse = $client->auth->validateSession($sessionToken);
    
    if ($validateResponse['success']) {
        echo "✅ Токен валиден\n";
        echo "   - Проект: {$validateResponse['data']['project_uuid']}\n";
        echo "   - План: {$validateResponse['data']['plan_uuid']}\n\n";
    } else {
        throw new Exception('Токен невалиден: ' . $validateResponse['message']);
    }
    
    // 3. Получение URL для оплаты
    echo "3. Получение URL для оплаты...\n";
    
    $paymentResponse = $client->auth->getPaymentUrl(
        $sessionToken,
        '987f6543-e21c-34d5-b678-987654321000'
    );
    
    if (!$paymentResponse['success']) {
        throw new Exception('Ошибка получения URL: ' . $paymentResponse['message']);
    }
    
    $checkoutUrl = $paymentResponse['data']['checkout_url'];
    $plan = $paymentResponse['data']['plan'];
    
    echo "✅ URL для оплаты сформирован:\n";
    echo "   - URL: {$checkoutUrl}\n";
    echo "   - План: {$plan['name']}\n";
    echo "   - Цена: {$plan['price']} {$plan['currency']}\n\n";
    
    // 4. Получение информации о сессии
    echo "4. Получение информации о сессии...\n";
    
    $sessionInfo = $client->auth->getSessionInfo($sessionToken);
    
    if ($sessionInfo['success']) {
        $data = $sessionInfo['data'];
        echo "✅ Информация о сессии:\n";
        echo "   - Пользователь: {$data['user']['name']} ({$data['user']['email']})\n";
        echo "   - Проект: {$data['project_uuid']}\n";
        echo "   - План: {$data['plan_uuid']}\n";
        echo "   - Авторизован: {$data['authenticated_at']}\n";
        echo "   - Истекает: {$data['expires_at']}\n";
        echo "   - IP адрес: {$data['ip_address']}\n\n";
    }
    
    // 5. Продление сессии
    echo "5. Продление сессии на 4 часа...\n";
    
    $extendResponse = $client->auth->extendSession($sessionToken, 4);
    
    if ($extendResponse['success']) {
        echo "✅ Сессия продлена до: {$extendResponse['data']['expires_at']}\n\n";
    } else {
        echo "❌ Ошибка продления сессии: {$extendResponse['message']}\n\n";
    }
    
    // 6. Завершение сессии
    echo "6. Завершение сессии...\n";
    
    $destroyResponse = $client->auth->destroySession($sessionToken);
    
    if ($destroyResponse['success']) {
        echo "✅ Сессия успешно завершена\n\n";
    } else {
        echo "❌ Ошибка завершения сессии: {$destroyResponse['message']}\n\n";
    }
    
    // 7. Проверка, что токен больше не валиден
    echo "7. Проверка завершенной сессии...\n";
    
    $finalValidate = $client->auth->validateSession($sessionToken);
    
    if (!$finalValidate['success']) {
        echo "✅ Токен больше не валиден (сессия завершена)\n";
        echo "   - Сообщение: {$finalValidate['message']}\n\n";
    } else {
        echo "❌ Токен все еще валиден (ошибка завершения сессии)\n\n";
    }
    
    echo "=== Пример завершен успешно ===\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}

// Дополнительные примеры использования

echo "\n=== Дополнительные примеры ===\n\n";

// Пример обработки ошибок
echo "Пример обработки ошибок:\n";

try {
    // Попытка авторизации с несуществующим проектом
    $errorResponse = $client->auth->authenticateByEmail([
        'email' => 'test@example.com',
        'project_uuid' => '00000000-0000-0000-0000-000000000000'
    ]);
    
    if (!$errorResponse['success']) {
        echo "❌ Ожидаемая ошибка: {$errorResponse['message']}\n";
        if (isset($errorResponse['code'])) {
            echo "   - Код ошибки: {$errorResponse['code']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Исключение: " . $e->getMessage() . "\n";
}

echo "\nПример валидации несуществующего токена:\n";

try {
    $invalidToken = str_repeat('x', 64);
    $validateResponse = $client->auth->validateSession($invalidToken);
    
    if (!$validateResponse['success']) {
        echo "❌ Ожидаемая ошибка: {$validateResponse['message']}\n";
        if (isset($validateResponse['code'])) {
            echo "   - Код ошибки: {$validateResponse['code']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Исключение: " . $e->getMessage() . "\n";
}

echo "\n=== Все примеры завершены ===\n";
