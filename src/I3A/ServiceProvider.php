<?php
namespace I3A\Sdk\Company\I3A;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['i3a'] = function ($app) {
            return new Client($app);
        };
    }

}