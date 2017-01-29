<?php
namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\CatrobatCode\StatementFactory;
use Catrobat\AppBundle\Exceptions\Upload\InvalidXmlException;
use Catrobat\AppBundle\Exceptions\Upload\MissingXmlException;
use Symfony\Component\Finder\Finder;

class RemixData
{
    const SCRATCH_DOMAIN = 'scratch.mit.edu';

    private $remix_url;
    private $remix_url_data;

    /**
     * @param string $remix_url
     */
    public function __construct($remix_url)
    {
        $this->remix_url = $remix_url;
        $this->remix_url_data = parse_url($this->remix_url);
    }

    public function getUrl()
    {
        return $this->remix_url;
    }

    public function getProgramId() {
        if (!array_key_exists('path', $this->remix_url_data)) {
            return 0;
        }

        $remix_url_path = $this->remix_url_data['path'];
        preg_match("/(\\/[0-9]+(\\/)?)$/", $remix_url_path, $id_matches);
        return (count($id_matches) > 0) ? intval(str_replace('/', '', $id_matches[0])) : 0;
    }

    public function isScratchProgram() {
        if (!array_key_exists('host', $this->remix_url_data)) {
            return false;
        }
        return (strpos($this->remix_url_data['host'], self::SCRATCH_DOMAIN) !== false);
    }

    public function isAbsoluteUrl() {
        return array_key_exists('host', $this->remix_url_data)
        && in_array($this->remix_url_data['scheme'], array('http', 'https'));
    }
}
