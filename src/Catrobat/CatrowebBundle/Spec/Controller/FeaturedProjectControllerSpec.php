<?php

namespace Catrobat\CatrowebBundle\Spec\Controller;

use PhpSpec\Symfony2Extension\Specification\ControllerBehavior;
use Prophecy\Argument;

class FeaturedProjectControllerSpec extends ControllerBehavior
{
    function it_is_container_aware()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\ContainerAwareInterface');
    }
    
    function it_is_initializable()
    {
      $this->shouldHaveType('Catrobat\CatrowebBundle\Controller\FeaturedProjectController');
    }  
    
}
