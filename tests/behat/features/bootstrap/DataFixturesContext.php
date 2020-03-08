<?php

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Extension;
use App\Entity\Program;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\ExtensionRepository;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Feature context.
 */
class DataFixturesContext implements KernelAwareContext
{
  /**
   * @var KernelInterface
   */
  private $kernel;

  /**
   * Sets HttpKernel instance.
   * This method will be automatically called by Symfony2Extension ContextInitializer.
   *
   * @param KernelInterface $kernel
   */
  public function setKernel(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * @var string
   */
  private $old_metadata_hash = "";

  /**
   * @BeforeScenario
   *
   * @throws ToolsException
   */
  public function clearData()
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $metaData = $em->getMetadataFactory()->getAllMetadata();
    $new_metadata_hash = md5(json_encode($metaData));
    if ($this->old_metadata_hash === $new_metadata_hash)
    {
      return;
    }
    $this->old_metadata_hash = $new_metadata_hash;
    $tool = new SchemaTool($em);
    $tool->dropSchema($metaData);
    $tool->createSchema($metaData);
  }

  /**
   * @Given /^there are users:$/
   *
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreUsers(TableNode $table)
  {
    /**
     * @var $user_manager UserManager
     * @var $user         User
     * @var $em           EntityManager
     */
    $user_manager = $this->kernel->getContainer()->get(UserManager::class);
    $users = $table->getHash();
    $number_of_users = count($users);
    for ($i = 0; $i < $number_of_users; $i++)
    {
      $user = $user_manager->createUser();
      $user->setUsername($users[$i]['name']);
      $user->setEmail(isset($users[$i]['email']) ? $users[$i]['email'] : $users[$i]['name'] . "@catrobat.at");
      $user->setPlainPassword(isset($users[$i]['password']) ? $users[$i]['password'] : '123456');
      $user->setUploadToken(isset($users[$i]['token']) ? $users[$i]['token'] : "default_token");
      $user->setSuperAdmin(isset($users[$i]['admin']) ? $users[$i]['admin'] === 'true' : false);
      $user->setAdditionalEmail('');
      $user->setEnabled(true);
      $user->setCountry('at');
      $user_manager->updateUser($user, true);

      if (array_key_exists('id', $users[$i]))
      {
        $user->setId($users[$i]['id']);
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $em->flush();
      }
    }
  }

  // Todo: add There are projects, notifications, etc...
}