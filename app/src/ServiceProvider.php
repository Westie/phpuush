<?php

namespace App;

use App\Configuration\Configuration;
use App\Repository\File as FileRepository;
use App\Repository\User as UserRepository;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{
    /**
     *  Provides
     */
    protected $provides = [
        AdapterInterface::class,
        Configuration::class,
        FileRepository::class,
        UserRepository::class,
    ];

    /**
     *  Register our services
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->share(Configuration::class, function () {
            return new Configuration();
        });

        $container->share(AdapterInterface::class, function () use (&$container) {
            return new Adapter($container->get(Configuration::class)->get('databases.sql'));
        });

        $container->share(FileRepository::class, function () use (&$container) {
            return new FileRepository(
                $container->get(Configuration::class),
                $container->get(AdapterInterface::class)
            );
        });

        $container->share(UserRepository::class, function () use (&$container) {
            return new UserRepository(
                $container->get(Configuration::class),
                $container->get(AdapterInterface::class)
            );
        });
    }
}
