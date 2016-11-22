<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class GameJamController extends Controller
{
    /**
     * @Route("/gamejame/submit-your-own", name="gamejam_submit_own")
     * @Method({"GET"})
     */
    public function gamejamSubmitOwnAction()
    {
        /**
         * @var $gamejam GameJam
         */
        $jam = null;
        $gamejam = $this->get('gamejamrepository')->getCurrentGameJam();

        if($gamejam) {
            $gamejam_flavor = $gamejam->getFlavor();

            if($gamejam_flavor != null) {
                $config = $this->container->getParameter('gamejam');
                $gamejam_config = $config[$gamejam_flavor];
                $jam = $this->configureSubmitYourOwn($gamejam_config, $gamejam);
            }
        }
        return $this->get('templating')->renderResponse('::gamejam_submit_own.html.twig', array(
            'jam' => $jam
        ));
    }

    /**
     * @Route("/gaming-tutorials/{page}", name="catrobat_web_gamejamtutorialcards", defaults={"page" = -1}, requirements={"page":"\d+"})
     * @Method({"GET"})
     */
    public function tutorialcardsAction($page)
    {
        $cards_num = 6;

        if ($page > $cards_num) {
            throw $this->createNotFoundException('Unable to find tutorialcard.');
        }

        if ($page == -1) {
            return $this->get('templating')->renderResponse(':help:gamejamtutorialcards.html.twig', array('count' => $cards_num));
        }

        $blocks = array();
        $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
        $blocks[1] = array('video_link' => "6ytY_vfsnNU");
        $blocks[2] = array('program_id' => "0");

        switch($page) {
            case 1:
                $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
                $blocks[1] = array('video_link' => "tGgMFWoJDBU");
                $blocks[2] = array('download_url' => $this->generateUrl('download', array('id' => 4799, 'fname' => "Welcome to Wonderland")));
                break;
            case 2:
                $blocks[0] = array('image_orientation' => "potrait", 'image_count' => 2);
                $blocks[1] = array('video_link' => "mx2DLFIg1Rc");
                $blocks[2] = array('download_url' => $this->generateUrl('download', array('id' => 4801, 'fname' => "Save Alice")));
                break;
            case 3:
                $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
                $blocks[1] = array('video_link' => "HxYr_2HdMsE");
                $blocks[2] = array('download_url' => $this->generateUrl('download', array('id' => 4683, 'fname' => "The Hatter - Hit and Run")));
                break;
            case 4:
                $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
                $blocks[1] = array('video_link' => "yLqhLmX9Mp4");
                $blocks[2] = array('download_url' => $this->generateUrl('download', array('id' => 4683, 'fname' => "The Hatter - Hit and Run")));
                break;
            case 5:
                $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
                $blocks[1] = array('video_link' => "G85_vgb1Ja4");
                $blocks[2] = array('download_url' => $this->generateUrl('download', array('id' => 4684, 'fname' => "Whack a Cheshire Cat")));
                break;
            case 6:
                $blocks[0] = array('image_orientation' => "potrait", 'image_count' => 2);
                $blocks[1] = array('video_link' => "m97g4G49kOg");
                $blocks[2] = array('download_url' => $this->generateUrl('download', array('id' => 4682, 'fname' => "A Rabbits Race")));
                break;
        }

        return $this->get('templating')->renderResponse(':help:gamejamtutorialcard.html.twig', array(
            'page' => $page,
            'blocks' => $blocks,
        ));
    }

    /**
     * @param $gamejam_config
     * @param $gamejam
     * @return array
     */
    private function configureSubmitYourOwn($gamejam_config, $gamejam) {
        $jam = null;
        if ($gamejam_config != null) {
            $display_name = $gamejam_config['display_name'];
            $mobile_en_image_url = $gamejam_config['mobile_image_url_en'];
            $mobile_de_image_url = $gamejam_config['mobile_image_url_de'];
            $web_en_image_url = $gamejam_config['web_image_url_en'];
            $web_de_image_url = $gamejam_config['web_image_url_de'];
            $gamejam_url = $gamejam_config['gamejam_url'];
            $gamejam_tag = $gamejam->getHashtag();

            $jam = array(
                'name' => $display_name,
                'mobile_en_image_url' => $mobile_en_image_url,
                'mobile_de_image_url' => $mobile_de_image_url,
                'web_en_image_url' => $web_en_image_url,
                'web_de_image_url' => $web_de_image_url,
                'gamejam_url' => $gamejam_url,
                'tag' => $gamejam_tag
            );
        }
        return $jam;
    }
}
