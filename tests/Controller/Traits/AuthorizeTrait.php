<?php

namespace App\Tests\Controller\Traits;

use \GuzzleHttp\Client;

/**
 * Trait AuthorizeTrait
 * @package App\Tests\Controller\Traits
 */
trait AuthorizeTrait
{
    /**
     * @var $client Client
     */
    protected $client = null;

    /**
     * init http client
     */
    public function initClient()
    {
        $this->client = new Client([
            'base_uri' => 'http://api-league.local/',
            'http_errors' => false
        ]);
    }

    /**
     * @param array $options
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function authorize($options = [])
    {
        $params = [
            'username' => 'admin',
            'password' => 'admin',
            'full_response' => false
        ];
        $params = array_merge($params, $options);

        $this->initClient();
        $response = $this->client->post('/v1/login', [
            'form_params' => [
                'username' => $params['username'],
                'password' => $params['password']
            ],
        ]);

        if (!empty($params['full_response'])) {
            return $response;
        }

        $body = json_decode($response->getBody()->getContents(), TRUE);
        return $body['data']['access_token'] ?? null;
    }
}
