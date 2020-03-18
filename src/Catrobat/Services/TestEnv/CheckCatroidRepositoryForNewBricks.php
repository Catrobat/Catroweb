<?php

namespace App\Catrobat\Services\TestEnv;

use Exception;

class CheckCatroidRepositoryForNewBricks
{
  /**
   * @throws Exception
   */
  public static function check()
  {
    $url1 = 'https://raw.githubusercontent.com/Catrobat/Catroid/develop/catroid/src/main/java/org/catrobat/catroid/io/XstreamSerializer.java';
    $url2 = 'https://raw.githubusercontent.com/Catrobat/Catroid/develop/catroid/src/main/java/org/catrobat/catroid/ui/fragment/CategoryBricksFactory.java';
    $catroid_data_set_1 = file_get_contents($url1);
    $catroid_data_set_2 = file_get_contents($url2);

    if (!$catroid_data_set_1)
    {
      throw new Exception('XstreamSerializer.java could not be downloaded');
    }

    if (!$catroid_data_set_2)
    {
      throw new Exception('CategoryBricksFactory.java could not be downloaded');
    }

    // Filtering out all those Scripts and Bricks that are used by the catroid app
    $search_pattern = '/import org.catrobat.catroid.*?(([^\\.]+)(Brick|Script))/';
    preg_match_all($search_pattern, $catroid_data_set_1, $match1);
    preg_match_all($search_pattern, $catroid_data_set_2, $match2);

    $our_fallback_bricks = ['UnknownBrick', 'UnknownScript'];

    // bricks already deprecated by CATROID - we should still support them for statistics of old projects
    $deprecated_bricks = ['CollisionScript', 'LoopEndlessBrick'];

    // combining found bricks and scripts with our fallback/deprecated bricks and scripts
    $all_catroid_bricks_and_scripts = array_unique(
      array_merge($match1[1], $match2[1], $our_fallback_bricks, $deprecated_bricks)
    );
    asort($all_catroid_bricks_and_scripts);

    $path = './src/Catrobat/Services/CatrobatCodeParser/Constants.php';
    $catroweb_data_set = file_get_contents($path);

    if (!$catroweb_data_set)
    {
      throw new Exception("Constants.php could'nt be read");
    }

    // Filtering out all those Scripts and Bricks that are defined by us
    $search_pattern = "/const .*? = ('|\")((.+)(Brick|Script))('|\")/";
    preg_match_all($search_pattern, $catroweb_data_set, $match3);

    $all_catroweb_bricks_and_scripts = array_unique($match3[2]);
    asort($all_catroweb_bricks_and_scripts);

    // Comparing Blocks and Scripts between catroid and catroweb
    $diff_result_not_in_app_but_web = array_diff($all_catroweb_bricks_and_scripts, $all_catroid_bricks_and_scripts);
    $diff_result_not_in_web_but_app = array_diff($all_catroid_bricks_and_scripts, $all_catroweb_bricks_and_scripts);

    if (count($diff_result_not_in_web_but_app) > 0 || count($diff_result_not_in_app_but_web) > 0)
    {
      $message = '';

      $message .= count($diff_result_not_in_app_but_web) > 0 ?
        "These blocks are only defined in catroweb but not in the app: \n".
        print_r($diff_result_not_in_app_but_web, true)."\n".

        '//'."\n".
        '// For new deprecated bricks: '."\n".
        '//'."\n".
        "// 1) Set the correct Brick/Scripts classes image to 'Constants::DEPRECATED_SCRIPT_IMG'"."\n".
        "//    The block should from now count to the 'other' category."."\n".
        '//    E.g look at src/Catrobat/Services/CatrobatCodeParser/Scripts/CollisionScript.php'."\n".
        '//'."\n".
        '// 2) Finally add it in this test to the deprecated_bricks array'."\n".
        '//'."\n\n" : '';

      $message .= count($diff_result_not_in_web_but_app) > 0 ?
        "These blocks are only defined in catroid but not in the web: \n".
        print_r($diff_result_not_in_web_but_app, true)."\n".
        '//'."\n".
        '// For new bricks - Add the Brick/Script! '."\n".
        '//'."\n".
        '// 1) Create necessary Constants in src/Catrobat/Services/CatrobatCodeParser/Constants.php'."\n".
        '//'."\n".
        '// 2) Create the new brick/script class in src/Catrobat/Services/CatrobatCodeParser/(Scripts|Bricks)'."\n".
        "//    Make sure to set the correct img Constant -> that's defining the category of this new block"."\n".
        '//'."\n".
        '// 3) If it is a NEW img_constant(=category) make sure to add the new case in'."\n".
        '//    src/Catrobat/Services/CatrobatCodeParser/CodeStatistic.php  updateBrickStatistic(...)'."\n".
        '//'."\n".
        '// 4) Make sure to add the new case in'."\n".
        '//    src/Catrobat/Services/CatrobatCodeParser/Bricks/BrickFactory.php generate(...) or'."\n".
        '//    src/Catrobat/Services/CatrobatCodeParser/Scripts/ScriptFactory.php generate(...)'."\n".
        '//'."\n".
        '// 5) Write a test to check the code statistics of the new block script!!'."\n".
        '//    -> Create project with app (E.g. upload & download it)'."\n".
        '//    put project file into tests/testdata/DataFixtures/CodeStatistics/'."\n".
        '//    write the test in tests/behat/features/web/code_statistics.feature'."\n".
        '//'."\n".
        '// Now should not only this test case work, the new block is implemented and tested in catroweb'."\n".
        '//'."\n\n" : '';

      throw new Exception($message);
    }
  }
}
