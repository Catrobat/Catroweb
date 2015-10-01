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

class UploadController extends Controller
{

    /**
     * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function uploadAction(Request $request)
    {
        $user_manager = $this->get('usermanager');
        $context = $this->get('security.context');
        $program_manager = $this->get('programmanager');
        $tokenGenerator = $this->get('tokengenerator');
        
        $response = array();
        if ($request->files->count() != 1) {
            $response['statusCode'] = StatusCode::MISSING_POST_DATA;
            $response['answer'] = $this->trans('errors.post-data');
        } elseif (! $request->request->has('fileChecksum')) {
            $response['statusCode'] = StatusCode::MISSING_CHECKSUM;
            $response['answer'] = $this->trans('errors.checksum.missing');
        } else {
            $file = array_values($request->files->all())[0];
            if (md5_file($file->getPathname()) != $request->request->get('fileChecksum')) {
                $response['statusCode'] = StatusCode::INVALID_CHECKSUM;
                $response['answer'] = $this->trans('errors.checksum.invalid');
            } else {
                $add_program_request = new AddProgramRequest($context->getToken()->getUser(), $file, $request->getClientIp());
                
                $id = $program_manager->addProgram($add_program_request)->getId();
                $user = $context->getToken()->getUser();
                $user->setToken($tokenGenerator->generateToken());
                $user_manager->updateUser($user);
                
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
        return $this->get('translator')->trans($message, $parameters, 'catroweb');
    }
}
