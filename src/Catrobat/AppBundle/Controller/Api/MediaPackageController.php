<?php
/**
 * Copyright (c) 2017. Catrobat
 * Imagine that each Catrobat program is a cake, a very special cake that comes with its recipe (programming blocks).
 * All members of the Catrobat community share their cakes along with their recipes. This means that you can enjoy the
 * cakes and learn how to make them yourself! There are no secret recipes: the instructions on how to make these cakes
 * are open for anyone to use, reuse, modify, and serve as inspiration for new ideas... I mean cakes.
 *
 * You can eat the cakes as well as copy other people's recipes to make your own, maybe with different ingredients.
 * This freedom comes with two simple requirements:
 *
 * share your cakes along with the recipe
 * give credit to those who inspired you
 *
 *
 * In setting up the Catrobat community, we decided to adopt this approach since we believe that it supports learning
 * and creativity within the community. By sharing recipes and ingredients (scripts and artwork), people can build upon
 * one another's ideas and everyone will benefit.
 *
 * In designing the Catrobat website, we included features to encourage people to share and to give credit to others.
 * On each program page, you can always download the original scripts for the program. If you remix a program
 * (modifying the scripts or artwork, and sharing the result), we encourage you to give credit in the Program Notes,
 * mentioning the people and program that inspired you.
 *
 * Learn more about the terms of use of the Catrobat online community on https://share.catrob.at/pocketcode/termsOfUse.
 *
 * Version 1.1, 2 April 2013
 */

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\MediaPackage;
use Catrobat\AppBundle\Entity\MediaPackageCategory;
use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class MediaPackageController
 * @package Catrobat\AppBundle\Controller\Api
 */
class MediaPackageController extends Controller
{

  /**
   * @Route("/api/media/json", name="api_media_lib", defaults={"_format": "json"}, methods={"GET"})
   *
   * @return JsonResponse
   */
  public function getMediaLib()
  {
    $em = $this->getDoctrine()->getManager();
    $media_package_files = $em->getRepository('AppBundle:MediaPackageFile')
      ->findAll();
    $json_response_array = [];
    if ($media_package_files == null || empty($media_package_files))
    {
      return JsonResponse::create(
        $json_response_array
      );
    }
    foreach ($media_package_files as $media_package_file)
    {
      /** @var MediaPackageFile $media_package_file */
      array_push($json_response_array,
        $this->createArrayOfMediaData($media_package_file));
    }

    return JsonResponse::create($json_response_array);
  }


  /**
   * @Route("/api/media/category/json", name="api_media_lib_all_category",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function getCategories(Request $request)
  {
    /**
     * @var $categories array[MediaPackageCategory]
     */

    $em = $this->getDoctrine()->getManager();
    $categories = $em->getRepository(MediaPackageCategory::class)->findAll();

