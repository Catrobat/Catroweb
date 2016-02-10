<?php
namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;

class LogsController extends CRUDController
{
    const LOG_DIR = '../app/logs/';
    const LOG_PATTERN = '*.log';
    const TAIL_COMMAND = 'tail -n 20 ';

    /*
     * (non-PHPdoc)
     * @see \Sonata\AdminBundle\Controller\CRUDController::listAction()
     */
    public function listAction(Request $request = null)
    {
        $finder = new Finder();
        $finder->files()->in(self::LOG_DIR)->name(self::LOG_PATTERN);
        $finder->sortByName();

        foreach($finder as $file) {
            $files[] = $file->getRelativePathname();
        }

        for ($i = 0; $i < count($files); $i++ ) {
            $command = self::TAIL_COMMAND.self::LOG_DIR.$files[$i];
            $content[$i]= explode("\n",shell_exec($command));
        }

        return $this->render(':Admin:logs.html.twig', array(
            'files' => $files,
            'content' => $content
        ));
    }
}