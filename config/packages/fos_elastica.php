<?php

declare(strict_types=1);

use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'fos_elastica',
    [
      'clients' => [
        'default' => [
          'hosts' => [
            '%env(ELASTICSEARCH_URL)%',
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
            'name' => null,
            'private' => null,
            'visible' => null,
            'debug_build' => null,
            'auto_hidden' => null,
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
            'model' => Program::class,
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
            'username' => null,
            'profile_hidden' => null,
            'verified' => null,
          ],
          'persistence' => [
            'driver' => 'orm',
            'model' => User::class,
          ],
        ],
        'app_studio' => [
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
            'name' => null,
            'description' => null,
            'is_public' => [
              'type' => 'boolean',
            ],
            'auto_hidden' => [
              'type' => 'boolean',
            ],
            'is_enabled' => [
              'type' => 'boolean',
            ],
          ],
          'persistence' => [
            'driver' => 'orm',
            'model' => Studio::class,
          ],
        ],
      ],
    ]
  );
};
