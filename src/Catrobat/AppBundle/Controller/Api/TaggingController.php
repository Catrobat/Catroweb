<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\Tag;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\StatusCode;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;

class TaggingController extends Controller
{

    /**
     * @Route("/api/tags/getTags.json", name="api_get_tags", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function taggingAction(Request $request)
    {
        $tags_repo = $this->get('tagrepository');

        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata('Catrobat\AppBundle\Entity\Tag')->getFieldNames();

        $tags = array();
        $tags['statusCode'] = 200;
        $tags['constantTags'] = array();

        $language = $request->query->get('language');
        if(!in_array($language, $metadata)) {
            $language = 'en';
            $tags['statusCode'] = 404;
        }
        $results = $tags_repo->getConstantTags($language);

        foreach($results as $tag)
        {
            array_push($tags['constantTags'], $tag[$language]);
        }
        return JsonResponse::create($tags);
    }
}
