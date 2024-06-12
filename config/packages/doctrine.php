<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\JsonType;
use DoctrineExtensions\Query\Mysql\MatchAgainst;
use DoctrineExtensions\Query\Mysql\Rand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'doctrine',
    [
      'dbal' => [
        'url' => '%env(resolve:DATABASE_URL)%',
        'server_version' => '10.11.7-MariaDB',
        'types' => ['json' => JsonType::class],
        'mapping_types' => ['enum' => 'string'],
      ],
      'orm' => [
        'auto_generate_proxy_classes' => '%kernel.debug%',
        'auto_mapping' => true,
        'mappings' => [
          'App' => [
            'is_bundle' => false,
            'type' => 'attribute',
            'dir' => '%kernel.project_dir%/src/DB/Entity',
            'prefix' => 'App\DB\Entity', 'alias' => 'App',
          ],
        ],
        'dql' => [
          'string_functions' => [
            'match' => MatchAgainst::class,
            'rand' => Rand::class,
          ],
        ],
      ],
    ]
  );
};
