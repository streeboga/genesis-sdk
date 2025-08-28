# Genesis PHP SDK (streeboga/genesis)

Лёгкий PHP SDK для взаимодействия с API платформы Genesis: авторизация, биллинг, управление фичами, demo и утилиты для биллинга.

## Установка

Для локальной разработки пакет подключается как `path`-пакет (уже сконфигурирован в корневом `composer.json`). Для публикации используйте Packagist.

```bash
composer require streeboga/genesis
```

## Быстрый старт

```php
use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;

$config = Config::fromArray([
  'api_key' => 'YOUR_API_KEY',
  'base_url' => 'https://api.genesis.com/v1/'
]);

$client = GenesisClient::fromConfig($config);

// Features
$features = $client->features->getFeatures($projectId, $userId);

// Demo
$status = $client->demo->getStatus($projectId, $userId);

// Billing
$planFeatures = $client->billing->getPlanFeatures($projectId, $planUuid);
$overage = $client->billing->calculateOveragePrice($projectId, $planUuid, 'api_calls', 10);

// Auth
$tokens = $client->auth->login(['email' => 'a@b.com', 'password' => 'secret']);

// User Auth API - авторизация по email с токеном сессии
$session = $client->auth->authenticateByEmail([
    'email' => 'user@example.com',
    'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000'
]);

$paymentUrl = $client->auth->getPaymentUrl($session['session_token'], $planUuid);
```

## Доступные клиенты

- `GenesisClient` — базовый HTTP-клиент (GET/POST/PUT/DELETE), обработка ошибок (404/422/5xx), свойство `auth`, `features`, `demo`, `billing`.
- `AuthClient` — методы: `login`, `register`, `refresh`, `logout`, `authenticateByEmail`, `validateSession`, `getPaymentUrl`, `extendSession`, `destroySession`, `getSessionInfo`.
- `FeaturesClient` — методы: `getFeatures`, `checkFeature`, `consumeFeature`, `getStats`.
- `DemoClient` — методы: `getStatus`, `giveDemo`, `extendDemo`, `revokeDemo`.
- `BillingClient` — методы: `createSubscription`, `initiatePayment`, `getPlanFeatures`, `getPlanMetadata`, `getOverage`, `consumeOverage`, `calculateOveragePrice`.

## Утилиты

В `Streeboga\Genesis\Utils\BillingUtils`:

- `calculateOverageTotal(unitPrice, amount)` — вычисляет итоговую сумму по единице и количеству.
- `formatCurrency(amount, currency)` — простое форматирование суммы (`USD`, `EUR`, `RUB`).
- `summarizePlan(metadata)` — краткое описание плана, например `Pro — $10.00`.

Пример использования:

```php
use Streeboga\Genesis\Utils\BillingUtils;

$total = BillingUtils::calculateOverageTotal(0.05, 10); // 0.5
$label = BillingUtils::summarizePlan(['name' => 'Pro', 'price' => 1000, 'currency' => 'RUB']);
```

## Ошибки и исключения

SDK выбрасывает специфичные исключения в `Streeboga\Genesis\Exceptions`:

- `NotFoundException` — 404
- `ValidationException` — 422 (с массивом ошибок)
- `ServerException` — 5xx
- `ApiException` — общее для 4xx/прочих

Оборачивайте вызовы в `try/catch` для безопасной обработки.

## Тесты

Для запуска тестов пакета используйте Pest (находясь в корне проекта):

```bash
./vendor/bin/pest packages/streeboga/genesis/tests
```

Тесты используют Guzzle MockHandler, поэтому они не делают реальных HTTP-запросов.

## Документация API

Исходные спецификации находятся в `docs/api/*` и `docs/openapi.yaml`. В README проекта — ссылки на Features API, Payments и PRD.

## Дальнейшие шаги (рекомендации)

