<?php
/**
 * Created by IntelliJ IDEA.
 * User: catroweb
 * Date: 18.08.15
 * Time: 16:57
 */

namespace Catrobat\AppBundle\Services\TestEnv;


use FR3D\LdapBundle\Driver\LdapDriverException;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LdapTestDriver implements LdapDriverInterface
{
  protected $objects;
  private $baseDN;

  private static $APC_OBJECTS  = "LdapTestDriverFixture";


  public function __construct($baseDN)
  {
    $this->baseDN = $baseDN;
  }
  /**
   * Bind to LDAP directory.
   *
   * @param UserInterface $user The user for authenticating the bind.
   * @param string $password The password for authenticating the bind.
   *
   * @return bool true on success or false on failure
   */
  public function bind(UserInterface $user, $password)
  {
    $this->loadFixtures();
    if($user instanceof LdapUserInterface)
    {
      foreach($this->objects as $object)
      {
        if($object["ObjectClass"] == "person" &&
          $user->getDn() == $object["dn"] &&
          $object["password"] == $password) {
          return true;
        }
      }
    }

    foreach($this->objects as $object)
    {
      if($object["ObjectClass"] == "person" &&
        in_array($user->getUsername(),$object["cn"]) &&
        $object["password"] == $password) {
        return true;
      }
    }
    return false;
  }

  /**
   * Search LDAP tree.
   *
   * @param  string $baseDn The base DN for the directory.
   * @param  string $filter The search filter.
   * @param  array $attributes The array of the required attributes,
   *                                   'dn' is always returned. If array is
   *                                   empty then will return all attributes
   *                                   and their associated values.
   *
   * @return array|bool Returns a complete result information in a
   *                       multi-dimensional array on success and FALSE on error.
   *                       see {@link http://www.php.net/function.ldap-get-entries.php}
   *                       for array format examples.
   *
   * @throws LdapDriverException if some error occurs.
   */
  public function search($baseDn, $filter, array $attributes = array())
  {
    //load users
    $this->loadFixtures();

    $searchRequirements = $this->extractKeys($filter);
    $result = array();
    foreach($this->objects as $object)
    {
      $isOk = true;
      foreach($searchRequirements as $reqKey => $reqVal)
      {
        if(is_array($object[$reqKey]) && in_array($reqVal,$object[$reqKey]))
        {
          continue;
        }
        else if($object[$reqKey]!=$reqVal)
        {
          $isOk = false;
          break;
        }

      }
      if($isOk)
      {
        $object_entity = array();
        foreach($attributes as $at)
          $object_entity[$at]=$object[$at];

        $object_entity["dn"] = $object["dn"];

        array_push($result,$object_entity);
      }
    }

    $result["count"] = count($result);

    return $result;
  }

  public function resetFixtures()
  {
    return apc_delete($this::$APC_OBJECTS);
  }

  public function addTestUser($username,$password,$groups=array(),$mail = null)
  {
    $this->loadFixtures();
    foreach($groups as $group)
    {
      $key = null;
      foreach($this->objects as $existing_group)
      {
        if($existing_group["ObjectClass"]!="groupOfUniqueNames")
          continue;

        if(in_array($group,$existing_group["cn"])) {
          array_push($existing_group["uniqueMember"],"cn=".strtolower($username).",".$this->baseDN);
          break;
        }
      }
      if($key == null)
      {
        $group_entity = [
          "ObjectClass"=>"groupOfUniqueNames",
          "dn"=>"cn=".$group.", ".$this->baseDN,
          "cn"=>array($group),
          "uniqueMember"=>array("cn=".strtolower($username).",".$this->baseDN)
        ];
        array_push($this->objects,$group_entity);
      }
    }

    $user_entity = [
      "ObjectClass"=>"person",
      "dn"=>"cn=".$username.",".$this->baseDN,
      "cn"=>array($username),
      "password"=>$password,
      "mail"=>$mail!=null?$mail:array($username."@generated.at")
    ];
    array_push($this->objects,$user_entity);

    return apc_store($this::$APC_OBJECTS,$this->objects);
  }

  private function extractKeys($string)
  {
    $matches = null;
    $result = array();
    if(preg_match_all('~\((\\w*=.*?)\)~', $string, $matches))
    {
      foreach($matches[1] as $match)
      {
        $array = explode("=",$match,2);
        $result[$array[0]]=$array[1];
      }
    }
    return $result;
  }

  private function loadFixtures()
  {
    if(!is_array($this->objects)) {
      $this->objects = array();
      if(apc_fetch($this::$APC_OBJECTS)!=false)
        $this->objects = apc_fetch($this::$APC_OBJECTS);
    }
  }
}