<?php
namespace Catrobat\AppBundle\Services\Ci;

class JenkinsDispatcher
{
    protected $router;
    protected $config;
    
    public function __construct($router, $config)
    {
        if (!isset($config['url']))
        {
            throw new \Exception();
        }
        $this->config = $config;
        $this->router = $router;
    }
    
    public function sendBuildRequest($id)
    {
        $params = array(
                "job" => $this->config['job'],
                "token" => $this->config['token'],
                $this->config['id_parameter_name'] => $id,
                "download" => $this->router->generate('download', array('id' => $id), true),
                "upload" => $this->router->generate('ci_upload_apk', array('id' => $id, 'token' => $this->config['uploadtoken']), true),
            );
        return $this->dispatch($params);
    }
    
    protected function dispatch($params)
    {
        return $this->config['url'] ."?". http_build_query($params);
    }
}