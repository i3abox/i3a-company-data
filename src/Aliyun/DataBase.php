<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/8/16
 * Time: 17:25
 */
namespace I3A\Sdk\Company\Aliyun;

use I3A\Sdk\Company\CompanyBase;
use OverNick\Support\Arr;

/**
 * 数加基础服务
 *
 * Class ShujiaBase
 * @package I3A\Sdk\Aliyun\Shujia
 */
class DataBase extends CompanyBase
{
    /**
     * 获取解密内容
     *
     * @param array $data
     * @param boolean $decrypt
     * @return string
     */
    public function getData(array $data, $decrypt = false)
    {
        $data =  Arr::get($data, 'data', '');

        return $decrypt ? $this->decrypt($data) : $data;
    }

    /**
     * 解密数加服务的返回值
     *
     * @param string $data 待解密字符
     * @param string|null $token 企业图谱提供的token
     * @return string
     */
    public function decrypt($data, $token = null)
    {
        // 获取到传入的token
        $token = $token ?? $this->app->config->get('aliyun.enterprise.token');
        // 得到一个16位用于解密的key
        $key = strlen($token) >= 16 ?
            substr($token,0,16) :
            str_pad($token, 16, '0',STR_PAD_RIGHT);

        // 需要开启openssl扩展
        return openssl_decrypt(
            hex2bin($data),
            'AES-128-ECB',
            hash('MD5', $key, true),
            OPENSSL_PKCS1_PADDING
        );
    }

    /**
     * 获取加密的签名
     *
     * https://help.aliyun.com/document_detail/30245.html
     *
     * @param $data
     * @return string
     */
    protected function getSign($data)
    {
        return base64_encode(hash_hmac(
            'sha1',
            $data,
            $this->app->config->get('aliyun.enterprise.access_secret'),
            true
        ));
    }

    /**
     * 发起请求
     *
     * @param $url
     * @param string $content
     * @param string $method
     * @param array $query
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request($url, $content = '', $method = 'POST', array $query = [])
    {
        $content = is_array($content) ? json_encode($content) : $content;
        // 访问数加应用接口必须要携带的参数
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
                'Date' => gmdate("D, d M Y H:i:s \G\M\T")
            ],
            'body' => $content,
            'verify' => false,
            'http_errors' => false,
        ];
        
        // 只需要路径部分
        $signUrl = parse_url($url, PHP_URL_PATH);
        // 加密的字符串
        $string = implode("\n", array_merge([
            $method,
            $options['headers']['Accept'],
            empty($content) ? '' : base64_encode(md5($content,true)),
            $options['headers']['Content-type'],
            $options['headers']['Date'],
            empty($query) ? $signUrl : $signUrl.'?'.http_build_query($query)
            ]
        ));

        // 认证的签名
        $options['headers']['Authorization'] = "Dataplus ".
            $this->config['enterprise']['access_key_id'].
            ':'
            .$this->getSign($string);
        return $this->getHttpClient()->request($method , $url, $options);
    }
}