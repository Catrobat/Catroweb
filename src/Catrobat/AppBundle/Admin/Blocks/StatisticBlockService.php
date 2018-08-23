<?php
namespace Catrobat\AppBundle\Admin\Blocks;

use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StatisticBlockService extends AbstractBlockService
{

    private $extraced_path;
    private $apk_path;

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $block, Response $response = null)
    {
        $settings = $block->getSettings();

        $wholeSpace = disk_total_space("/");
        $freeSpaceRaw = disk_free_space("/");
        $wholeSpaceRaw = $wholeSpace;
        $usedSpace = $wholeSpaceRaw-$freeSpaceRaw;

        return $this->renderResponse($block->getTemplate(), array(
            'block'     => $block->getBlock(),
            'settings'  => $settings,
            'wholeSpace' => $this->getSymbolByQuantity($wholeSpace),
            'wholeSpace_raw' => $wholeSpaceRaw,
            'freeSpace_raw' => $freeSpaceRaw,
            'freeSpace' => $this->getSymbolByQuantity($freeSpaceRaw),
            'usedSpace' => $this->getSymbolByQuantity($usedSpace),
            'ram' => shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"),
        ), $response);

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Cleanup Server';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'url'      => false,
            'title'    => 'Server Information',
            'template' => 'Admin/block_statistic.html.twig',
        ));
    }

    public function __construct($name, $templating, $extraced_path, $apk_path)
    {
        parent::__construct($name, $templating);
        $this->extraced_path = $extraced_path;
        $this->apk_path = $apk_path;
    }


    function getSymbolByQuantity($bytes) {
        $symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $exp = floor(log($bytes)/log(1024));

        return sprintf('%.2f '.$symbol[$exp], ($bytes/pow(1024, floor($exp))));
    }
}

