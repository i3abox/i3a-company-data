<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/12/25
 * Time: 15:39
 */
namespace I3A\Sdk\Company;

use I3A\Sdk\Company\Aliyun\Enterprise;
use I3A\Sdk\Company\I3A\Client;
use OverNick\Support\Config;

/**
 * @method basic($company)
 *
 * Class CompanyManage
 *
 * @property Config $config
 * @property Client $i3a
 * @property Enterprise $aliyun
 *
 * @package I3A\Sdk\Company
 */
class CompanyManage extends ServiceContainer
{

    protected $providers = [
        Aliyun\ServiceProvider::class,
        I3A\ServiceProvider::class
    ];

    /**
     * @param $driver
     * @return $this
     */
    public function driver($driver)
    {
        $this->config->set('default', $driver);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDriver()
    {
        return $this->config->get('default', 'aliyun');
    }

    /**
     * 调用方法
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->offsetGet($this->getDriver()), $name], $arguments);
    }

}