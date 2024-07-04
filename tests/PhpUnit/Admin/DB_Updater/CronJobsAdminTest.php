<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\DB_Updater;

use App\Admin\System\CronJobs\CronJobsAdmin;
use App\System\Testing\PhpUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * @internal
 *
 * @coversNothing
 */
class CronJobsAdminTest extends DefaultTestCase
{
  protected CronJobsAdmin|MockObject $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(CronJobsAdmin::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(CronJobsAdmin::class));
    $this->assertInstanceOf(CronJobsAdmin::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractAdmin::class, $this->object);
  }
}
