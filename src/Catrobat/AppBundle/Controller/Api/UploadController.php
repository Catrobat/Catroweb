<?php
namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Requests\AddProgramRequest;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Exceptions\Upload\MissingChecksumException;
use Catrobat\AppBundle\Exceptions\Upload\InvalidChecksumException;
use Catrobat\AppBundle\Exceptions\Upload\MissingPostDataException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;
use Catrobat\AppBundle\Entity\GameJamRepository;
use Doctrine\ORM\EntityRepository;
use Catrobat\AppBundle\Exceptions\Upload\NoGameJamException;

/**
 * @Route(service="controller.upload")
 */
class UploadController extends Controller
{

    private $usermanager;

    private $tokenstorage;

    private $programmanager;

    private $gamejamrepository;

    private $tokengenerator;

    private $translator;

    public function __construct(UserManager $usermanager, TokenStorage $tokenstorage, ProgramManager $programmanager, GameJamRepository $gamejamrepository, TokenGenerator $tokengenerator, TranslatorInterface $translator)
    {
        $this->usermanager = $usermanager;
        $this->tokenstorage = $tokenstorage;
        $this->programmanager = $programmanager;
        $this->gamejamrepository = $gamejamrepository;
        $this->tokengenerator = $tokengenerator;
        $this->translator = $translator;
    }

    /**
     * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function uploadAction(Request $request)
    {
        return $this->processUpload($request);
    }

    /**
     * @Route("/api/gamejam/submit.json", name="catrobat_api_gamejam_submit", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function submitAction(Request $request)
    {
        $jam = $this->gamejamrepository->getCurrentGameJam();
        if ($jam == null)
        {
            throw new NoGameJamException();
        }
        return $this->processUpload($request, $jam);
    }

    private function processUpload(Request $request, $gamejam = null)
    {
        $response = array();
        if ($request->files->count() != 1) {
            throw new MissingPostDataException();
        } elseif (! $request->request->has('fileChecksum')) {
            throw new MissingChecksumException();
        } else {
            $file = array_values($request->files->all())[0];
            if (md5_file($file->getPathname()) != $request->request->get('fileChecksum')) {
                throw new InvalidChecksumException();
            } else {
                $user = $this->tokenstorage->getToken()->getUser();
                $add_program_request = new AddProgramRequest($user, $file, $request->getClientIp(), $gamejam, $request->request->get('deviceLanguage'));
                
                $program = $this->programmanager->addProgram($add_program_request);
                $user->setUploadToken($this->tokengenerator->generateToken());
                $this->usermanager->updateUser($user);

                $response['projectId'] = $program->getId();
                $response['statusCode'] = StatusCode::OK;
                $response['answer'] = $this->trans('success.upload');
                $response['token'] = $user->getUploadToken();
                if ($gamejam !== null && !$program->isAcceptedForGameJam())
                {
                    $response['form'] = $this->assembleFormUrl($gamejam, $user, $program, $request);
                }

                $request->attributes->set('post_to_facebook', true);
                $request->attributes->set('program_id', $program->getId());
            }
        }
        
        $response['preHeaderMessages'] = '';

        return JsonResponse::create($response);
    }

    private function assembleFormUrl($gamejam, $user, $program, $request)
    {
        $languageCode = $this->getLanguageCode($request);

        $url = $gamejam->getFormUrl();
        $url = str_replace("%CAT_ID%", $program->getId(), $url);
        $url = str_replace("%CAT_MAIL%", $user->getEmail(), $url);
        $url = str_replace("%CAT_NAME%", $user->getUsername(), $url);
        $url = str_replace("%CAT_LANGUAGE%", $languageCode, $url);

        return $url;
    }
    
    private function trans($message, $parameters = array())
    {
        return $this->translator->trans($message, $parameters, 'catroweb');
    }

    private function getLanguageCode($request) {
        $languageCode = strtoupper($request->getLocale());

        if($languageCode != "DE")
            $languageCode = "EN";

        return $languageCode;
    }
}
