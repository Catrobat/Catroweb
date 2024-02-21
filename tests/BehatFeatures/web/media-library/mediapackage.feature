@homepage
Feature:
  In order to speed up the creation of a pocketcode project
  As UX/Design team
  We want to offer the user a library of assets to work with


  Background:
    Given there are media packages:
      | id | name   | name_url |
      | 1  | Looks  | looks    |
      | 2  | Sounds | sounds   |
    And there are media package categories:
      | id | name         | package |
      | 1  | Animals      | Looks   |
      | 2  | Fantasy      | Sounds  |
      | 3  | Bla          | Looks   |
      | 4  | ThemeSpecial | Looks   |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |

    And there are media package files:
      | id | name       | category     | extension | active | file       | flavors    | author        |
      | 1  | Dog (😊🐶) | Animals      | png       | 1      | 1.png      | pocketcode | Bob Schmidt   |
      | 2  | Bubble     | Fantasy      | mpga      | 1      | 2.mpga     | pocketcode |               |
      | 3  | SexyGrexy  | Bla          | png       | 0      | 3.png      | luna       | Micheal John  |
      | 4  | SexyFlavor | Animals      | png       | 1      | 4.png      | luna       |               |
      | 5  | SexyNULL   | Animals      | png       | 1      | 5.png      | pocketcode |               |
      | 6  | SexyWolfi  | Animals      | png       | 1      | 6.png      | pocketcode | Jenifer Shawn |
      | 7  | MyLuna     | ThemeSpecial | png       | 1      | 7.png      | luna       |               |
      | 8  | MyObject   | Bla          | catrobat  | 1      | 8.catrobat | pocketcode |               |

  Scenario: Viewing defined categories in a specific package
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    Then I should see "Animals"

  Scenario: When viewing a media package category the project navigation in the nav sidebar should be hidden
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    But I should not see a "#project-navigation" element

  Scenario: Viewing only media files for the pocketcode flavor
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    Then I should see media file with id "1"
    And I should see media file with id "5"
    And I should see media file with id "6"
    And I should see media file with id "8"
    But I should not see media file with id "4"
    And I should not see media file with id "7"
    And I should not see a "#category-theme-special" element

  Scenario: When using Luna-Flavor, there should be a theme-special category
    Given I am on "/luna/media-library/looks"
    And I wait for the page to be loaded
    Then I should see a "#category-theme-special" element
    And I should see media file with id 7 in category "Luna & Cat Theme Special"
    And I should see 1 media file in category "Luna & Cat Theme Special"
    And I should see 2 media file in category "Bla"

  Scenario: Selecting and deselecting media files for downloading
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    And I click "#mediafile-1"
    Then the element "#mediafile-1" should have a attribute "class" with value "selected"
    And I click "#mediafile-5"
    Then the element "#mediafile-5" should have a attribute "class" with value "selected"
    Then the element "#top-app-bar__btn-download-selection" should be visible
    And the "#top-app-bar__download-nr-selected" element should contain "2"
    And I click "#mediafile-1"
    Then the element "#mediafile-1" should have no attribute "class" with value "selected"
    And I click "#mediafile-5"
    Then the element "#mediafile-5" should have no attribute "class" with value "selected"
    Then the element "#top-app-bar__btn-download-selection" should not be visible
    And the "#top-app-bar__download-nr-selected" element should contain "0"

  Scenario: Pressing the x button in the download bar should delete the selection
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    And I click "#mediafile-1"
    And I click "#mediafile-5"
    And I click "#top-app-bar__btn-cancel-download-selection"
    Then the element "#mediafile-1" should have no attribute "class" with value "selected"
    Then the element "#mediafile-5" should have no attribute "class" with value "selected"
    And the element "#top-app-bar__btn-download-selection" should not be visible

  Scenario: Downloading multiple selected files
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    And I click "#mediafile-1"
    And I click "#mediafile-5"
    And I click "#top-app-bar__btn-download-selection"
    Then the element "#top-app-bar__btn-download-selection" should not be visible
#   Disabled for the moment due to problems with github/docker/shared volumes
#   Then I should have downloaded a file named "Dog (-).png"
#   Then I should have downloaded a file named "SexyNULL.png"


  Scenario: Searching the Media Package "looks" with the Pocketcode app and the search term "Snake" should result in an empty result.
    Given I am on "/app/media-library/Looks"
    And I wait for the page to be loaded
    Then I click "#top-app-bar__btn-search"
    And I enter "Snake" into visible "#top-app-bar__search-input"
    And I press enter in the search bar
    And I wait for the page to be loaded
    Then I should be on "/app/media-library/Looks/search/Snake"
    And I should see "Your search returned 0 results"

  Scenario: The search label in the media library should be different than to the normal search
    Given I am on "/app/media-library/Looks"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    Then the element "#top-app-bar__search-label" should have a attribute "alt" with value "Search in media library"
    

