<?php

namespace App\Admin\Tools\FeatureFlag;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FeatureFlagController extends CRUDController
{
  public function __construct(protected FeatureFlagManager $manager)
  {
  }

  public function setFlagAction(): RedirectResponse
  {
    /* @var $object FeatureFlag */
    $object = $this->admin->getSubject();
    $this->manager->setFlagValue($object->getName(), $object->getValue());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function addFlagAction(): RedirectResponse
  {
    /* @var $object FeatureFlag */
    $object = $this->admin->getSubject();
    $this->manager->setFlagValue($object->getName(), $object->getValue());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function deleteFlagAction(): RedirectResponse
  {
    /* @var $object FeatureFlag */
    $object = $this->admin->getSubject();
    $this->manager->deleteFlag($object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
