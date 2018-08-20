<?php
namespace Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Ldap\UserHydrator;
use FR3D\LdapBundle\Driver\LdapDriverException;
use FR3D\LdapBundle\Ldap\LdapManager;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Monolog\Logger;
use FR3D\LdapBundle\Model\LdapUserInterface;

class UserLDAPManager extends LdapManager
{

    protected $role_mappings;

    protected $group_filter;

    protected $tokengenerator;

    protected $logger;

    public function __construct(LdapDriverInterface $driver, UserHydrator $userManager, array $params, $role_mappings, $group_filter, $tokengenerator, Logger $logger)
    {
        $this->role_mappings = $role_mappings;
        $this->group_filter = $group_filter;
        $this->logger = $logger;
        $this->tokengenerator = $tokengenerator;
        
        parent::__construct($driver, $userManager, $params);
    }

    public function findUserBy(array $criteria)
    {
        try {
            $filter = $this->buildFilter($criteria);
            $entries = $this->driver->search($this->params['baseDn'], $filter, $this->params['attributes']);
            if ($entries['count'] > 1) {
                throw new \Exception('This search can only return a single user');
            }
            
            if ($entries['count'] == 0) {
                return false;
            }
            
            // same Email-Address already in system?
            /**
             * @var UserManager $usermanager
             */
            $usermanager = $this->userManager;
            $sameEmailUser = $usermanager->findOneBy(array(
                "email" => $entries[0]['mail']
            ));
            if ($sameEmailUser != null) {
                if ($sameEmailUser instanceof LdapUserInterface) {
                    $sameEmailUser->setDn($entries[0]['dn']);
                }
                $usermanager->updateUser($sameEmailUser);
                return $sameEmailUser;
            }
            
            $user = $this->userManager->createUser();
            $this->hydrate($user, $entries[0]);
            
            return $user;
        } catch (LdapDriverException $e) {
            $this->logger->addError("LDAP-Server not reachable?: " . $e->getMessage());
            return false;
        }
    }

    public function bind(UserInterface $user, $password)
    {
         try {
            $filter = sprintf($this->group_filter, $user->getDn());
            $entries = $this->driver->search($this->params['baseDn'], $filter, array(
                "cn"
            ));
            $binding = $this->driver->bind($user, $password);
        } catch (LdapDriverException $e) {
            $this->logger->addError("LDAP-Server not reachable?: " . $e->getMessage());
            return false;
        }
        
        if ($binding) {
            /**
             * @var $user \Catrobat\AppBundle\Entity\User*
             */
            $user->setRealRoles(array());
            $user->setRoles(array());
            $roles = array();
            foreach ($entries as $entry) {
                $ldap_group_name = $entry["cn"][0];
                if ($role_to_add = array_search($ldap_group_name, $this->role_mappings)) {
                    array_push($roles, $role_to_add);
                }
            }
            $user->setRoles($roles);
        }
        return $binding;
    }

    protected function hydrate(UserInterface $user, array $entry)
    {
        parent::hydrate($user, $entry);
        $user->setUploadToken($this->tokengenerator->generateToken());
    }
}