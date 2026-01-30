@homepage
Feature: Media library overview
  In order to find assets quickly
  As a user
  I want to browse and search the media library overview

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
    And there are media categories:
      | id                                   | name     | description         | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | Animals  | Animal assets       | 10       |
      | 550e8400-e29b-41d4-a716-446655440002 | Sounds   | Sound assets        | 20       |
      | 550e8400-e29b-41d4-a716-446655440003 | Space    | Space assets        | 5        |
      | 550e8400-e29b-41d4-a716-446655440004 | Plants   | Plant assets        | 30       |
      | 550e8400-e29b-41d4-a716-446655440005 | Sports   | Sports assets       | 40       |
      | 550e8400-e29b-41d4-a716-446655440006 | Food     | Food assets         | 50       |
      | 550e8400-e29b-41d4-a716-446655440007 | Weather  | Weather assets      | 60       |
      | 550e8400-e29b-41d4-a716-446655440008 | Buildings| Building assets     | 70       |
      | 550e8400-e29b-41d4-a716-446655440009 | People   | People assets       | 80       |
      | 550e8400-e29b-41d4-a716-446655440010 | Tools    | Tool assets         | 90       |
      | 550e8400-e29b-41d4-a716-446655440011 | Vehicles | Vehicle assets      | 100      |
      | 550e8400-e29b-41d4-a716-446655440012 | Underwater | Underwater assets | 110      |
    And there are media assets:
      | id                                   | name      | extension | file_type | category                             | flavors    | downloads | author |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 10        |        |
      | 650e8400-e29b-41d4-a716-446655440002 | Meow      | mp3       | SOUND     | 550e8400-e29b-41d4-a716-446655440002 | pocketcode | 5         |        |
      | 650e8400-e29b-41d4-a716-446655440003 | Animals Asset 10 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440004 | Animals Asset 01 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440005 | Animals Asset 02 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440006 | Animals Asset 03 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440007 | Animals Asset 04 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440008 | Animals Asset 05 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440009 | Animals Asset 06 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440010 | Animals Asset 07 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440011 | Animals Asset 08 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440012 | Animals Asset 09 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440013 | Animals Asset 11 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |
      | 650e8400-e29b-41d4-a716-446655440014 | Animals Asset 12 | png | IMAGE | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 1 |        |

  Scenario: Viewing defined categories on overview page
    Given I am on "/app/media-library"
    And I wait for the page to be loaded
    Then I should see "Animals"
    And I should see "Sounds"
    And I should see "Space"
    And I should see "View all Animals"

  Scenario: Overview loads more categories on scroll
    Given I am on "/app/media-library"
    And I wait for the page to be loaded
    And I scroll to the bottom of the page
    And I wait for the page to be loaded
    Then I should see "Vehicles"
    And I should see "Underwater"
    And I should see "Animals"

  Scenario: Overview loads more assets in a category row
    Given I am on "/app/media-library"
    And I wait for the page to be loaded
    And I scroll horizontal on "categories-container" "media-assets-row" using a value of "2000"
    And I wait 500 milliseconds
    Then I should see "Animals Asset 10"

  Scenario: Searching the overview filters categories
    Given I am on "/app/media-library"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    And I enter "Dog" into visible "#top-app-bar__search-input"
    And I click "#top-app-bar__search-form__submit"
    And I wait for the page to be loaded
    Then I should see "Animals"
    And I should see "Dog Image"
    And I should not see "Sounds"

  Scenario: Searching the overview with no results
    Given I am on "/app/media-library"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    And I enter "Snake" into visible "#top-app-bar__search-input"
    And I click "#top-app-bar__search-form__submit"
    And I wait for the page to be loaded
    Then I should see "No results found."

  Scenario: The overview search label uses the media library text
    Given I am on "/app/media-library"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    Then the element "#top-app-bar__search-label" should have a attribute "alt" with value "Search in media library"
