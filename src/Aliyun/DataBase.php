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
        $token = $token ?? $this->app->config->get('aliyun.token');
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
            $this->app->config->get('aliyun.access_secret'),
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
            $this->app->config->get('aliyun.access_key_id').
            ':'
            .$this->getSign($string);
        return $this->getHttpClient()->request($method , $url, $options);
    }

    /**
     * 格式化阿里云图谱的数据格式
     *
     * @param array $company
     * @return array
     */
    public function setData(array $company = [])
    {
        //$company = json_decode($data, true);

        // 企业基本信息
        $basic = [
            'basic' => array_get($company,
                'result.result.module.reportModuleMap.BASIC.categoryMap.BASIC.reportPropertiesMap'),
            // 公司股东
            'shareholder' => array_get($company,
                'result.result.module.reportModuleMap.BASIC.categoryMap.BASIC_SHAREHOLDER.reportPropertiesMap'),
            // 公司分支机构
            'branch'=> array_get($company,
                'result.result.module.reportModuleMap.BASIC.categoryMap.BASIC_BRANCH.reportPropertiesMap'),
            // 公司主要成员
            'manager' => array_get($company,
                'result.result.module.reportModuleMap.BASIC.categoryMap.BASIC_MANAGER.reportPropertiesMap')
        ];
        // 对外投资
        $inv = [
            //企业对外投资
            'company' => array_get($company,
                'result.result.module.reportModuleMap.INV.categoryMap.INV_ENTERPRISE.reportPropertiesMap'),
            //法定代表人对外投资
            'person' => array_get($company,
                'result.result.module.reportModuleMap.INV.categoryMap.INV_LEGAL_PERSON.reportPropertiesMap')
        ];
        // 风险
        $risk = [
            // 股权风险－股权冻结
            'equity_freeze' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_EQUITY_FREEZE.reportPropertiesMap'),
            // 司法风险－失信公告
            'announcement' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_EXECUTION_ANNOUNCEMENT.reportPropertiesMap'),
            // 财产风险－动产抵押
            'mortgage' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_MORTGAGE.reportPropertiesMap'),
            // 财产风险－动产抵押物
            'mortgage_subject' =>  array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_MORTGAGE_SUBJECT.reportPropertiesMap'),
            // 变更信息
            'change' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_CHANGE_DETAIL.reportPropertiesMap'),
            // 行政处罚
            'punishment' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_PUNISHMENT.reportPropertiesMap'),
            // 经营异常
            'operation' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_OPERATION_EXCEPTION.reportPropertiesMap'),
            // 司法风险－执行公告
            'punish' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_PUNISH_BREAK.reportPropertiesMap'),
            // 股权风险－股权出质
            'pledged' => array_get($company,
                'result.result.module.reportModuleMap.RISK.categoryMap.RISK_EQUITY_PLEDGED.reportPropertiesMap'),

        ];
        // 对外任职
        $position = array_get($company,
            'result.result.module.reportModuleMap.EXTERNAL_POSITION.categoryMap.EXTERNAL_POSITION_LEGAL_PERSON.reportPropertiesMap');
        // 年报
        $financial = array_get($company, 'FINANCIAL');

        // 招聘
        $recurit = [
            'recurit' => array_get($company, 'RECURIT'),
            'quota' => array_get($company, 'RECURIT_QUOTA'),
            'statistic' => array_get($company, 'RECURIT_STATISTIC'),

        ];
        // 信誉
        $credit = array_get($company,
            'result.result.module.reportModuleMap.CREDIT.categoryMap.CREDIT_RATE.reportPropertiesMap');

        // 把内容按type 对应的值组成数组
        $companyInfo = [
            'financial' => $financial,
            'recurit' => $recurit,
            'basic' => $basic,
            'credit' => $credit,
            'inv' => $inv,
            'external' => $position,
            'risk' => $risk,
        ];
        return $companyInfo;
    }

}