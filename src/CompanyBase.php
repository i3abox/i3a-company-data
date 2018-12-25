<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/12/25
 * Time: 16:30
 */
namespace I3A\Sdk\Company;

class CompanyBase
{
    use HttpRequestTrait;

    /**
     * @var CompanyManage
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    public function __construct($app = null)
    {
        $this->app = $app;
    }
}