<?php

namespace App\Catrobat\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MaintainController.
 */
class MaintainController extends Controller
{
  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function extractedAction(KernelInterface $kernel)
  {
    if (false === $this->admin->isGranted('EXTRACTED'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:extracted',
    ]);

    $return = $application->run($input, new NullOutput());
    if (0 == $return)
    {
      $this->addFlash('sonata_flash_success', 'Reset extracted files OK');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function archiveLogsAction(KernelInterface $kernel)
  {
    if (false === $this->admin->isGranted('EXTRACTED'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:logs:archive',
    ]);

    $return = $application->run($input, new NullOutput());
    if (0 == $return)
    {
      $this->addFlash('sonata_flash_success', 'Archive log files OK');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function deleteLogsAction(KernelInterface $kernel)
  {
    if (false === $this->admin->isGranted('EXTRACTED'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:logs',
    ]);

    $output = new BufferedOutput();

    $return = $application->run($input, $output);

    if (0 === $return)
    {
      $this->addFlash('sonata_flash_success', 'Clean log files OK');
    }
    else
    {
      $message = "<strong>Failed cleaning log files:</strong><br />\n";
      $message .= str_replace("\n", "<br />\n", $output->fetch());
      $this->addFlash('sonata_flash_error', $message);
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function apkAction(KernelInterface $kernel)
  {
    if (false === $this->admin->isGranted('APK'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:apk',
    ]);

    $output = new NullOutput();

    $return = $application->run($input, $output);

    if (0 == $return)
    {
      $this->addFlash('sonata_flash_success', 'Reset APK Projects OK');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function deleteBackupsAction(KernelInterface $kernel, Request $request = null)
  {
    if (false === $this->admin->isGranted('BACKUP'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:backup',
      '--all' => $request->get('--all'),
    ]);

    if ($request->get('backupFile'))
    {
      $input = new ArrayInput([
        'command' => 'catrobat:clean:backup',
        'backupfile' => $request->get('backupFile'),
      ]);
    }

    $output = new NullOutput();

    try
    {
      $return = $application->run($input, $output);
      if (0 == $return)
      {
        $this->addFlash('sonata_flash_success', 'Delete Backups OK');
      }
    }
    catch (\Exception $e)
    {
      $this->addFlash('sonata_flash_error', 'Something went wrong: '.$e->getMessage());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function createBackupAction(KernelInterface $kernel, Request $request = null)
  {
    if (false === $this->admin->isGranted('BACKUP'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:backup:create',
    ]);

    $output = new NullOutput();

    $is_name_defined = false;
    if ($request->get('backupName'))
    {
      $input = new ArrayInput([
        'command' => 'catrobat:backup:create',
        'backupName' => $request->get('backupName'),
      ]);
      $is_name_defined = true;
    }

    try
    {
      $return = $application->run($input, $output);
      if (0 == $return)
      {
        if ($is_name_defined)
        {
          $this->addFlash('sonata_flash_success', 'Create Backup: ['.$input['backupName'].'] OK');
        }
        else
        {
          $this->addFlash('sonata_flash_success', 'Create Backup OK');
        }
      }
    }
    catch (\Exception $e)
    {
      $this->addFlash('sonata_flash_error', 'Something went wrong: '.$e->getMessage());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function listAction(Request $request = null)
  {
    if (false === $this->admin->isGranted('LIST'))
    {
      throw new AccessDeniedException();
    }

    //... use any methods or services to get statistics data
    $RemovableObjects = [];

    $description = "This will remove all extracted catrobat files in the 'extraced'-directory and flag the programs accordingly";
    $rm = new RemovableMemory('Extracted Catrobatfiles', $description);
    $this->setSizeOfObject($rm, $this->container->getParameter('catrobat.file.extract.dir'));
    $rm->setCommandName('Delete extracted files');
    $rm->setCommandLink($this->admin->generateUrl('extracted'));
    $RemovableObjects[] = $rm;

    $description = "This will remove all generated apk-files in the 'apk'-directory and flag the programs accordingly";
    $rm = new RemovableMemory('Generated APKs', $description);
    $this->setSizeOfObject($rm, $this->container->getParameter('catrobat.apk.dir'), ['apk']);
    $rm->setCommandName('Delete APKs');
    $rm->setCommandLink($this->admin->generateUrl('apk'));
    $RemovableObjects[] = $rm;

    $description = "This will remove all backups stored on this server in the 'backup'-directory (".$this->container->getParameter('catrobat.backup.dir').')';
    $rm = new RemovableMemory('Manual backups', $description);
    $this->setSizeOfObject($rm, $this->container->getParameter('catrobat.backup.dir'), ['gz']);
    $rm->setCommandName('Delete backups');
    $rm->setCommandLink($this->admin->generateUrl('delete_backups'));
    $RemovableObjects[] = $rm;

    $description = "This will create a backup which will be stored on this server in the 'backup'-directory (".$this->container->getParameter('catrobat.backup.dir').')';
    $ac = new AdminCommand('Create backup', $description);
    $ac->setCommandName('Create backup');
    $ac->setCommandLink($this->admin->generateUrl('create_backup'));
    $backupCommand = $ac;

    $description = 'This will remove all log files.';
    $rm = new RemovableMemory('Logs', $description);
    $this->setSizeOfObject($rm, $this->container->getParameter('catrobat.logs.dir'));
    $rm->setCommandName('Delete log files');
    $rm->setCommandLink($this->admin->generateUrl('delete_logs'));
    $rm->setArchiveCommandLink($this->admin->generateUrl('archive_logs'));
    $rm->setArchiveCommandName('Archive logs files');
    $RemovableObjects[] = $rm;

    $freeSpace = disk_free_space('/');
    $usedSpace = disk_total_space('/') - $freeSpace;
    $usedSpaceRaw = $usedSpace;
    foreach ($RemovableObjects as $obj)
    {
      $usedSpaceRaw -= $obj->size_raw;
    }

    $programsSize = $this->get_dir_size($this->container->getParameter('catrobat.file.storage.dir'));
    $usedSpaceRaw -= $programsSize;

    $screenshotSize = $this->get_dir_size($this->container->getParameter('catrobat.screenshot.dir'));
    $featuredImageSize = $this->get_dir_size($this->container->getParameter('catrobat.featuredimage.dir'));
    $mediaPackageSize = $this->get_dir_size($this->container->getParameter('catrobat.mediapackage.dir'));
    $backupSize = $programsSize + $screenshotSize + $featuredImageSize + $mediaPackageSize;

    $whole_ram = (float) shell_exec("free | grep Mem | awk '{print $2}'") * 1000;
    $used_ram = (float) shell_exec("free | grep Mem | awk '{print $3}'") * 1000;
    $free_ram = (float) shell_exec("free | grep Mem | awk '{print $4}'") * 1000;
    $shared_ram = (float) shell_exec("free | grep Mem | awk '{print $5}'") * 1000;
    $cached_ram = (float) shell_exec("free | grep Mem | awk '{print $6}'") * 1000;
    $available_ram = (float) shell_exec("free | grep Mem | awk '{print $6}'") * 1000;

    $free_ram_percentage = ($free_ram / $whole_ram) * 100;
    $used_ram_percentage = ($used_ram / $whole_ram) * 100;
    $shared_ram_percentage = ($shared_ram / $whole_ram) * 100;
    $cached_ram_percentage = ($cached_ram / $whole_ram) * 100;

    return $this->renderWithExtraParams('Admin/maintain.html.twig', [
      'RemovableObjects' => $RemovableObjects,
      'RemovableBackupObjects' => $this->getBackupFileObjects(),
      'wholeSpace' => $this->getSymbolByQuantity($usedSpace + $freeSpace),
      'usedSpace' => $this->getSymbolByQuantity($usedSpaceRaw),
      'usedSpace_raw' => $usedSpaceRaw,
      'freeSpace_raw' => $freeSpace,
      'freeSpace' => $this->getSymbolByQuantity($freeSpace),
      'programsSpace_raw' => $programsSize,
      'programsSpace' => $this->getSymbolByQuantity($programsSize),
      'freeRamPercentage' => $free_ram_percentage,
      'usedRamPercentage' => $used_ram_percentage,
      'sharedRamPercentage' => $shared_ram_percentage,
      'cachedRamPercentage' => $cached_ram_percentage,
      'wholeRam' => $this->getSymbolByQuantity($whole_ram),
      'usedRam' => $this->getSymbolByQuantity($used_ram),
      'freeRam' => $this->getSymbolByQuantity($free_ram),
      'sharedRam' => $this->getSymbolByQuantity($shared_ram),
      'cachedRam' => $this->getSymbolByQuantity($cached_ram),
      'availableRam' => $this->getSymbolByQuantity($available_ram),
      'backupCommand' => $backupCommand,
      'backupSize' => $this->getSymbolByQuantity($backupSize),
    ]);
  }

  /**
   * @return array
   */
  private function getBackupFileObjects()
  {
    $objects = [];
    $backupFolder = $this->container->getParameter('catrobat.backup.dir');
    $files = array_reverse(glob($backupFolder.'/*.tar.gz')); // get all file names
    foreach ($files as $file)
    { // iterate files
      $objects[] = $this->generateBackupObject($file);
    }

    return $objects;
  }

  /**
   * @param      $directory
   * @param null $extension
   *
   * @return int
   */
  private function get_dir_size($directory, $extension = null)
  {
    $count_size = 0;
    $count = 0;
    $dir_array = preg_grep('/^([^.])/', scandir($directory)); //no hidden files
    foreach ($dir_array as $key => $filename)
    {
      if (null != $extension && !in_array(pathinfo($filename, PATHINFO_EXTENSION), $extension, true))
      {
        continue;
      }
      if ('..' != $filename && '.' != $filename)
      {
        if (is_dir($directory.'/'.$filename))
        {
          $new_foldersize = $this->get_dir_size($directory.'/'.$filename);
          $count_size = $count_size + $new_foldersize;
        }
        else
        {
          if (is_file($directory.'/'.$filename))
          {
            $count_size = $count_size + filesize($directory.'/'.$filename);
            ++$count;
          }
        }
      }
    }

    return $count_size;
  }

  /**
   * @param      $object
   * @param      $path
   * @param null $extension
   */
  private function setSizeOfObject(&$object, $path, $extension = null)
  {
    /* @var RemovableMemory $object */
    if (is_dir($path))
    {
      $size = $this->get_dir_size($path, $extension);
      $object->setSizeRaw($size);
      $object->setSize($this->getSymbolByQuantity($size));
    }
  }

  /**
   * @param $bytes
   *
   * @return string
   */
  private function getSymbolByQuantity($bytes)
  {
    $symbol = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    $exp = floor(log($bytes) / log(1024)) > 0 ? floor(log($bytes) / log(1024)) : 0;

    return sprintf('%.2f '.$symbol[$exp], ($bytes / pow(1024, floor($exp))));
  }

  private function executeShellCommand(array $command, string $description, OutputInterface $output): void
  {
    $output->write($description." ('".$command."') ... ");
    $process = new Process($command);
    $process->run();
    if ($process->isSuccessful())
    {
      $output->writeln($description.' OK');
    }
    else
    {
      $output->writeln('failed!');
    }
  }

  /**
   * @param $file
   *
   * @return RemovableMemory
   */
  private function generateBackupObject($file)
  {
    $filename = pathinfo($file, PATHINFO_BASENAME);
    $backupObject = new RemovableMemory($filename, 'created at: '.date('d.F.Y H:i:s', filemtime($file)));
    $backupObject->setSizeRaw(filesize($file));
    $backupObject->setSize($this->getSymbolByQuantity($backupObject->size_raw));
    $backupObject->setCommandLink($this->admin->generateUrl('delete_backups', ['backupFile' => $filename]));
    $backupObject->setCommandName('Delete backups');
    $backupObject->setDownloadLink($this->generateUrl('backup_download', ['backupFile' => $filename]));
    $backupObject->setExecuteLink($this->admin->generateUrl('restore_backup', ['backupFile' => $filename]));

    return $backupObject;
  }
}
