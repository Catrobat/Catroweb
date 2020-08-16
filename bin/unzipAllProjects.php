<?php

use App\Catrobat\CatrobatCode\Parser\CatrobatCodeParser;
use App\Catrobat\Services\CatrobatFileSanitizer;
use \App\Catrobat\Services\CatrobatFileExtractor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

require_once('vendor/autoload.php');
require_once(__DIR__ . '/../src/Catrobat/Services/CatrobatFileExtractor.php');
require_once(__DIR__ . '/../src/Catrobat/Services/CatrobatFileSanitizer.php');
require_once(__DIR__ . '/../src/Catrobat/CatrobatCode/Parser/CatrobatCodeParser.php');
require_once(__DIR__ . '/../src/Entity/ProgramManager.php');
$code_parser = new CatrobatCodeParser();
$sanitizer = new CatrobatFileSanitizer($code_parser);
$file_extractor= new CatrobatFileExtractor(__DIR__ . '/../public/resources/extract/', "resources/extract/");

$files = glob(__DIR__.'/../public/resources/programs/*'); // get all file names
foreach($files as $file){
    $file_path = explode(".", $file);
    $file_name = explode("/", $file_path[2]);
    try {
        $extracted_file = $file_extractor->extract(new File($file));
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
    $sanitizer->sanitize($extracted_file);
    $extracted_file_path = $file_extractor->getExtractDir() . '/' . end($file_name);
    if(!is_dir($extracted_file_path))
    {
        mkdir($extracted_file_path, null, true);
        rename($extracted_file->getPath(), $extracted_file_path);
        (new Filesystem())->remove($file);
    }
}
