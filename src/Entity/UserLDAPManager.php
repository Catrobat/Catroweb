<?php

namespace App\Entity;

use App\Catrobat\Ldap\UserHydrator;
use App\Catrobat\Services\TokenGenerator;
use Exception;
use FR3D\LdapBundle\Driver\LdapDriverException;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use FR3D\LdapBundle\Ldap\LdapManager;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserLDAPManager.
 */
class UserLDAPManager extends LdapManager
{
  /**
   * @var
   */
  protected $role_mappings;

  /**
   * @var
   */
  protected $group_filter;

  protected TokenGenerator $token_generator;

  protected Logger $logger;

  private UserManager $user_manager;

  /**
   * UserLDAPManager constructor.
   *
   * @param $role_mappings
   * @param $group_filter
   * @param $token_generator
   */
  public function __construct(LdapDriverInterface $driver, UserHydrator $user_hydrator, UserManager $user_manager,
                              array $params, $role_mappings, $group_filter, TokenGenerator $token_generator,
                              Logger $logger)
  {
    $this->role_mappings = $role_mappings;
    $this->group_filter = $group_filter;
    $this->logger = $logger;
    $this->token_generator = $token_generator;
    $this->user_manager = $user_manager;

    parent::__construct($driver, $user_hydrator, $params);
  }

  /**
   * @throws Exception
   *
   * @return bool|\FOS\UserBundle\Model\UserInterface|object|UserInterface|null
   */
  public function findUserBy(array $criteria): ?UserInterface
  {
    try
    {
      $filter = $this->buildFilter($criteria);
      $entries = $this->driver->search($this->params['baseDn'], $filter, $this->params['attributes']);
      if ($entries['count'] > 1)
      {
        throw new Exception('This search can only return a single user');
      }

      if (0 == $entries['count'])
      {
        return null;
      }

      // same Email-Address already in system?
      $same_email_user = $this->user_manager->findOneBy([
        'email' => $entries[0]['mail'],
      ]);
      if (null != $same_email_user)
      {
        if ($same_email_user instanceof LdapUserInterface)
        {
          $same_email_user->setDn($entries[0]['dn']);
        }
        $this->user_manager->updateUser($same_email_user);

        return $same_email_user;
      }

      return $this->hydrator->hydrate($entries[0]);
    }
    catch (LdapDriverException $e)
    {
      $this->logger->error('LDAP-Server not reachable?: '.$e->getMessage());

      return null;
    }
  }

  /**
   * @param $password
   */
  public function bind(UserInterface $user, $password): bool
  {
    try
    {
      $filter = sprintf($this->group_filter, $user->getDn());
      $entries = $this->driver->search($this->params['baseDn'], $filter, [
        'cn',
      ]);
      $binding = $this->driver->bind($user, $password);
    }
    catch (LdapDriverException $e)
    {
      $this->logger->error('LDAP-Server not reachable?: '.$e->getMessage());

      return false;
    }

    if ($binding)
    {
      $user->setRealRoles([]);
      $user->setRoles([]);
      $roles = [];
      foreach ($entries as $entry)
      {
        $ldap_group_name = $entry['cn'][0];
        if ($role_to_add = array_search($ldap_group_name, $this->role_mappings, true))
        {
          array_push($roles, $role_to_add);
        }
      }
      $user->setRoles($roles);
    }

    return $binding;
  }
}
