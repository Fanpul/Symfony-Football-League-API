<?php

namespace App\Tests\Controller;

use App\Tests\Controller\Traits\AuthorizeTrait;
use PHPUnit\Framework\TestCase;

class LeagueControllerTest extends TestCase
{
    use AuthorizeTrait;

    public function testBrowse()
    {
        $token = $this->authorize();

        $response = $this->client->get('/v1/league',[
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $token)
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
