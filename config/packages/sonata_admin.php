<?php

declare(strict_types=1);

use App\DB\Entity\User\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
  $containerConfigurator->extension(
    'sonata_block',
    [
      'http_cache' => false,
      'default_contexts' => [
        'cms',
      ],
      'blocks' => [
        'sonata.admin.block.stats' => [
          'contexts' => [
            'admin',
          ],
        ],
        'sonata.admin.block.admin_list' => [
          'contexts' => [
            'admin',
          ],
        ],
        'sonata.admin.block.search_result' => [
          'contexts' => [
            'admin',
          ],
        ],
        'sonata.block.service.text' => null,
        'sonata.user.block.menu' => null,
        'sonata.user.block.account' => null,
      ],
    ]
  );

  $containerConfigurator->extension(
    'sonata_admin',
    [
      'title' => 'Admin Panel',
      'title_logo' => 'images/admin/admin_logo.png',
      'show_mosaic_button' => false,
      'templates' => [
        'layout' => 'Admin\standard_layout.html.twig',
      ],
      'dashboard' => [
        'blocks' => [
          [
            'position' => 'center',
            'type' => 'sonata.block.service.text',
            'settings' => [
              'content' => '<h2>Welcome to the Admin Panel!</h2> According to your role you can access different information and tools. <br><br>',
            ],
          ],
          [
            'class' => 'col-lg-3 col-xs-6',
            'position' => 'bottom',
            'type' => 'sonata.admin.block.stats',
            'settings' => [
              'code' => 'admin.block.projects.overview',
              'icon' => 'fas fa-cubes',
              'text' => 'All projects',
              'color' => 'bg-blue',
            ],
          ],
          [
            'class' => 'col-lg-3 col-xs-6',
            'position' => 'bottom',
            'type' => 'sonata.admin.block.stats',
            'settings' => [
              'code' => 'admin.block.users.overview',
              'icon' => 'fas fa-users',
              'text' => 'User',
              'color' => 'bg-green',
            ],
          ],
        ],
        'groups' => [
          'sonata.admin.group.programs' => [
            'label' => 'Projects',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-cubes"></i>',
            'items' => [
              'admin.block.projects.overview',
              'admin.block.projects.approve',
              'admin.block.projects.reported',
            ],
          ],
          'sonata.admin.group.users' => [
            'label' => 'Users',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-users"></i>',
            'items' => [
              'admin.block.users.overview',
              'admin.block.users.data_report',
              'admin.block.users.reported',
            ],
          ],
          'sonata.admin.group.comments' => [
            'label' => 'Comments',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-comments"></i>',
            'items' => [
              'admin.block.comments.overview',
              'admin.block.comments.reported',
            ],
          ],
          'sonata.admin.group.featured' => [
            'label' => 'Special Projects',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-bullhorn"></i>',
            'items' => [
              'admin.block.featured.program',
              'admin.block.example.program',
            ],
          ],
          'sonata.admin.group.mediapackage' => [
            'label' => 'Media Package',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-newspaper-o"></i>',
            'items' => [
              'admin.block.mediapackage.package',
              'admin.block.mediapackage.category',
              'admin.block.mediapackage.file',
            ],
          ],
          'sonata.admin.group.apk' => [
            'label' => 'Apk Generation',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-android"></i>',
            'items' => [
              'admin.block.apk.pending',
              'admin.block.apk.list',
            ],
          ],
          'sonata.admin.group.survey' => [
            'label' => 'Survey',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-bar-chart"></i>',
            'items' => [
              'admin.block.survey',
            ],
          ],
          'sonata.admin.group.db_updater' => [
            'label' => 'DB Updater',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-cogs"></i>',
            'items' => [
              'admin.block.cron_jobs',
              'admin.block.special_updater',
              'admin.block.achievements',
              'admin.block.extensions',
              'admin.block.tags',
            ],
          ],
          'sonata.admin.group.tools' => [
            'label' => 'Tools',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-cogs"></i>',
            'items' => [
              'admin.block.tools.maintain',
              'admin.block.tools.logs',
              'admin.block.tools.broadcast',
              'admin.block.tools.mail',
              'admin.block.tools.feature_flag',
              'admin.block.tools.maintenance_information',
            ],
          ],
          'sonata.admin.group.statistics' => [
            'label' => 'Statistics',
            'translation_domain' => 'catroweb',
            'icon' => '<i class="fa fa-bar-chart"></i>',
            'items' => [
              'admin.block.statistics.project_machine_translation',
              'admin.block.statistics.project_custom_translation',
              'admin.block.statistics.comment_machine_translation',
            ],
          ],
        ],
      ],
      'security' => [
        'handler' => 'sonata.admin.security.handler.acl',
        'role_admin' => 'ROLE_ADMIN',
        'role_super_admin' => 'ROLE_SUPER_ADMIN',
        'information' => [
          'GUEST' => [
            'VIEW',
            'LIST',
          ],
          'STAFF' => [
            'EDIT',
            'LIST',
            'CREATE',
          ],
          'EDITOR' => [
            'OPERATOR',
            'EXPORT',
          ],
          'ADMIN' => [
            'MASTER',
          ],
        ],
        'admin_permissions' => [
          'CREATE',
          'LIST',
          'DELETE',
          'UNDELETE',
          'EXPORT',
          'OPERATOR',
          'MASTER',
        ],
        'object_permissions' => [
          'VIEW',
          'EDIT',
          'DELETE',
          'UNDELETE',
          'OPERATOR',
          'MASTER',
          'OWNER',
        ],
      ],
    ]
  );

  $containerConfigurator->extension(
    'sonata_doctrine_orm_admin', [
      'entity_manager' => null,
      'templates' => [
        'types' => [
          'list' => [
            'array' => '@SonataAdmin/CRUD/list_array.html.twig',
            'boolean' => '@SonataAdmin/CRUD/list_boolean.html.twig',
            'date' => '@SonataAdmin/CRUD/list_date.html.twig',
            'time' => '@SonataAdmin/CRUD/list_time.html.twig',
            'datetime' => '@SonataAdmin/CRUD/list_datetime.html.twig',
            'text' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'trans' => '@SonataAdmin/CRUD/list_trans.html.twig',
            'string' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'smallint' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'bigint' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'integer' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'decimal' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'identifier' => '@SonataAdmin/CRUD/base_list_field.html.twig',
          ],
          'show' => [
            'array' => '@SonataAdmin/CRUD/show_array.html.twig',
            'boolean' => '@SonataAdmin/CRUD/show_boolean.html.twig',
            'date' => '@SonataAdmin/CRUD/show_date.html.twig',
            'time' => '@SonataAdmin/CRUD/show_time.html.twig',
            'datetime' => '@SonataAdmin/CRUD/show_datetime.html.twig',
            'text' => '@SonataAdmin/CRUD/base_show_field.html.twig',
            'trans' => '@SonataAdmin/CRUD/show_trans.html.twig',
            'string' => '@SonataAdmin/CRUD/base_show_field.html.twig',
            'smallint' => '@SonataAdmin/CRUD/base_show_field.html.twig',
            'bigint' => '@SonataAdmin/CRUD/base_show_field.html.twig',
            'integer' => '@SonataAdmin/CRUD/base_show_field.html.twig',
            'decimal' => '@SonataAdmin/CRUD/base_show_field.html.twig',
          ],
        ],
      ],
    ]
  );

  $containerConfigurator->extension(
    'sonata_user',
    [
      'security_acl' => true,
      'manager_type' => 'orm',
      'mailer' => 'sonata.user.mailer.default',
      'class' => [
        'user' => User::class,
      ],
      'resetting' => [
        'email' => [
          'template' => '@SonataUser/Admin/Security/Resetting/email.html.twig',
          'address' => 'support@catrob.at',
          'sender_name' => 'Catrobat Team',
        ],
      ],
    ]
  );
};
