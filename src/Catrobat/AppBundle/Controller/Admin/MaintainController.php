<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Catrobat\AppBundle\Commands\ArchiveLogsCommand;
use Catrobat\AppBundle\Commands\CleanApkCommand;
use Catrobat\AppBundle\Commands\CleanExtractedFileCommand;
use Catrobat\AppBundle\Commands\CleanBackupsCommand;
use Catrobat\AppBundle\Commands\CleanLogsCommand;
use Catrobat\AppBundle\Commands\CreateBackupCommand;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MaintainController extends Controller
{
    public function extractedAction() {
        if ($this->admin->isGranted('EXTRACTED') === false) {
            throw new AccessDeniedException();
        }

        $command = new CleanExtractedFileCommand();
        $command->setContainer($this->container);

        $return = $command->run(new ArrayInput(array()), new NullOutput());
        if ($return == 0) {
            $this->addFlash('sonata_flash_success', 'Reset extracted files OK');
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }

    public function archiveLogsAction() {
        if ($this->admin->isGranted('EXTRACTED') === false) {
            throw new AccessDeniedException();
        }

        $command = new ArchiveLogsCommand();
        $command->setContainer($this->container);

        $return = $command->run(new ArrayInput(array()), new NullOutput());
        if ($return == 0) {
            $this->addFlash('sonata_flash_success', 'Archive log files OK');
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }

    public function deleteLogsAction() {
        if ($this->admin->isGranted('EXTRACTED') === false) {
            throw new AccessDeniedException();
        }

        $command = new CleanLogsCommand();
        $command->setContainer($this->container);

        $return = $command->run(new ArrayInput(array()), new NullOutput());
        if ($return == 0) {
            $this->addFlash('sonata_flash_success', 'Reset log files OK');
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }


    public function apkAction() {
        if ($this->admin->isGranted('APK') === false) {
            throw new AccessDeniedException();
        }

        $command = new CleanApkCommand();
        $command->setContainer($this->container);

        $return = $command->run(new ArrayInput(array()), new NullOutput());
        if ($return == 0) {
            $this->addFlash('sonata_flash_success', 'Reset APK Projects OK');
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }


    public function deleteBackupsAction(Request $request = NULL) {
        if (false === $this->admin->isGranted('BACKUP')) {
            throw new AccessDeniedException();
        }

        $command = new CleanBackupsCommand();
        $command->setContainer($this->container);

        $input = array();
        if ($request->get("backupFile")) {
            $input["backupfile"] = $request->get("backupFile");
        } else {
            $input["--all"] = "--all";
        }

        try {
            $return = $command->run(new ArrayInput($input), new NullOutput());
            if ($return == 0) {
                $this->addFlash('sonata_flash_success', 'Delete Backups OK');
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'Something went wrong: ' . $e->getMessage());
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }

    public function createBackupAction(Request $request = NULL) {
        if (false === $this->admin->isGranted('BACKUP')) {
            throw new AccessDeniedException();
        }

        $command = new CreateBackupCommand();
        $command->setContainer($this->container);

        $input = array();
        if ($request->get("backupName")) {
            $input["backupName"] = $request->get("backupName");
        }

        try {
            $return = $command->run(new ArrayInput($input), new NullOutput());
            if ($return == 0) {
                if (count($input) > 0) {
                    $this->addFlash('sonata_flash_success', 'Create Backup: [' . $input["backupName"] . '] OK');
                } else {
                    $this->addFlash('sonata_flash_success', 'Create Backup OK');
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'Something went wrong: ' . $e->getMessage());
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }

    public function listAction(Request $request = NULL) {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        //... use any methods or services to get statistics data
        $removeableObjects = array();

        $description = "This will remove all extracted catrobat files in the 'extraced'-directory and flag the programs accordingly";
        $rm = new RemoveableMemory("Extracted Catrobatfiles", $description);
        $this->setSizeOfObject($rm, $this->container->getParameter("catrobat.file.extract.dir"));
        $rm->setCommandName("Delete extracted files");
        $rm->setCommandLink($this->admin->generateUrl("extracted"));
        $removeableObjects[] = $rm;

        $description = "This will remove all generated apk-files in the 'apk'-directory and flag the programs accordingly";
        $rm = new RemoveableMemory("Generated APKs", $description);
        $this->setSizeOfObject($rm, $this->container->getParameter("catrobat.apk.dir"), array("apk"));
        $rm->setCommandName("Delete APKs");
        $rm->setCommandLink($this->admin->generateUrl("apk"));
        $removeableObjects[] = $rm;

        $description = "This will remove all backups stored on this server in the 'backup'-directory (" . $this->container->getParameter("catrobat.backup.dir") . ")";
        $rm = new RemoveableMemory("Manual backups", $description);
        $this->setSizeOfObject($rm, $this->container->getParameter("catrobat.backup.dir"), array("gz"));
        $rm->setCommandName("Delete backups");
        $rm->setCommandLink($this->admin->generateUrl("delete_backups"));
        $removeableObjects[] = $rm;

        $description = "This will create a backup which will be stored on this server in the 'backup'-directory (" . $this->container->getParameter("catrobat.backup.dir") . ")";
        $ac = new AdminCommand("Create backup", $description);
        $ac->setCommandName("Create backup");
        $ac->setCommandLink($this->admin->generateUrl("create_backup"));
        $backupCommand = $ac;

        $description = "This will remove all log files.";
        $rm = new RemoveableMemory("Logs", $description);
        $this->setSizeOfObject($rm, $this->container->getParameter("catrobat.logs.dir"));
        $rm->setCommandName("Delete log files");
        $rm->setCommandLink($this->admin->generateUrl("delete_logs"));
        $rm->setArchiveCommandLink($this->admin->generateUrl("archive_logs"));
        $rm->setArchiveCommandName("Archive logs files");
        $removeableObjects[] = $rm;

        $freeSpace = disk_free_space("/");
        $usedSpace = disk_total_space("/") - $freeSpace;
        $usedSpaceRaw = $usedSpace;
        foreach ($removeableObjects as $obj) {
            $usedSpaceRaw -= $obj->size_raw;
        }

        $programsSize = $this->get_dir_size($this->container->getParameter("catrobat.file.storage.dir"));
        $usedSpaceRaw -= $programsSize;

        $screenshotSize = $this->get_dir_size($this->container->getParameter('catrobat.screenshot.dir'));
        $featuredImageSize = $this->get_dir_size($this->container->getParameter('catrobat.featuredimage.dir'));
        $mediaPackageSize = $this->get_dir_size($this->container->getParameter('catrobat.mediapackage.dir'));
        $backupSize = $programsSize + $screenshotSize + $featuredImageSize + $mediaPackageSize;

        return $this->render('Admin/maintain.html.twig', array(
            'RemoveableObjects' => $removeableObjects,
            'RemoveableBackupObjects' => $this->getBackupFileObjects(),
            'wholeSpace' => $this->getSymbolByQuantity($usedSpace + $freeSpace),
            'usedSpace' => $this->getSymbolByQuantity($usedSpaceRaw),
            'usedSpace_raw' => $usedSpaceRaw,
            'freeSpace_raw' => $freeSpace,
            'freeSpace' => $this->getSymbolByQuantity($freeSpace),
            'programsSpace_raw' => $programsSize,
            'programsSpace' => $this->getSymbolByQuantity($programsSize),
            'ram' => shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"),
            'backupCommand' => $backupCommand,
            'backupSize' => $this->getSymbolByQuantity($backupSize),
        ));
    }

    private function getBackupFileObjects() {
        $objects = array();
        $backupFolder = $this->container->getParameter("catrobat.backup.dir");
        $files = array_reverse(glob($backupFolder . '/*.tar.gz')); // get all file names
        foreach ($files as $file) { // iterate files
            $objects[] = $this->generateBackupObject($file);
        }
        return $objects;
    }


    private function get_dir_size($directory, $extension = null) {
        $count_size = 0;
        $count = 0;
        $dir_array = preg_grep('/^([^.])/', scandir($directory)); //no hidden files
        foreach ($dir_array as $key => $filename) {
            if ($extension != null && !in_array(pathinfo($filename, PATHINFO_EXTENSION), $extension))
                continue;
            if ($filename != ".." && $filename != ".") {
                if (is_dir($directory . "/" . $filename)) {
                    $new_foldersize = $this->get_dir_size($directory . "/" . $filename);
                    $count_size = $count_size + $new_foldersize;
                } else if (is_file($directory . "/" . $filename)) {
                    $count_size = $count_size + filesize($directory . "/" . $filename);
                    $count++;
                }
            }
        }
        return $count_size;
    }


    private function setSizeOfObject(&$object, $path, $extension = null) {
        /** @var RemoveableMemory $object */
        if (is_dir($path)) {
            $size = $this->get_dir_size($path, $extension);
            $object->setSizeRaw($size);
            $object->setSize($this->getSymbolByQuantity($size));
        }
    }

    private function getSymbolByQuantity($bytes) {
        $symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $exp = floor(log($bytes) / log(1024)) > 0 ? floor(log($bytes) / log(1024)) : 0;

        return sprintf('%.2f ' . $symbol[$exp], ($bytes / pow(1024, floor($exp))));
    }

    private function executeShellCommand($command, $description, $output) {
        $output->write($description . " ('" . $command . "') ... ");
        $process = new Process($command);
        $process->run();
        if ($process->isSuccessful()) {
            $output->writeln($description . ' OK');
        } else {
            $output->writeln('failed!');
        }
    }

    /**
     * @param $file
     * @return RemoveableMemory
     */
    private function generateBackupObject($file) {
        $filename = pathinfo($file, PATHINFO_BASENAME);
        $backupObject = new RemoveableMemory($filename, "created at: " . date("d.F.Y H:i:s", filemtime($file)));
        $backupObject->setSizeRaw(filesize($file));
        $backupObject->setSize($this->getSymbolByQuantity($backupObject->size_raw));
        $backupObject->setCommandLink($this->admin->generateUrl("delete_backups", array("backupFile" => $filename)));
        $backupObject->setCommandName("Delete backups");
        $backupObject->setDownloadLink($this->generateUrl("backup_download", array("backupFile" => $filename)));
        $backupObject->setExecuteLink($this->admin->generateUrl("restore_backup", array("backupFile" => $filename)));
        return $backupObject;
    }
}

class RemoveableMemory
{
    public $name;
    public $description;
    public $size;
    public $size_raw;
    public $command_link;
    public $command_name;
    public $download_link;
    public $execute_link;
    public $archive_command_link;
    public $archive_command_name;

    public function __construct($name, $description) {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @param mixed $size
     */
    public function setSizeRaw($size) {
        $this->size_raw = $size;
    }

    /**
     * @param mixed $command
     */
    public function setCommandLink($command) {
        $this->command_link = $command;
    }

    /**
     * @param mixed $command
     */
    public function setCommandName($command) {
        $this->command_name = $command;
    }

    /**
     * @param mixed $download_link
     */
    public function setDownloadLink($download_link) {
        $this->download_link = $download_link;
    }

    /**
     * @return mixed
     */
    public function getArchiveCommandLink()
    {
        return $this->archive_command_link;
    }

    /**
     * @param mixed $archive_command_link
     */
    public function setArchiveCommandLink($archive_command_link)
    {
        $this->archive_command_link = $archive_command_link;
    }

    /**
     * @return mixed
     */
    public function getArchiveCommandName()
    {
        return $this->archive_command_name;
    }

    /**
     * @param mixed $archive_command_name
     */
    public function setArchiveCommandName($archive_command_name)
    {
        $this->archive_command_name = $archive_command_name;
    }

    /**
     * @param mixed $execute_link
     */
    public function setExecuteLink($execute_link) {
        $this->execute_link = $execute_link;
    }
}

class AdminCommand
{
    public $name;
    public $description;
    public $command_link;
    public $progress_link;
    public $command_name;

    public function __construct($name, $description) {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @param mixed $command
     */
    public function setCommandLink($command) {
        $this->command_link = $command;
    }

    /**
     * @param mixed $command
     */
    public function setCommandName($command) {
        $this->command_name = $command;
    }

    /**
     * @return mixed
     */
    public function getProgressLink()
    {
        return $this->progress_link;
    }

    /**
     * @param mixed $progress_link
     */
    public function setProgressLink($progress_link)
    {
        $this->progress_link = $progress_link;
    }


}