<?php

namespace iAvatar777\avatarPay;

use cs\services\VarDumper;
use yii\base\Component;
use yii\base\Exception;
use yii\base\Object;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use Yii;
use yii\httpclient\Client;


/**
 * Class avatarPay
 *
 * https://unisender.com
 *
 * https://one.unisender.com/ru/docs/page/Balance - получить баланс
 *
 * @package avatar\modules\UniSender
 */
class avatarPay extends Component
{
    // https://www.unisender.com/ru/support/integration/api/subscribe
    const DOUBLE_OPTIN_3 = 3; // Если 3, то также считается, что у Вас согласие подписчика уже есть, подписчик добавляется со статусом «новый».

    public $apiUrl = 'https://api.unisender.com/ru/api';

    public $token = '6pbq9zeh3t7tkqoiwii75ukcgub8umug4gyqffwe';

    /**
     * @param string $path
     * @param array $data
     *
     * @return mixed
     *
     * @throws
     */
    public function _call($path, $data = [])
    {
        $data['api_key'] = $this->token;
        $data['format'] = 'json';

        $client = new Client(['baseUrl' => $this->apiUrl]);
        Yii::info(\yii\helpers\VarDumper::dumpAsString([$path, $data]), 'avatar\modules\UniSender\UniSender::_call');
        $response = $client->post($path, $data)->send();
        Yii::info(\yii\helpers\VarDumper::dumpAsString($response), 'avatar\modules\UniSender\UniSender::_call::$response');
        if ($response->headers['http-code'] != 200) {
            throw new \Exception('Код ответа = ' . $response->headers['http-code']);
        }
        $data = Json::decode($response->content);
        if (isset($data['error'])) {
            throw new \Exception($data['error']);
        }

        return $data['result'];
    }

    /**
     * Подписывает
     * https://www.unisender.com/ru/support/integration/api/subscribe
     *
     * @param array $list_ids
     * @param array $fields Ассоциативный массив дополнительных полей.
     * - email - string - обязательный
     * - name - string
     * @param array $options
     * - tags - array - Перечисленные через запятую метки, которые добавляются к подписчику.
     * - request_ip - string - IP-адрес подписчика, с которого поступила просьба о подписке, в формате «NNN.NNN.NNN.NNN»
     * - request_time - string - Дата и время поступления просьбы о подписке
     * - double_optin - int - Число от 0 до 3 - есть ли подтверждённое согласие подписчика, и что делать, если превышен лимит подписок.
     * - confirm_ip - string - IP-адрес подписчика, с которого поступило подтверждение подписки, в формате "NNN.NNN.NNN.NNN"
     * - confirm_time - string - Дата и время подтверждения подписки
     * - overwrite - int - Режим перезаписывания полей и меток, число от 0 до 2 (по умолчанию 0). Задаёт, что делать в случае существования подписчика (подписчик определяется по email-адресу и/или телефону).
     *
     * @return array
     */
    public function subscribe($list_ids, $fields, $options = [])
    {
        $data = [
            'list_ids'  => join(',', $list_ids),
            'fields'    => $fields,
        ];
        if (isset($options['tags'])) {
            $data['tags'] = join(',', $options['tags']);
        }
        $params = [
            'request_ip',
            'request_time',
            'double_optin',
            'confirm_ip',
            'confirm_time',
            'overwrite',
        ];
        foreach ($params as $param) {
            if (isset($options[$param])) {
                $data[$param] = $options[$param];
            }
        }

        return $this->_call('subscribe', $data);
    }

    /**
     * https://www.unisender.com/ru/support/integration/api/importcontacts
     *
     * @param array $field_names
     * @param array $data двумерный массив
     */
    public function importContacts($field_names, $data)
    {

    }

    public function getBalance()
    {
        /**
         * {
             * "result": {
                 * "id": 2708021,
                 * "login": "dram1008@yandex.ru",
                 * "phone": "+7-925-237-45-01",
                 * "email": "dram1008@yandex.ru",
                 * "status": 1,
                 * "firstName": "Святослав",
                 * "lastName": "",
                 * "middleName": "",
                 * "balance": {
                     * "currency": "USD",
                     * "main": 0,
                     * "bonus": 0,
                     * "creditLimit": 0
                 * },
                 * "tariff": {
                     * "name": "Часто 500",
                     * "dateFrom": "29.11.2017 06:17",
                     * "dateTo": "29.12.2017 06:17",
                     * "messageLimit": 0,
                     * "subscriberLimit": 500,
                     * "messagesUsed": 0,
                     * "subscribersUsed": 5,
                     * "period": "1 month",
                     * "price": 10,
                     * "currency": "USD"
                 * },
                 * "services": [],
                 * "timeZone": "UTC",
                 * "country": "RUS",
                 * "apiKey": "6pbq9zeh3t7tkqoiwii75ukcgub8umug4gyqffwe",
                 * "apiMode": "on"
             * }
         * }
         */
        $result = $this->_call('getUserData');

        return $result;
    }
}