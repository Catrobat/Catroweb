<?php

namespace App\Catrobat\Ldap;

use FR3D\LdapBundle\Hydrator\HydratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;


/**
 * Class UserHydrator
 * @package App\Catrobat\Ldap
 */
class UserHydrator implements HydratorInterface
{

  /**
   * Populate an user with the data retrieved from LDAP.
   *
   * @param array $ldapEntry LDAP result information as a multi-dimensional array.
   *                         see {@link http://www.php.net/function.ldap-get-entries.php} for array format examples.
   *
   * @return UserInterface
   */
  public function hydrate(array $ldapEntry): UserInterface
  {
    $user = new User();
    $user->setUsername($ldapEntry['cn'][0]);
    $user->setEmail($ldapEntry['email'][0]);

    return $user;
  }
}