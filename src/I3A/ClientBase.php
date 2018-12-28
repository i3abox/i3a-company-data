<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/12/25
 * Time: 16:18
 */

namespace I3A\Sdk\Company\I3A;

use I3A\Sdk\Company\CompanyBase;

class ClientBase extends CompanyBase
{
    protected $baseUrl = 'https://apiserv.i3abox.com';

    /**
     * 签名
     *
     * @return string
     */
    public function buildSign()
    {
        $sign = [
            'access_id' => $this->app->config->get('i3a.access_id'),
            'time' => time()
        ];
        $sign['access_key'] = hash_hmac('sha1',http_build_query($sign), $this->app->config->get('i3a.access_key'));
        return implode('', $sign);
    }

    /**
     * 接口请求
     *
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $options
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException | \Exception
     */
    public function request($url, array $params = [], $method = 'POST', array $options = [])
    {
        $options = array_merge($options, [
            'verify' => false,
            'http_errors' => false,
            'json' => $params,
            'headers' => [
                'I3A-AUTH' => $this->buildSign()
            ]
        ]);
        $response = $this->getHttpClient()->request($method, $this->gateWay($url), $options);
        if($response->getStatusCode() != 200){
            return false;
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if(array_get($result , 'errcode') != 0){
            return false;
        }
        return array_get($result , 'data');
    }

    /**
     * @param null $url
     * @return string
     * @throws \Exception
     */
    public function gateWay($url = null)
    {
        $ip = gethostbyname(parse_url($this->baseUrl, PHP_URL_HOST));

        $match = "/^(172\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})|(10\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})|(192\.168\.[0-9]{1,3}\.[0-9]{1,3})$/";

        if(preg_match($match, $ip)){
            throw new \Exception('');
        }

        return $this->baseUrl . '/' . ltrim($url,'/');
    }

}