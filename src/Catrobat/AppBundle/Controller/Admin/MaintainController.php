<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Catrobat\AppBundle\Commands\CleanExtractedFileCommand;
use Catrobat\AppBundle\Commands\CleanApkCommand;
use Catrobat\AppBundle\Commands\CleanBackupsCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MaintainController extends Controller
{
    public function extractedAction()
    {
        if (false === $this->admin->isGranted('EXTRACTED')) {
            throw new AccessDeniedException();
        }

        $command = new CleanExtractedFileCommand();
        $command->setContainer($this->container);

        $return = $command->run(new ArrayInput(array()),new NullOutput());
        if($return == 0)
        {
            $this->addFlash('sonata_flash_success', 'Reset extracted files OK');
        }


        return new RedirectResponse($this->admin->generateUrl("list"));
    }


    public function apkAction()
    {
        if (false === $this->admin->isGranted('APK')) {
            throw new AccessDeniedException();
        }

        $command = new CleanApkCommand();
        $command->setContainer($this->container);

        $return = $command->run(new ArrayInput(array()),new NullOutput());
        if($return == 0)
        {
            $this->addFlash('sonata_flash_success', 'Reset APK Projects OK');
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }


    public function backupAction(Request $request = NULL)
    {
        if (false === $this->admin->isGranted('BACKUP')) {
            throw new AccessDeniedException();
        }

        $backupFile = null;
        if($request->get("backupFile"))
        {
            $backupFile = $request->get("backupFile");
        }

        $command = new CleanBackupsCommand();
        $command->setContainer($this->container);
        $input = array();

        if($backupFile != null)
        {
            $input["backupfile"] = $backupFile;
        }else
            $input["--all"]="--all";

        try{
            $return = $command->run(new ArrayInput($input),new NullOutput());
            if($return == 0)
            {
                $this->addFlash('sonata_flash_success', 'Reset Backup OK');
            }
        }catch (\Exception $e)
        {
            $this->addFlash('sonata_flash_error', 'Something went wrong: '.$e->getMessage());
        }

        return new RedirectResponse($this->admin->generateUrl("list"));
    }

    public function listAction(Request $request = NULL)
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        //... use any methods or services to get statistics data
        $removeableObjects = array();

        $description = "This will remove all extracted catrobat files in the 'extraced'-directory and flag the programs accordingly";
        $rm = new RemoveableMemory("Extracted Catrobatfiles",$description);
        $this->setSizeOfObject($rm,$this->container->getParameter("catrobat.file.extract.dir"));
        $rm->setCommandName("Delete extracted files");
        $rm->setCommandLink($this->admin->generateUrl("extracted"));
        $removeableObjects[] = $rm;

        $description = "This will remove all generated apk-files in the 'apk'-directory and flag the programs accordingly";
        $rm = new RemoveableMemory("Generated APKs",$description);
        $this->setSizeOfObject($rm,$this->container->getParameter("catrobat.apk.dir"),"apk");
        $rm->setCommandName("Delete APKs");
        $rm->setCommandLink($this->admin->generateUrl("apk"));
        $removeableObjects[] = $rm;

        $description = "This will remove all backups stored on this server in the 'backup'-directory (".$this->container->getParameter("catrobat.backup.dir").")";
        $rm = new RemoveableMemory("Manual Backups",$description);
        $this->setSizeOfObject($rm,$this->container->getParameter("catrobat.backup.dir"),"zip");
        $rm->setCommandName("Delete Backups");
        $rm->setCommandLink($this->admin->generateUrl("backup"));
        $removeableObjects[] = $rm;

        $freeSpace = disk_free_space("/");
        $usedSpace = disk_total_space("/")-$freeSpace;
        $usedSpaceRaw = $usedSpace;
        foreach($removeableObjects as $obj)
        {
            $usedSpaceRaw -= $obj->size_raw;
        }

        $programsSize = $this->get_dir_size($this->container->getParameter("catrobat.file.storage.dir"));
        $usedSpaceRaw -= $programsSize;

       return $this->render(':Admin:maintain.html.twig', array(
           'RemoveableObjects' => $removeableObjects,
           'RemoveableBackupObjects' => $this->getBackupFileObjects(),
           'wholeSpace' => $this->getSymbolByQuantity($usedSpace+$freeSpace),
           'usedSpace' => $this->getSymbolByQuantity($usedSpaceRaw),
           'usedSpace_raw' => $usedSpaceRaw,
           'freeSpace_raw' => $freeSpace,
           'freeSpace' =>  $this->getSymbolByQuantity($freeSpace),
           'programsSpace_raw' => $programsSize,
           'programsSpace' => $this->getSymbolByQuantity($programsSize),
           'ram' => shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"),
       ));
    }

    private function getBackupFileObjects()
    {
        $objects = array();
        $backupFolder = $this->container->getParameter("catrobat.backup.dir");
        $files = array_reverse(glob($backupFolder.'/*.zip')); // get all file names
        foreach($files as $file) { // iterate files
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $backupObject = new RemoveableMemory($filename,"created at: ".date ("d.F.Y H:i:s", filemtime($file)));
            $backupObject->setSizeRaw(filesize($file));
            $backupObject->setSize($this->getSymbolByQuantity($backupObject->size_raw));
            $backupObject->setCommandLink($this->admin->generateUrl("backup",array("backupFile"=>$filename)));
            $backupObject->setCommandName("Delete backup");
            $objects[]=$backupObject;
        }
        return $objects;
    }


    private function get_dir_size($directory,$extension=null) {
        $count_size = 0;
        $count = 0;
        $dir_array = preg_grep('/^([^.])/', scandir($directory)); //no hidden files
        foreach($dir_array as $key=>$filename){
            if($extension != null && pathinfo($filename,PATHINFO_EXTENSION) != $extension)
                continue;
            if($filename!=".." && $filename!="."){
                if(is_dir($directory."/".$filename)){
                    $new_foldersize = $this->get_dir_size($directory."/".$filename);
                    $count_size = $count_size+ $new_foldersize;
                }else if(is_file($directory."/".$filename)){
                    $count_size = $count_size + filesize($directory."/".$filename);
                    $count++;
                }
            }
        }
        return $count_size;
    }



    private function setSizeOfObject(&$object,$path,$extension=null)
    {
        /** @var RemoveableMemory $object */
        if(is_dir($path))
        {
            $size = $this->get_dir_size($path,$extension);
            $object->setSizeRaw($size);
            $object->setSize($this->getSymbolByQuantity($size));
        }
    }

    private function getSymbolByQuantity($bytes) {
        $symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $exp = floor(log($bytes)/log(1024))>0?floor(log($bytes)/log(1024)):0;

        return sprintf('%.2f '.$symbol[$exp], ($bytes/pow(1024, floor($exp))));
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

    public function __construct($name,$description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @param mixed $size
     */
    public function setSizeRaw($size)
    {
        $this->size_raw = $size;
    }
    /**
     * @param mixed $command
     */
    public function setCommandLink($command)
    {
        $this->command_link = $command;
    }
    /**
     * @param mixed $command
     */
    public function setCommandName($command)
    {
        $this->command_name = $command;
    }

}