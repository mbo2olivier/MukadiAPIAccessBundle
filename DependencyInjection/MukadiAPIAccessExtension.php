<?php

namespace Mukadi\APIAccessBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class MukadiAPIAccessExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if($config['driver'] === "orm"){
            $container->setAlias("mukadi_api_access.driver","doctrine.orm.entity_manager");
        }

        $this->setupClients($config['clients'],$container,new Reference("mukadi_api_access.driver"));

    }

    private function setupClients(array $clients,ContainerBuilder $container,$driver){
        $MANAGER_BASE_CLASS = "Mukadi\\APIAccessBundle\\Model\\ClientManager";
        $PROVIDER_BASE_CLASS = "Mukadi\\APIAccessBundle\\Security\\ClientProvider";
        $CLIENT_BASE_CLASS = "\\Mukadi\\APIAccessBundle\\Model\\Client";

        foreach ($clients as $slug => $client) {
            $class = $client['client_class'];
            if(! is_subclass_of($class,$CLIENT_BASE_CLASS)){
                throw new LogicException(sprintf("class %s is not sub class of %s",$class,$CLIENT_BASE_CLASS));
            }
            $slug = strtolower($slug);
            $class_param = sprintf("mukadi_api_access.%s.class",$slug);
            $container->setParameter($class_param,$class);

            $manager = sprintf("mukadi_api_access.%s.client_manager",$slug);
            if(!isset($client['client_manager']) || is_null($client['client_manager'])){
                $cm = new Definition($MANAGER_BASE_CLASS,array(
                    $driver,
                    "%".$class_param."%"
                ));
                $container->setDefinition($manager,$cm);
            }else{
                $container->setAlias($manager,$client['client_manager']);
            }

            $provider = sprintf("mukadi_api_access.%s.client_provider",$slug);
            if(!isset($client['client_manager']) || is_null($client['client_provider'])){
                $cp = new Definition($PROVIDER_BASE_CLASS,array(
                    new Reference($manager)
                ));
                $container->setDefinition($provider,$cp);
            }else{
                $container->setAlias($provider,$client['client_provider']);
            }
        }
    }
}
