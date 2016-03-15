<?php
namespace chartinger\Behat\TwigReportExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;

class TwigReportExtension implements Extension
{

    /*
     * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::getConfigKey()
     */
    public function getConfigKey()
    {
        return "twig_report";
    }

    /*
     * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::load()
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $template_dir = __DIR__ . "/../views/";
        if (isset($config["templates"]["dir"])) {
            $template_dir = $config["templates"]["dir"];
        }
        
        $definition = new Definition('\Twig_Loader_Filesystem', array(
            $template_dir
        ));
        $container->setDefinition("behat.twig_output.twig.loader", $definition);
        
        $definition = new Definition('\Twig_Environment', array(
            new Reference("behat.twig_output.twig.loader"), array('debug' => true)
        ));
        $definition->addMethodCall("addExtension", array(
            new \Twig_Extension_Debug()
        ));
        $container->setDefinition("behat.twig_output.twig.templating", $definition);
        
        $definition = new Definition('chartinger\Behat\TwigReportExtension\EventListener', array(
            new Reference("behat.twig_output.twig.templating")
        ));
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, array());
        $definition->addMethodCall("setTemplate", array(
            $config["templates"]["file"]
        ));
        $definition->addMethodCall("setOutputDirectory", array(
            $config["output"]["dir"]
        ));
        $container->setDefinition("behat.twig_output.listener.event", $definition);
    }

    /*
     * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::initialize()
     */
    public function initialize(ExtensionManager $extensionManager)
    {}

    /*
     * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::configure()
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('templates')
            ->children()
            ->scalarNode("dir")
            ->defaultNull()
            ->end()
            ->scalarNode("file")
            ->defaultValue("default.twig")
            ->end()
            ->end()
            ->end()
            ->arrayNode('output')
            ->children()
            ->scalarNode("dir")
            ->isRequired()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }

    public function process(ContainerBuilder $container)
    {}
}