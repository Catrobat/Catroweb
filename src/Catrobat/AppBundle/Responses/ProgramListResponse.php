<?php
namespace Catrobat\AppBundle\Responses;

class ProgramListResponse
{

    private $programs;

    private $total_programs;

    private $show_details;

    public function __construct($programs, $total_programs, $show_details = true)
    {
        $this->programs = $programs;
        $this->total_programs = $total_programs;
        $this->show_details = $show_details;
    }

    public function getPrograms()
    {
        return $this->programs;
    }

    public function getTotalPrograms()
    {
        return $this->total_programs;
    }

    public function getShowDetails()
    {
        return $this->show_details;
    }
}