<?php

namespace App\Catrobat\Services\TestEnv;

use FR3D\LdapBundle\Driver\LdapDriverException;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class LdapTestDriver.
 */
class LdapTestDriver implements LdapDriverInterface
{
  /**
   * @var
   */
  protected $objects;

  /**
   * @var
   */
  private $baseDN;

  /**
   * @var bool
   */
  private $throw_expection_on_search;

  /**
   * @var string
   */
  private static $APC_OBJECTS = 'LdapTestDriverFixture';

  /**
   * LdapTestDriver constructor.
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    $this->baseDN = $parameter_bag->get('ldap_base_dn');
    $this->throw_expection_on_search = false;
  }

  /**
   * Bind to LDAP directory.
   *
   * @param userInterface $user
   *                                The user for authenticating the bind
   * @param string        $password
   *                                The password for authenticating the bind
   *
   * @return bool true on success or false on failure
   */
  public function bind(UserInterface $user, $password): bool
  {
    $this->loadFixtures();
    if ($user instanceof LdapUserInterface)
    {
      foreach ($this->objects as $object)
      {
        if ('person' == $object['ObjectClass'] && $user->getDn() == $object['dn'] && $object['password'] == $password)
        {
          return true;
        }
      }
    }

    foreach ($this->objects as $object)
    {
      if ('person' == $object['ObjectClass'] && in_array($user->getUsername(), $object['cn'], true) && $object['password'] == $password)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Search LDAP tree.
   *
   * @param string $baseDn
   *                           The base DN for the directory
   * @param string $filter
   *                           The search filter
   * @param array  $attributes
   *                           The array of the required attributes,
   *                           'dn' is always returned. If array is
   *                           empty then will return all attributes
   *                           and their associated values.
   *
   * @throws LdapDriverException if some error occurs
   *
   * @return array|bool Returns a complete result information in a
   *                    multi-dimensional array on success and FALSE on error.
   *                    see {@link http://www.php.net/function.ldap-get-entries.php}
   *                    for array format examples.
   */
  public function search($baseDn, $filter, array $attributes = [])
  {
    if ($this->throw_expection_on_search)
    {
      throw new LdapDriverException('Test Exception on Search!');
    }
    // load users
    $this->loadFixtures();

    $searchRequirements = $this->extractKeys($filter);
    $result = [];
    foreach ($this->objects as $object)
    {
      $isOk = true;
      foreach ($searchRequirements as $reqKey => $reqVal)
      {
        var_export("\nKey: ".$reqKey."\nVal: ".$reqVal);
        if (is_array($object[$reqKey]) && in_array($reqVal, $object[$reqKey], true))
        {
          continue;
        }

        if ($object[$reqKey] != $reqVal)
        {
          $isOk = false;
          break;
        }
      }
      if ($isOk)
      {
        $object_entity = [];
        foreach ($attributes as $at)
        {
          $object_entity[$at] = $object[$at];
        }

        $object_entity['dn'] = $object['dn'];

        array_push($result, $object_entity);
      }
    }

    $result['count'] = count($result);

    return $result;
  }

  /**
   * @return bool
   */
  public function resetFixtures()
  {
    return ApcReplace::Instance()->apc_delete($this::$APC_OBJECTS);
  }

  /**
   * @param       $username
   * @param       $password
   * @param array $groups
   * @param null  $mail
   *
   * @return bool
   */
  public function addTestUser($username, $password, $groups = [], $mail = null)
  {
    $this->loadFixtures();
    foreach ($groups as $group)
    {
      $key = null;
      foreach ($this->objects as $existing_group)
      {
        if ('groupOfUniqueNames' != $existing_group['ObjectClass'])
        {
          continue;
        }

        if (in_array($group, $existing_group['cn'], true))
        {
          array_push($existing_group['uniqueMember'], 'cn='.strtolower($username).','.$this->baseDN);
          break;
        }
      }
      if (null == $key)
      {
        $group_entity = [
          'ObjectClass' => 'groupOfUniqueNames',
          'dn' => 'cn='.strtolower($group).', '.$this->baseDN,
          'cn' => [
            $group,
          ],
          'uniqueMember' => [
            'cn='.strtolower($username).','.$this->baseDN,
          ],
        ];
        array_push($this->objects, $group_entity);
      }
    }

    $user_entity = [
      'ObjectClass' => 'person',
      'dn' => 'cn='.strtolower($username).','.$this->baseDN,
      'cn' => [
        $username,
      ],
      'password' => $password,
      'mail' => null != $mail ? $mail : [
        $username.'@generated.at',
      ],
    ];

    array_push($this->objects, $user_entity);

    return ApcReplace::Instance()->apc_store($this::$APC_OBJECTS, $this->objects);
  }

  /**
   * @param $value
   */
  public function setThrowExceptionOnSearch($value)
  {
    $this->throw_expection_on_search = $value;
  }

  /**
   * @param $string
   *
   * @return array
   */
  private function extractKeys($string)
  {
    $matches = null;
    $result = [];
    if (preg_match_all('~\((\\w*=.*?)\)~', $string, $matches))
    {
      foreach ($matches[1] as $match)
      {
        $array = explode('=', $match, 2);
        $result[$array[0]] = $array[1];
      }
    }

    return $result;
  }

  private function loadFixtures()
  {
    if (!is_array($this->objects))
    {
      $this->objects = [];
      if (false != ApcReplace::Instance()->apc_fetch($this::$APC_OBJECTS))
      {
        $this->objects = ApcReplace::Instance()->apc_fetch($this::$APC_OBJECTS);
      }
    }
  }
}
