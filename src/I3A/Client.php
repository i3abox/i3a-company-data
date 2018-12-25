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
        ]);
    }



}