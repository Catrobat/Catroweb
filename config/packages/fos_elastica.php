<?php

declare(strict_types=1);

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'fos_elastica',
    [
      'clients' => [
        'default' => [
          'connections' => [
            [
              'url' => '%env(ELASTICSEARCH_URL)%',
            ],
            [
              'host' => '%es_host%',
            ],
            [
              'port' => '%es_port%',
            ],
          ],
        ],
      ],
      'indexes' => [
        'app_program' => [
          'settings' => [
            'analysis' => [
              'analyzer' => [
                'default' => [
                  'type' => 'custom',
                  'tokenizer' => 'standard',
                  'filter' => [
                    'lowercase',
                    'elision',
                    'language_stop',
                  ],
                ],
              ],
              'filter' => [
                'language_stop' => [
                  'type' => 'stop',
                  'ignore_case' => true,
                  'stopwords' => [
                    '_english_',
                  ],
                ],
              ],
            ],
          ],
          'properties' => [
            'description' => null,
            'flavor' => null,
            'id' => null,
            'language_version' => [
              'type' => 'float',
            ],
            'name' => [
              'boost' => 3,
            ],
            'private' => null,
            'visible' => null,
            'debug_build' => null,
            'getTagsString' => null,
            'getExtensionsString' => null,
            'getUsernameString' => null,
            'downloads' => [
              'type' => 'integer',
            ],
            'rand' => [
              'type' => 'integer',
            ],
            'popularity' => [
              'type' => 'float',
            ],
            'uploaded_at' => [
              'type' => 'date',
              'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time ||epoch_millis',
            ],
            'last_modified_at' => [
              'type' => 'date',
              'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time ||epoch_millis',
            ],
          ],
          'persistence' => [
            'driver' => 'orm',
            'model' => Project::class,
          ],
        ],
        'app_user' => [
          'settings' => [
            'analysis' => [
              'analyzer' => [
                'default' => [
                  'type' => 'custom',
                  'tokenizer' => 'standard',
                  'filter' => [
                    'lowercase',
                    'elision',
                    'language_stop',
                  ],
                ],
              ],
              'filter' => [
                'language_stop' => [
                  'type' => 'stop',
                  'ignore_case' => true,
                  'stopwords' => [
                    '_english_',
                  ],
                ],
              ],
            ],
          ],
          'properties' => [
            'id' => null,
            'username' => [
              'boost' => 3,
            ],
          ],
          'persistence' => [
            'driver' => 'orm',
            'model' => User::class,
          ],
        ],
      ],
    ]
  );
};
