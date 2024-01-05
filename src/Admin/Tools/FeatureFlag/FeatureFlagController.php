<?php

namespace App\Admin\Tools\FeatureFlag;

use App\DB\Entity\FeatureFlag;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<FeatureFlag>
 */
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
}
