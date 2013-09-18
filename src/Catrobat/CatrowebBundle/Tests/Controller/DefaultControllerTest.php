<?php

namespace Catrobat\CatrowebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
         $client = static::createClient();

         $crawler = $client->request('GET', '/');

         $this->assertTrue($crawler->filter('html:contains("Pocket Code")')->count() > 0);
    }
}
