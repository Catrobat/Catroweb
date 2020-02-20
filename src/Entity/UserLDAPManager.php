<?php

namespace App\Entity;

use App\Catrobat\Ldap\UserHydrator;
use FR3D\LdapBundle\Driver\LdapDriverException;
use FR3D\LdapBundle\Ldap\LdapManager;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Monolog\Logger;
use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * Class UserLDAPManager
 * @package App\Entity
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

  /**
   * @var
   */
  protected $tokengenerator;

  /**
   * @var Logger
   */
  protected $logger;

  /**
   * UserLDAPManager constructor.
   *
   * @param LdapDriverInterface $driver
   * @param UserHydrator        $user_manager
   * @param array               $params
   * @param                     $role_mappings
   * @param                     $group_filter
   * @param                     $tokengenerator
   * @param Logger              $logger
   */
  public function __construct(LdapDriverInterface $driver, UserHydrator $user_manager,
                              array $params, $role_mappings, $group_filter, $tokengenerator, Logger $logger)
  {
    $this->role_mappings = $role_mappings;
    $this->group_filter = $group_filter;
    $this->logger = $logger;
    $this->tokengenerator = $tokengenerator;

    parent::__construct($driver, $user_manager, $params);
  }

  /**
   * @param array $criteria
   *
   * @return bool|\FOS\UserBundle\Model\UserInterface|object|UserInterface|null
   * @throws \Exception
   */
  public function findUserBy(array $criteria): ?UserInterface
  {
    try
    {
      $filter = $this->buildFilter($criteria);
      $entries = $this->driver->search($this->params['baseDn'], $filter, $this->params['attributes']);
      if ($entries['count'] > 1)
      {
        throw new \Exception('This search can only return a single user');
      }

      if ($entries['count'] == 0)
      {
        return null;
      }

      // same Email-Address already in system?
      /**
       * @var UserManager $usermanager
       */
      $usermanager = $this->user_manager;
      $sameEmailUser = $usermanager->findOneBy([
        "email" => $entries[0]['mail'],
      ]);
      if ($sameEmailUser != null)
      {
        if ($sameEmailUser instanceof LdapUserInterface)
        {
          $sameEmailUser->setDn($entries[0]['dn']);
        }
        $usermanager->updateUser($sameEmailUser);

        return $sameEmailUser;
      }

      $user = $this->user_manager->createUser();
      $this->hydrate($user, $entries[0]);

      return $user;
    } catch (LdapDriverException $e)
    {
      $this->logger->error("LDAP-Server not reachable?: " . $e->getMessage());

      return null;
    }
  }

  /**
   * @param UserInterface $user
   * @param               $password
   *
   * @return bool
   */
  public function bind(UserInterface $user, $password): bool
  {
    try
    {
      $filter = sprintf($this->group_filter, $user->getDn());
      $entries = $this->driver->search($this->params['baseDn'], $filter, [
        "cn",
      ]);
      $binding = $this->driver->bind($user, $password);
    } catch (LdapDriverException $e)
    {
      $this->logger->error("LDAP-Server not reachable?: " . $e->getMessage());

      return false;
    }

    if ($binding)
    {
      /**
       * @var $user \App\Entity\User*
       */
      $user->setRealRoles([]);
      $user->setRoles([]);
      $roles = [];
      foreach ($entries as $entry)
      {
        $ldap_group_name = $entry["cn"][0];
        if ($role_to_add = array_search($ldap_group_name, $this->role_mappings))
        {
          array_push($roles, $role_to_add);
        }
      }
      $user->setRoles($roles);
    }

    return $binding;
  }

  /**
   * @param UserInterface $user
   * @param array         $entry
   */
  protected function hydrate(UserInterface $user, array $entry)
  {
    parent::hydrate($user, $entry);
    $user->setUploadToken($this->tokengenerator->generateToken());
  }
}