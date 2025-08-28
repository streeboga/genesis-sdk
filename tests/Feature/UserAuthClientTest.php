<?php

namespace Streeboga\Genesis\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Streeboga\Genesis\Tests\TestCase;
use Streeboga\Genesis\GenesisClient;

class UserAuthClientTest extends TestCase
{
    private GenesisClient $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $this->client = new GenesisClient(
            'test-api-key',
            'https://api.test.com/',
            $httpClient
        );
    }

    public function testAuthenticateByEmail(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Пользователь успешно авторизован',
            'data' => [
                'session_token' => str_repeat('a', 64),
                'user' => [
                    'id' => 123,
                    'uuid' => '550e8400-e29b-41d4-a716-446655440001',
                    'name' => 'Test User',
                    'email' => 'test@example.com'
                ],
                'project_user' => [
                    'id' => 456,
                    'uuid' => '550e8400-e29b-41d4-a716-446655440002',
                    'status' => 'active'
                ],
                'expires_at' => '2024-01-15T14:30:00.000000Z'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $response = $this->client->auth->authenticateByEmail([
            'email' => 'test@example.com',
            'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000',
            'name' => 'Test User'
        ]);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testValidateSession(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Токен сессии валиден',
            'data' => [
                'user' => [
                    'id' => 123,
                    'email' => 'test@example.com',
                    'name' => 'Test User'
                ],
                'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000',
                'expires_at' => '2024-01-15T14:30:00.000000Z'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $response = $this->client->auth->validateSession($sessionToken);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetPaymentUrl(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'URL для оплаты сформирован',
            'data' => [
                'checkout_url' => 'https://example.com/checkout/987f6543-e21c-34d5-b678-987654321000?session_token=' . str_repeat('a', 64),
                'plan' => [
                    'uuid' => '987f6543-e21c-34d5-b678-987654321000',
                    'name' => 'Базовый план',
                    'price' => 1000,
                    'currency' => 'RUB',
                    'description' => 'Описание плана'
                ],
                'user' => [
                    'id' => 123,
                    'email' => 'test@example.com',
                    'name' => 'Test User'
                ]
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $planUuid = '987f6543-e21c-34d5-b678-987654321000';
        
        $response = $this->client->auth->getPaymentUrl($sessionToken, $planUuid);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testExtendSession(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Сессия успешно продлена',
            'data' => [
                'expires_at' => '2024-01-15T18:30:00.000000Z'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $response = $this->client->auth->extendSession($sessionToken, 4);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testExtendSessionWithDefaultHours(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Сессия успешно продлена',
            'data' => [
                'expires_at' => '2024-01-15T16:30:00.000000Z'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $response = $this->client->auth->extendSession($sessionToken);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testDestroySession(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Сессия успешно завершена'
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $response = $this->client->auth->destroySession($sessionToken);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetSessionInfo(): void
    {
        $expectedResponse = [
            'success' => true,
            'data' => [
                'user' => [
                    'id' => 123,
                    'email' => 'test@example.com',
                    'name' => 'Test User'
                ],
                'project_user_id' => 456,
                'project_uuid' => '550e8400-e29b-41d4-a716-446655440000',
                'plan_uuid' => '987f6543-e21c-34d5-b678-987654321000',
                'authenticated_at' => '2024-01-15T12:30:00.000000Z',
                'expires_at' => '2024-01-15T14:30:00.000000Z',
                'ip_address' => '192.168.1.1'
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $response = $this->client->auth->getSessionInfo($sessionToken);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testAuthenticateByEmailWithError(): void
    {
        $expectedResponse = [
            'success' => false,
            'message' => 'Проект не найден',
            'code' => 'PROJECT_NOT_FOUND'
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($expectedResponse))
        );

        try {
            $this->client->auth->authenticateByEmail([
                'email' => 'test@example.com',
                'project_uuid' => '00000000-0000-0000-0000-000000000000'
            ]);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Streeboga\Genesis\Exceptions\ApiException $e) {
            $this->assertInstanceOf(\Streeboga\Genesis\Exceptions\ApiException::class, $e);
        }
    }

    public function testValidateSessionWithExpiredToken(): void
    {
        $expectedResponse = [
            'success' => false,
            'message' => 'Токен сессии истек',
            'code' => 'SESSION_EXPIRED'
        ];

        $this->mockHandler->append(
            new Response(401, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('x', 64);
        
        try {
            $this->client->auth->validateSession($sessionToken);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Streeboga\Genesis\Exceptions\ApiException $e) {
            $this->assertInstanceOf(\Streeboga\Genesis\Exceptions\ApiException::class, $e);
        }
    }

    public function testGetPaymentUrlWithInvalidPlan(): void
    {
        $expectedResponse = [
            'success' => false,
            'message' => 'План подписки не найден',
            'code' => 'PLAN_NOT_FOUND'
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($expectedResponse))
        );

        $sessionToken = str_repeat('a', 64);
        $planUuid = '00000000-0000-0000-0000-000000000000';
        
        try {
            $this->client->auth->getPaymentUrl($sessionToken, $planUuid);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Streeboga\Genesis\Exceptions\ApiException $e) {
            $this->assertInstanceOf(\Streeboga\Genesis\Exceptions\ApiException::class, $e);
        }
    }

    public function testValidationErrors(): void
    {
        $expectedResponse = [
            'success' => false,
            'message' => 'Ошибка валидации данных',
            'errors' => [
                'email' => ['The email field is required.'],
                'project_uuid' => ['The project uuid field is required.']
            ]
        ];

        $this->mockHandler->append(
            new Response(422, [], json_encode($expectedResponse))
        );

        try {
            $this->client->auth->authenticateByEmail([
                // Пустые данные для тестирования валидации
            ]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (\Streeboga\Genesis\Exceptions\ValidationException $e) {
            $this->assertInstanceOf(\Streeboga\Genesis\Exceptions\ValidationException::class, $e);
        }
    }
}
