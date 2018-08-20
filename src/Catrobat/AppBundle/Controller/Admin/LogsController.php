<?php
namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;

class LogsController extends CRUDController
{
    const LOG_DIR = '../app/logs/';
    const LOG_PATTERN = '*.log';

    const FILTER_LEVEL_DEBUG = 0;
    const FILTER_LEVEL_INFO = 1;
    const FILTER_LEVEL_NOTICE = 2;
    const FILTER_LEVEL_WARNING = 3;
    const FILTER_LEVEL_ERROR = 4;
    const FILTER_LEVEL_CRITICAL = 5;
    const FILTER_LEVEL_ALERT = 6;
    const FILTER_LEVEL_EMERGENCY = 7;

    /*
     * (non-PHPdoc)
     * @see \Sonata\AdminBundle\Controller\CRUDController::listAction()
     * @Method({"POST"})
     */
    public function listAction(Request $request = null) //public function listAction(Request $request = null, int $linecount = 20, int $filter = self::FILTER_LEVEL_DEBUG, Boolean $greater_equal_than_level = true)
    {
        $filter = self::FILTER_LEVEL_WARNING;
        $greater_equal_than_level = true;
        $linecount = 20;
        /*
         * @var $finder Symfony\Component\Finder\Finder
         */
        if ($request->isXmlHttpRequest()) {
            if ($request->query->get('count')) {
                $linecount = $request->query->getInt('count');
            }
            if ($request->query->get('filter') !== false) {
                $filter = $request->query->getInt('filter');
            }
            if ($request->query->get('greaterThan')) {
                $greater_equal_than_level = $request->query->getBoolean('greaterThan');
            }
        }

        $finder = new Finder();
        $finder->files()->in(self::LOG_DIR)->depth(0)->name(self::LOG_PATTERN);
        $finder->sortByName();

        foreach ($finder as $file) {
            $files[] = $file->getRelativePathname();
        }

        $content = [];
        for ($i = 0; $i < count($files); $i++) {
            $filename = self::LOG_DIR . $files[$i];
            $file = popen("tac $filename", "r");

            $index = 0;
            while (($line = fgets($file)) && ($index < $linecount)) {
                $logline = new LogLine($line);

                if (($greater_equal_than_level && $logline->debug_level >= $filter) ||
                    (!$greater_equal_than_level && $logline->debug_level == $filter)
                ) {
                    $content[$i][$index] = $logline;

                    $index++;
                }
            }
            if (!array_key_exists($i, $content)) {
                $content[$i][0] = new LogLine();
            }
            pclose($file);
        }

        return $this->render(':Admin:logs.html.twig', array(
            'files' => $files,
            'content' => $content
        ));
    }
}