<?php

namespace Catrobat\Behat\TwigReportExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;

/**
 * Class TwigReportExtension
 * @package Catrobat\Behat\TwigReportExtension\ServiceContainer
 */
class TwigReportExtension implements Extension
{

  /*
   * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::getConfigKey()
   */
  /**
   * @return string
   */
  public function getConfigKey()
  {
    return "twig_report";
  }

  /*
   * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::load()
   */
  /**
   * @param ContainerBuilder $container
   * @param array            $config
   */
  public function load(ContainerBuilder $container, array $config)
  {
    $template_dir = __DIR__ . "/../views/";
    if (isset($config["templates"]["dir"]))
    {
      $template_dir = $config["templates"]["dir"];
    }

    $definition = new Definition('\Twig_Loader_Filesystem', [
      $template_dir,
    ]);
    $container->setDefinition("behat.twig_output.twig.loader", $definition);

    $definition = new Definition('\Twig_Environment', [
      new Reference("behat.twig_output.twig.loader"), ['debug' => true, 'autoescape' => true],
    ]);
    $definition->addMethodCall("addExtension", [
      new \Twig_Extension_Debug(),
    ]);
    $container->setDefinition("behat.twig_output.twig.templating", $definition);

    $definition = new Definition('Catrobat\Behat\TwigReportExtension\EventListener', [
      new Reference("behat.twig_output.twig.templating"),
    ]);
    $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, []);
    $definition->addMethodCall("setTemplate", [
      $config["templates"]["file"],
    ]);
    $definition->addMethodCall("setIndexTemplate", [
      $config["templates"]["index"],
    ]);
    $definition->addMethodCall("setIndexFilename", [
      $config["output"]["index"],
    ]);
    $definition->addMethodCall("setOutputDirectory", [
      $config["output"]["dir"],
    ]);
    $definition->addMethodCall("setExtension", [
      $config["output"]["extension"],
    ]);
    $definition->addMethodCall("setScope", [
      $config["output"]["scope"],
    ]);
    $container->setDefinition("behat.twig_output.listener.event", $definition);
  }

  /*
   * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::initialize()
   */
  /**
   * @param ExtensionManager $extensionManager
   */
  public function initialize(ExtensionManager $extensionManager)
  {
  }

  /*
   * (non-PHPdoc) @see \Behat\Testwork\ServiceContainer\Extension::configure()
   */
  /**
   * @param ArrayNodeDefinition $builder
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
      ->scalarNode("index")
      ->end()
      ->end()
      ->end()
      ->arrayNode('output')
      ->children()
      ->scalarNode("dir")
      ->isRequired()
      ->end()
      ->scalarNode("extension")
      ->defaultValue("html")
      ->end()
      ->scalarNode("scope")
      ->defaultValue("suite")
      ->end()
      ->scalarNode("index")
      ->defaultValue("index")
      ->end()
      ->end()
      ->end()
      ->end()
      ->end();
  }

  /**
   * @param ContainerBuilder $container
   */
  public function process(ContainerBuilder $container)
  {
  }
}