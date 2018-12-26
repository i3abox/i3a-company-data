<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2018/12/25
 * Time: 16:05
 */
namespace I3A\Sdk\Company\Aliyun;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['aliyun'] = function($app){
            return new Enterprise($app);
        };
    }

}