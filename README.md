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
```

## Доступные клиенты

- `GenesisClient` — базовый HTTP-клиент (GET/POST/PUT/DELETE), обработка ошибок (404/422/5xx), свойство `auth`, `features`, `demo`, `billing`.
- `AuthClient` — методы: `login`, `register`, `refresh`, `logout`.
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

- Добавить примеры в `examples/` (quickstart для каждого клиента).
- Настроить GitHub Actions для автоматических тестов и lint.
- Опубликовать пакет на Packagist и документировать версионирование.

---

Если хотите, могу одновременно:
- Создать `examples/` с рабочими скриптами
- Добавить `README` на русском и английском
- Настроить CI (GitHub Actions) для запуска тестов

Что делаем следующим шагом? 
