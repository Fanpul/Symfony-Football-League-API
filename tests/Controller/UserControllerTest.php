<?php

namespace App\Tests\Controller;

use App\Tests\Controller\Traits\AuthorizeTrait;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    use AuthorizeTrait;

    /**
     * @return void
     */
    public function testLogin()
    {
        $response = $this->authorize(['full_response' => true]);

        $body = $response->getBody()->getContents();
        $content = json_decode($body, true);
        $header = $response->getHeader('Content-Type');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', array_shift($header));
        $this->assertJson($body);

        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('error', $content);
    }

    /**
     * @return void
     */
    public function testRefreshToken()
    {
        $authResponse = $this->authorize(['full_response' => true]);
        $authBody = $authResponse->getBody()->getContents();
        $authContent = json_decode($authBody, true);


        $response = $this->client->post('/v1/refresh-token', [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $authContent['data']['access_token'])
            ],
            'form_params' => [
                'refresh_token' => $authContent['data']['refresh_token']
            ]
        ]);
        $body = $response->getBody()->getContents();

        $content = json_decode($body, true);
        $header = $response->getHeader('Content-Type');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', array_shift($header));
        $this->assertJson($body);

        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('error', $content);
    }
}