- Добавить примеры в `examples/` (quickstart для каждого клиента). Уже есть `packages/streeboga/genesis/examples/session_example.php` и `examples/webhook.php` — их можно использовать как исходники.
- Настроить GitHub Actions для автоматических тестов и lint (рекомендуется запускать Pest + Pint).
- Опубликовать пакет на Packagist и документировать версионирование.

### Новое в SDK (с недавними изменениями)

- `EmbedClient` — поддержка виджетных endpoint'ов: `getInfo`, `getConfig`, `getStyle`, `getScript`, `health`, `authLogin`, `usersProfile`, `sessionsList`, `revokeSession`.
- `FeaturesClient::getPricing($project, $user)` — получение прайсинга фич пользователя.
- `BillingClient::listPlans($project)` и `BillingClient::getSubscriptionStatus($project, $user)` — управленческие billing endpoints.
- `AuthClient` расширен: `forgotPassword`, `resetPassword`, `resendVerification`, `verifyEmail`, `twoFaChallenge`, `twoFaVerify`.
- `GenesisClient::asUser($accessToken)` — лёгкое клонирование клиента для выполнения запросов от имени пользователя (добавляет `Authorization: Bearer ...`).
- `GenesisClient::getRaw($uri)` — получить raw тело ответа (CSS/JS assets в Embed).

### Сессии и persistence

SDK предоставляет `UserSessionManager` для управления пользовательскими сессиями с автorefresh токена:

- `Streeboga\Genesis\Session\UserSessionManager` — хранит `access_token`, `refresh_token`, `expires_at`, умеет автоматически вызывать `AuthClient::refresh()` при необходимости и возвращать `GenesisClient::asUser()`.
- Persistence: `TokenStoreInterface` и реализации:
  - `FileTokenStore` — простое файловое хранилище (JSON файлов).
  - `RedisTokenStore` — реализация поверх Redis (поддерживает Predis / ext-redis-like интерфейс).

Пример использования (см. `packages/streeboga/genesis/examples/session_example.php`):

```php
use Streeboga\Genesis\Config;
use Streeboga\Genesis\GenesisClient;
use Streeboga\Genesis\Session\UserSessionManager;
use Streeboga\Genesis\Session\Storage\FileTokenStore;

$config = Config::fromArray(['api_key' => 'SERVICE_KEY', 'base_url' => 'https://api.genesis.com/v1/']);
$client = GenesisClient::fromConfig($config);

// login
$tokens = $client->auth->login(['email' => 'user@example.com', 'password' => 'secret']);

$manager = new UserSessionManager($client);
$manager->setAccessTokenForTesting($tokens['access_token'] ?? 'demo', $tokens['refresh_token'] ?? 'demo', $tokens['expires_in'] ?? 3600);

$store = new FileTokenStore(sys_get_temp_dir() . '/genesis_sessions');
$manager->setStore($store);
$manager->saveSession('user_1_session');

$userClient = $manager->getClient(); // GenesisClient configured with Bearer token
$profile = $userClient->get('projects/1/users/1/profile');
```

### Рекомендации по безопасности

- Для server-to-server операций используйте сервисный `X-API-Key` (по умолчанию в SDK). Для действий от имени пользователя — используйте `asUser()` или `UserSessionManager` с user access token.

---

Если хотите, могу одновременно:
- Создать дополнительные примеры в `packages/streeboga/genesis/examples/` для: Embed quickstart, Billing example, Redis store example.
- Добавить английскую версию README и примеры запуска CI.

Что делаем следующим шагом?

## Webhooks

SDK предоставляет утилиты для валидации и обработки webhook notify от платёжных провайдеров и других сервисов.

- `Streeboga\Genesis\Webhook\Handler` — низкоуровневый обработчик: проверка подписи (HMAC-SHA256, base64), парсинг JSON и вызов callback.
- `Streeboga\Genesis\Webhook\Client` — удобный обёртка для приёма заголовков (массив), извлечения подписи и передачи в Handler.

Пример (см. `examples/webhook.php`):

```php
require __DIR__ . '/examples/webhook.php';
```

Он показывает базовый обработчик notify: проверка подписи, логирование и ответ 200. 
