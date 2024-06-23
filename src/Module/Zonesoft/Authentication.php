<?php

namespace App\Module\Zonesoft;

use Symfony\Component\HttpClient\HttpClient;

class Authentication
{
    private $httpClient;

    public function __construct() {
        $this->httpClient = HttpClient::create();
    }

    //string required url, path to make the API requests
 	private $url;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url = null)
    {
        $this->url = $url;
    }

    //string auth_hash url, token to make the API requests
    private $token;

    public function getToken()
    {
        return $this->token;
    }

    public function setToken(string $token = null)
    {
        $this->token = $token;
    }

    /**
    *Get the Token for requests
    *@param array $arUserData
    **/
    protected function authHash($arUserData = [])
    {
            $response = $this->httpClient->request(static::METHOD, $this->getUrl().static::ACCESS,
            [
                'json' => [
                    static::ENTITY => $arUserData
                ]
            ]);
        return json_decode($response->getContent())->Response->Content->auth_hash ?? null;
    }

    /**
    * Current path for request
    *@param array $arData
    **/
    protected function processData($arData = [])
    {

        $data = [
            'json' =>[
                'auth_hash' => $this->getToken(),
                'timeout' => 0.5,
                static::ENTITY => $arData
            ]
        ];
        ini_set('default_socket_timeout', '-1');
        $response = $this->httpClient->request(static::METHOD, $this->getUrl().$GLOBALS['ACCESS'], $data);
        $statusCode = $response->getStatusCode();

        if ($statusCode > 400) {
            $entity = static::ENTITY;
            $r = json_decode($response->getContent(false));

            $error = is_array($r->Response->Content->$entity) ?
                $r->Response->Content->$entity[0]
                :
                $r->Response->Content->$entity;

            return property_exists($error, '_errors') ?
                ['return' => 'error', 'msg' => $r->Response->Content->$entity[0]->_errors[0]]
            :
                ['return' => 'error', 'msg' => 'Opps! Some error occurred on invoice system!'];
        }

        if ($statusCode == 204) {
            return 'No results found!';
        }

        else {
            return $response->getContent() ? json_decode($response->getContent()) : 'No results found';
        }

    }

}