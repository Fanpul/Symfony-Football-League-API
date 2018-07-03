<?php

namespace App\Tests\Controller;

use App\Tests\Controller\Traits\AuthorizeTrait;
use PHPUnit\Framework\TestCase;

class TeamControllerTest extends TestCase
{
    use AuthorizeTrait;

    public function testBrowse()
    {
        $token = $this->authorize();

        $response = $this->client->get('/v1/teams',[
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $token)
            ],
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

    /**
     * contradiction method
     */
    public function testAdd()
    {
        $token = $this->authorize();

        $team = [
            'name' => 'New football team',
            'strip' => '#000000',
            'league_id' => 0,
        ];

        $response = $this->client->post('/v1/teams',[
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $token)
            ],
            'form_params' => $team
        ]);

        $body = $response->getBody()->getContents();
        $content = json_decode($body, true);
        $header = $response->getHeader('Content-Type');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/json', array_shift($header));
        $this->assertJson($body);

        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('error', $content);
    }

    /**
     * contradiction method
     */
    public function testUpdate()
    {
        $token = $this->authorize();

        $team = [
            'name' => 'New football team',
            'strip' => '#000000',
            'league_id' => 0,
        ];

        $response = $this->client->put('/v1/teams/0',[
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $token)
            ],
            'form_params' => $team
        ]);

        $body = $response->getBody()->getContents();
        $content = json_decode($body, true);
        $header = $response->getHeader('Content-Type');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', array_shift($header));
        $this->assertJson($body);

        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('error', $content);
    }
}
