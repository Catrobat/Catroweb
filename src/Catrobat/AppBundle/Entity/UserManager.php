<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Sonata\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

class UserManager extends \Sonata\UserBundle\Entity\UserManager
{
    public function __construct(PasswordUpdaterInterface $passwordUpdater,
                                CanonicalFieldsUpdater $canonicalFieldsUpdater,
                                ObjectManager $om, $class)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
    }
}
