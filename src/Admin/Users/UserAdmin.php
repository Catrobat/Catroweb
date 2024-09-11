<?php

declare(strict_types=1);

namespace App\Admin\Users;

use App\DB\Entity\User\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * @phpstan-extends AbstractAdmin<User>
 */
class UserAdmin extends AbstractAdmin
{
  protected $classnameLabel = 'user';

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'user';
  }

  protected function configureFormOptions(array &$formOptions): void
  {
    $formOptions['validation_groups'] = ['Default'];

    if (!$this->hasSubject() || null === $this->getSubject()->getId()) {
      $formOptions['validation_groups'][] = 'Registration';
    } else {
      $formOptions['validation_groups'][] = 'Profile';
    }
  }

  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('username')
      ->add('email')
      ->add('enabled', null, ['editable' => true])
      ->add('createdAt')
    ;

    //    if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
    //      $list
    //        ->add('impersonating', FieldDescriptionInterface::TYPE_STRING, [
    //          'virtual_field' => true,
    //          'template' => '@SonataUser/Admin/Field/impersonating.html.twig',
    //        ])

    //    }

    $list->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
      'translation_domain' => 'SonataAdminBundle',
      'actions' => [
        'edit' => [],
      ],
    ]);
  }

  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('username')
      ->add('email')
    ;
  }

  protected function configureShowFields(ShowMapper $show): void
  {
    $show
      ->add('username')
      ->add('email')
    ;
  }

  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->with('general', ['class' => 'col-md-4'])
      ->add('username')
      ->add('email')
      ->add('plainPassword', PasswordType::class, [
        'required' => (!$this->hasSubject() || null === $this->getSubject()->getId()),
      ])
      ->add('enabled')
      ->end()
      ->with('roles', ['class' => 'col-md-8'])
      ->add('realRoles', RolesMatrixType::class, [
        'label' => false,
        'multiple' => true,
        'required' => false,
      ])
      ->end()
    ;
  }

  protected function configureExportFields(): array
  {
    // Avoid sensitive properties to be exported.
    return array_filter(
      parent::configureExportFields(),
      static fn (string $v): bool => !\in_array($v, ['password', 'salt'], true)
    );
  }
}
