<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 2/23/17
 * Time: 4:20 PM
 */

namespace Catrobat\AppBundle\Security;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * UserChecker checks the user account flags.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UserChecker extends BaseUserChecker
{
    protected $entityManager;
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * Unbans user after banned_until was surpassed.
     * Doesn't unban users that have been locked without setting banned_until
     */
    public function checkPreAuth(UserInterface $user)
    {
        //do your custom preauth stuff here
        $real_user = $this->entityManager->getRepository('AppBundle:User')->findOneBy(['username' => $user->getUsername()]);
        $banned_until = $real_user->getBannedUntil();
        $current_time = new \DateTime('now');

        if ($real_user->isLocked() AND
            $banned_until !== null)
        {
            if ($current_time > $banned_until)
            {
                $real_user->setLocked(false);
                $real_user->setBannedUntil(null);
                $this->entityManager->flush();
            }
        }
        parent::checkPreAuth($user);
    }

}