<?php

namespace Catrobat\CoreBundle\Spec\Services\Validators;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;

class ExtractedFileValidatorSpec extends ObjectBehavior
{
  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CoreBundle\Services\Validators\ExtractedFileValidator');
  }
    
  /**
  * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator1
  * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator2
  * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator3
  * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $extracted_file
	*/
  function it_calls_all_validators_added_to_it_as_long_as_there_is_no_exception($validator1, $validator2, $validator3, $extracted_file)
  {
    $validator1->validate($extracted_file)->shouldBeCalled();
    $validator2->validate($extracted_file)->shouldBeCalled();
    $validator3->validate($extracted_file)->shouldBeCalled();
    $this->addValidator($validator1);
    $this->addValidator($validator2);
    $this->addValidator($validator3);
    $this->validate($extracted_file);
  }

  /**
   * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator1
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $extracted_file
   */
  function it_passes_through_an_exception_thrown_by_a_validator($validator1, $extracted_file)
  {
    $validator1->validate($extracted_file)->willThrow(new InvalidCatrobatFileException(""));
    $this->addValidator($validator1);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($extracted_file);
  }
  
  /**
   * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator1
   * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator2
   * @param \Catrobat\CoreBundle\Services\Validators\ExtractedCatrobatFileValidatorInterface $validator3
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $extracted_file
   */
  function it_stops_calling_validators_after_an_exception_is_thrown($validator1, $validator2, $validator3, $extracted_file)
  {
    $validator1->validate($extracted_file)->shouldBeCalled();
    $validator2->validate($extracted_file)->willThrow(new InvalidCatrobatFileException(""));
    $validator3->validate($extracted_file)->shouldNotBeCalled();
    $this->addValidator($validator1);
    $this->addValidator($validator2);
    $this->addValidator($validator3);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($extracted_file);
  }
  
}
