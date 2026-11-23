<?php

declare(strict_types=1);

use App\DB\Entity\Docs\DocPage;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use App\Docs\Search\DocsEmbedder;
use App\Docs\Search\DocsSearchIndexer;
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
            'has_missing_files' => ['type' => 'boolean'],
            'getTagsString' => [
              'type' => 'text',
            ],
            'getExtensionsString' => [
              'type' => 'text',
            ],
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
            'username' => null,
            'profile_hidden' => null,
            'verified' => null,
            'enabled' => null,
            'consent_status' => ['type' => 'keyword'],
          ],
          'persistence' => [
            'driver' => 'orm',
            'model' => User::class,
          ],
        ],
        // The user docs search (docs/operations/User-Docs.md, "Search"): guides and bricks in every locale. Documents are
        // built by DocsSearchIndexer (after every docs sync, and by fos:elastica:populate), not from DocPage rows directly.
        'app_doc' => [
          'settings' => [
            // The standard analyzer: the docs come in 70 languages, an English stopword list would only hurt.
            'analysis' => ['analyzer' => ['default' => ['type' => 'standard']]],
          ],
          'properties' => [
            'type' => ['type' => 'keyword'],
            'path' => ['type' => 'keyword'],
            'locale' => ['type' => 'keyword'],
            'title' => ['type' => 'text'],
            'text' => ['type' => 'text'],
            'snippet' => ['type' => 'text', 'index' => false],
            'embedding' => ['type' => 'dense_vector', 'dims' => DocsEmbedder::DIMS, 'index' => true, 'similarity' => 'cosine'],
          ],
          'persistence' => [
            'driver' => 'orm',
            'model' => DocPage::class,
            'listener' => ['enabled' => false],
            'provider' => ['service' => DocsSearchIndexer::class],
            'model_to_elastica_transformer' => ['service' => DocsSearchIndexer::class],
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
