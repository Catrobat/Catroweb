<?php

declare(strict_types=1);

namespace App\Admin\System\FeatureFlag;

use App\DB\Entity\System\FeatureFlag;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<FeatureFlag>
 */
class FeatureFlagController extends CRUDController
{
  public function __construct(protected FeatureFlagManager $manager)
  {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    $this->manager->synchronizeDefaults();

    return parent::listAction($request);
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
