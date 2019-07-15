<?php

namespace iAvatar777\avatarPay;

use yii\base\Component;
use yii\base\Exception;
use yii\base\Object;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use Yii;
use yii\httpclient\Client;


/**
 * v 1.0
 */
class avatarPay extends Component
{
    public $key;
    public $secret;

    public $apiUrl = 'https://api.processing.i-am-avatar.com';

    /**
     * @param string    $method 'post' | 'get'
     * @param string    $path
     * @param array     $data
     *
     * @return mixed
     *
     * @throws
     */
    public function _call($method, $path, $data = [])
    {
        $c = new Client(['baseUrl' => $this->apiUrl]);
        $login = $this->key;
        $secret = $this->secret;
        $hash = hash('sha256', ($secret . ((int)(time()/100))));
        $request = $c
            ->post($path, $data)
            ->addHeaders([
                'X-API-KEY'    => $login,
                'X-API-SECRET' => $hash,
            ]);
        $response = $request->send();

        if ($response->headers['http-code'] != 200) {
            throw new \Exception('Код ответа = ' . $response->headers['http-code']);
        }
        $data = Json::decode($response->content);
        if (isset($data['error'])) {
            throw new \Exception($data['error']);
        }

        return $data['result'];
    }
}