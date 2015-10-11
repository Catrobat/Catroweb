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

/**
 * @Route(service="controller.upload")
 */
class UploadController
{

    private $usermanager;

    private $tokenstorage;

    private $programmanager;

    private $tokengenerator;

    private $translator;

    public function __construct(UserManager $usermanager, TokenStorage $tokenstorage, ProgramManager $programmanager, TokenGenerator $tokengenerator, TranslatorInterface $translator)
    {
        $this->usermanager = $usermanager;
        $this->tokenstorage = $tokenstorage;
        $this->programmanager = $programmanager;
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
        return $this->processUpload($request, true);
    }

    private function processUpload(Request $request, $submission = false)
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
                $add_program_request = new AddProgramRequest($user, $file, $request->getClientIp());
        
                $id = $this->programmanager->addProgram($add_program_request)->getId();
                $user->setToken($this->tokengenerator->generateToken());
                $this->usermanager->updateUser($user);
        
                $response['projectId'] = $id;
                $response['statusCode'] = StatusCode::OK;
                $response['answer'] = $this->trans('success.upload');
                $response['token'] = $user->getToken();
            }
        }
        
        $response['preHeaderMessages'] = '';
        
        return JsonResponse::create($response);
    }
    
    
    
    private function trans($message, $parameters = array())
    {
        return $this->translator->trans($message, $parameters, 'catroweb');
    }
}
