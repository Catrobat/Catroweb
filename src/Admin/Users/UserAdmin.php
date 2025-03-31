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

  #[\Override]
  protected function configureFormOptions(array &$formOptions): void
  {
    $formOptions['validation_groups'] = ['Default'];

    if (!$this->hasSubject() || null === $this->getSubject()->getId()) {
      $formOptions['validation_groups'][] = 'Registration';
    } else {
      $formOptions['validation_groups'][] = 'Profile';
    }
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('username')
      ->add('email')
      ->add('enabled', null, ['editable' => true])
      ->add('verified', null, ['editable' => true])
      ->add('createdAt')
      ->add('lastLogin')
    ;

    $list->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
      'translation_domain' => 'SonataAdminBundle',
      'actions' => [
        'edit' => [],
      ],
    ]);
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('username')
      ->add('email')
      ->add('verified')
      ->add('createdAt')
      ->add('lastLogin')
    ;
  }

  #[\Override]
  protected function configureShowFields(ShowMapper $show): void
  {
    $show
      ->add('username')
      ->add('email')
      ->add('verified')
    ;
  }

  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->with('general', ['class' => 'col-md-4'])
      ->add('username')
      ->add('email')
      ->add('verified')
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

  #[\Override]
  protected function configureExportFields(): array
  {
    // Avoid sensitive properties to be exported.
    return array_filter(
      parent::configureExportFields(),
      static fn (string $v): bool => !\in_array($v, ['password', 'salt'], true)
    );
  }
}
