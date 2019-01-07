<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/8/16
 * Time: 17:24
 */
namespace I3A\Sdk\Company\Aliyun;

/**
 * 企业图谱
 *
 * Class Enterprise
 * @package I3A\Sdk\Aliyun\Shujia
 */
class Enterprise extends DataBase
{
    /**
     * 全息画像查询地址
     *
     * @var string
     */
    protected $basicUrl = 'https://dtplus-cn-beijing.data.aliyuncs.com/{$service_code}/eprofile_hi/basic';

    /**
     * 模糊查询url
     *
     * @var string
     */
    protected $likeUrl = 'https://dtplus-cn-beijing.data.aliyuncs.com/{$service_code}/eprofile_hi/getCompleteByName';

    /**
     * 根据公司名称查询企业画像
     *
     * @param string $companyName 公司名称（完整全名）
     * @return array|false
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function companyMap($companyName)
    {
        $result =  $this->request(
            str_replace('{$service_code}',
                $this->app->config->get('aliyun.service_code'),
                $this->basicUrl
            ),
            [
                'token' => $this->app->config->get('aliyun.token'),
                'compName' => $companyName
            ],
            'POST'
        );

        if($result->getStatusCode() != 200){
            return false;
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // 解密参数
        $dataArray = json_decode($this->getData($data , true) , true);
        return $this->setData($dataArray);
    }

    /**
     * 根据公司名称搜索相匹配公司列表
     *
     * @param string $companyName 公司名称(模糊名称)，公司名开头必须带城市
     * @return array|false
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function like($companyName)
    {
        $result = $this->request(
            str_replace('{$service_code}',
                $this->app->config->get('aliyun.enterprise.service_code'),
                $this->likeUrl
            ),
            [
                'token' => $this->app->config->get('aliyun.enterprise.token'),
                'compName' => $companyName,
                'ak' => $this->app->config->get('aliyun.enterprise.access_secret'),
            ]
        );

        if($result->getStatusCode() != 200){
            return false;
        }
        return json_decode($result->getBody()->getContents(), true);
    }

}