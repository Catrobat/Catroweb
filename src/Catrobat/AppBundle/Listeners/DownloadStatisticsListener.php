<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramDownloadedEvent;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class DownloadStatisticsListener
{
    private $download_statistics_service;
    private $security_token_storage;

    public function __construct($download_statistics_service, $security_token_storage)
    {
        $this->download_statistics_service = $download_statistics_service;
        $this->security_token_storage = $security_token_storage;
    }

    public function onTerminateEvent(PostResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        if ($attributes->has('download_statistics_program_id')) {
            $program_id = $attributes->get('download_statistics_program_id');
            $ip = $event->getRequest()->getClientIp();
            $ip = $this->getOriginalClientIp($ip);
            $user_agent = $event->getRequest()->headers->get('User-Agent');
            $user = $this->security_token_storage->getToken()->getUser();

            if ($user === 'anon.') {
                $user_name = $user;
            } else {
                $user_name = $user->getUsername();
            }

            $this->createProgramDownloadStatistics($program_id, $ip, $user_agent, $user_name);
            $event->getRequest()->attributes->remove('download_statistics_program_id');
        }
    }

    public function createProgramDownloadStatistics($program_id, $ip, $user_agent, $user_name)
    {
        if (strpos($user_agent, 'okhttp') === false) {
            $this->download_statistics_service->createProgramDownloadStatistics($program_id, $ip, $user_agent, $user_name);
        }
    }

    private function getOriginalClientIp($ip)
    {
        if (strpos($ip,',') !== false) {
            $ip = substr($ip,0,strpos($ip,','));
        }
        return $ip;
    }
}