    if ($categories == [])
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message'    => "No category found.",
        ]
      );
    }
    $json_response_array = [];
    foreach ($categories as $category)
    {
      array_push($json_response_array, $this->createArrayOfCategory($category));
    }

    $flavor = $request->get('flavor');

    if ($flavor !== 'pocketcode')
    {
      $snowflake = [
        'id'   => PHP_INT_MAX,
        'name' => $flavor,
        'displayID' => str_replace(' ', '', $flavor)
      ];
      array_push($json_response_array, $snowflake);
    }

    return JsonResponse::create([
        'statusCode' => StatusCode::OK,
        'data'       => $json_response_array,
      ]
    );
  }


  /**
   * @Route("/api/media/category/{category}/json", name="api_media_lib_category",
   *   requirements={"category":"\w+"}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $category
   *
   * @return JsonResponse
   */
  public function getMediaFilesForCategory($category)
  {
    $em = $this->getDoctrine()->getManager();
    $json_response_array = [];


    $media_package_categories = $em->getRepository(MediaPackageCategory::class)
      ->findBy(
        [
          'name' => $category,
        ]);
    if ($media_package_categories === null || count($media_package_categories) <= 0)
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message'    => "category " . $category . " not found",
        ]
      );
    }

    foreach ($media_package_categories as $media_package_category)
    {
      /**
       * @var array|MediaPackageFile     $media_package_files
       *
       * @var array|MediaPackageCategory $media_package_category
       */
      $media_package_files = $media_package_category->getFiles();
      if ($media_package_files != null && count($media_package_files) > 0)
      {
        foreach ($media_package_files as $media_package_file)
        {
          array_push($json_response_array, $this->createArrayOfMediaData($media_package_file));
        }
      }
    }

    return JsonResponse::create([
        'statusCode' => StatusCode::OK,
        'data'       => $json_response_array,
      ]
    );
  }


  /**
   * @Route("/api/media/package/{package}/json", name="api_media_lib_package",
   *   requirements={"package":"\w+"}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $package
   *
   * @return JsonResponse
   */
  public function getMediaFilesForPackage($package)
  {
    $em = $this->getDoctrine()->getManager();
    $media_package = $em->getRepository('AppBundle:MediaPackage')
      ->findOneBy(['name' => $package]);
    if ($media_package == null)
    {
      return JsonResponse::create(
        ['statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
         'message'    => $package . " not found"]
      );
    }
    $json_response_array = [];
    /** @var array|MediaPackageCategory $media_package_categories */
    $media_package_categories = $media_package->getCategories();
    if ($media_package_categories == null || empty($media_package_categories))
    {
      return JsonResponse::create(
        $json_response_array
      );
    }
    foreach ($media_package_categories as $media_package_category)
    {
      /** @var array|MediaPackageFile $media_package_files */
      $media_package_files = $media_package_category->getFiles();
      if ($media_package_files != null && count($media_package_files) > 0)
      {
        foreach ($media_package_files as $media_package_file)
        {
          array_push($json_response_array, $this->createArrayOfMediaData($media_package_file));
        }
      }
    }

    return JsonResponse::create(
      $json_response_array
    );
  }


  /**
   * @Route("/api/media/package/{package}/{category}/json", name="api_media_lib_package_category",
   *   requirements={"package":"\w+", "category":"\w+"}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $package
   * @param $category
   *
   * @return JsonResponse
   */
  public function getMediaFilesForPackageAndCategory($package, $category)
  {
    $em = $this->getDoctrine()->getManager();
    $media_package = $em->getRepository('AppBundle:MediaPackage')
      ->findOneBy(['name' => $package]);
    if ($media_package == null)
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
          'message'    => $package . " not found"
        ]
      );
    }
    $json_response_array = [];
    $category_not_found = true;
    /** @var array|MediaPackageCategory $media_package_categories */
    $media_package_categories = $media_package->getCategories();
    if ($media_package_categories == null || empty($media_package_categories))
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message'    => "category " . $category . " not found in package " . $package . " because " .
            "the package doesn't contain any categories",
        ]
      );
    }
    foreach ($media_package_categories as $media_package_category)
    {
      // case insensitive:
      if (strcasecmp($media_package_category->getName(), $category) == 0)
        // case sensitive:
        // if ($media_package_category->getName() == $category)
      {
        $category_not_found = false;
        /** @var array|MediaPackageFile $media_package_files */
        $media_package_files = $media_package_category->getFiles();
        if ($media_package_files !== null && count($media_package_files) > 0)
        {
          foreach ($media_package_files as $media_package_file)
          {
            array_push($json_response_array, $this->createArrayOfMediaData($media_package_file));
          }
        }
      }
    }
    if ($category_not_found)
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message'    => "category " . $category . " not found in package " . $package,
        ]
      );
    }

    return JsonResponse::create(
      $json_response_array
    );
  }


  /**
   *
   * @Route("/api/media/file/{id}/json", name="api_media_lib_file", requirements={"id":"\d+"},
   *   defaults={"id" = 0, "_format": "json"}, methods={"GET"})
   *
   * @param $id
   *
   * @return JsonResponse
   */
  public function getSingleMediaFile($id)
  {
    if ($id == 0)
    {
      return JsonResponse::create(
        [
          'statusCode' => Response::HTTP_NOT_FOUND,
        ]
      );
    }
    $em = $this->getDoctrine()->getManager();
    $media_file = $em->getRepository('AppBundle:MediaPackageFile')
      ->find($id);
    if ($media_file == null)
    {
      return JsonResponse::create(
        [
          'statusCode' => Response::HTTP_NOT_FOUND,
        ]
      );
    }

    return JsonResponse::create(
      $this->createArrayOfMediaData($media_file)
    );
  }


  /**
   * @param $category MediaPackageCategory
   *
   * @return array
   */
  private function createArrayOfCategory($category)
  {
    $id = $category->getId();
    $name = $category->getName();

    return
      [
        'id'   => $id,
        'name' => $name,
        'displayID' => str_replace(' ', '', $name),
      ];
  }


  /**
   * @param $media_package_file MediaPackageFile
   *
   * @return array
   */
  private function createArrayOfMediaData($media_package_file)
  {
    /**
     * @var MediaPackageFile $media_package_file
     * @var MediaPackage $package
     */

    $id = $media_package_file->getId();
    $name = $media_package_file->getName();
    $flavor = $media_package_file->getFlavor();
    $package = $media_package_file->getCategory()->getPackage()->first()->getName();
    $category = $media_package_file->getCategory()->getName();
    $author = $media_package_file->getAuthor();
    $extension = $media_package_file->getExtension();
    $url = $media_package_file->getUrl();
    $download_url = $this->generateUrl('download_media',
      [
        'id' => $id,
      ]);

    return
      [
        'id'           => $id,
        'name'         => $name,
        'flavor'       => $flavor,
        'package'      => $package,
        'category'     => $category,
        'author'       => $author,
        'extension'    => $extension,
        'url'          => $url,
        'download_url' => $download_url,
      ];
  }
}