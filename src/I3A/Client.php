<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/12/25
 * Time: 16:17
 */

namespace I3A\Sdk\Company\I3A;


class Client extends ClientBase
{
    /**
     * 企业图谱信息查询
     *
     * @param $com
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function companyMap($com)
    {
        return $this->request('gateway/api/company/map', [
            'com' => $com
        ], 'GET');
    }

    /**
     * 激活企业图谱服务
     *
     * @param $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function register($params = [])
    {
        return $this->request('gateway/api/gateway.api.company.map/open', $params);
    }

    /**
     * 判断是否激活企业图谱
     *
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function hasRegister($params = [])
    {
        return $this->request('gateway/api/gateway.api.company.map/has', $params, 'GET');
    }



}