<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\User;

use App\Api\Services\User\UserApiProcessor;
use App\DB\Entity\User\User;
use App\User\UserManager;
use OpenAPI\Server\Model\UpdateUserRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserApiProcessor::class)]
final class UserApiProcessorTest extends TestCase
{
  #[Group('unit')]
  public function testUpdateUserSanitizesProfileFields(): void
  {
    $user_manager = $this->createMock(UserManager::class);
    $user_manager->expects($this->once())->method('updateUser');

    $processor = new UserApiProcessor($user_manager);
    $user = new User();

    $request = $this->createStub(UpdateUserRequest::class);
    $request->method('getEmail')->willReturn(null);
    $request->method('getUsername')->willReturn(null);
    $request->method('getPassword')->willReturn(null);
    $request->method('getPicture')->willReturn(null);
    $request->method('getAbout')->willReturn('Contact me at kid@example.com');
    $request->method('getCurrentlyWorkingOn')->willReturn("f\u{200B}uck bugs");

    $processor->updateUser($user, $request);

    $this->assertSame('Contact me at [contact removed]', $user->getAbout());
    $this->assertSame('**** bugs', $user->getCurrentlyWorkingOn());
  }
}
