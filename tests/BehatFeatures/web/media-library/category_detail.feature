@homepage
Feature: Media library category detail
  In order to use assets in my projects
  As a user
  I want to browse a media category and select assets

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
    And there are media categories:
      | id                                   | name    | description   | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | Animals | Animal assets | 10       |
    And there are media assets:
      | id                                   | name             | file_type | category                             | flavors    |
      | 650e8400-e29b-41d4-a716-446655440077 | Dog Image        | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440066 | Luna Image       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | luna       |
      | 650e8400-e29b-41d4-a716-446655440055 | Cat Image        | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440024 | Animals Asset 24 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440023 | Animals Asset 23 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440022 | Animals Asset 22 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440021 | Animals Asset 21 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440020 | Animals Asset 20 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440019 | Animals Asset 19 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440018 | Animals Asset 18 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440017 | Animals Asset 17 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440016 | Animals Asset 16 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440015 | Animals Asset 15 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440014 | Animals Asset 14 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440013 | Animals Asset 13 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440012 | Animals Asset 12 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440011 | Animals Asset 11 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440010 | Animals Asset 10 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440009 | Animals Asset 09 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440008 | Animals Asset 08 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440007 | Animals Asset 07 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440006 | Animals Asset 06 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440005 | Animals Asset 05 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |
      | 650e8400-e29b-41d4-a716-446655440004 | Animals Asset 04 | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode |

  Scenario: Viewing a media category shows all default assets
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    Then I should see "Animals"
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440077"
    But I should not see media file with id "650e8400-e29b-41d4-a716-446655440066"

  Scenario: Viewing a media category shows all assets from a flavor + default
    Given I am on "/luna/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    Then I should see "Animals"
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440077"
    But I should see media file with id "650e8400-e29b-41d4-a716-446655440066"

  Scenario: Selecting and deselecting media files for downloading
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    And I click "#mediafile-650e8400-e29b-41d4-a716-446655440077"
    Then the element "#mediafile-650e8400-e29b-41d4-a716-446655440077" should have a attribute "class" with value "selected"
    And I click "#mediafile-650e8400-e29b-41d4-a716-446655440077"
    Then the element "#mediafile-650e8400-e29b-41d4-a716-446655440077" should have no attribute "class" with value "selected"

  Scenario: Clearing selection from the download bar
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    And I click "#mediafile-650e8400-e29b-41d4-a716-446655440077"
    And I click "#top-app-bar__btn-cancel-download-selection"
    Then the element "#mediafile-650e8400-e29b-41d4-a716-446655440077" should have no attribute "class" with value "selected"
    And the element "#top-app-bar__btn-download-selection" should not be visible

  Scenario: Searching within a category must filter the results
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440077"
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440055"
    And I click the currently visible search icon
    And I enter "Dog" into visible "#top-app-bar__search-input"
    And I click "#top-app-bar__search-form__submit"
    And I wait for the page to be loaded
    Then I wait 500 milliseconds
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440077"
    And I should not see media file with id "650e8400-e29b-41d4-a716-446655440055"

  Scenario: Searching within a category (no results)
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    And I enter "Snake" into visible "#top-app-bar__search-input"
    And I click "#top-app-bar__search-form__submit"
    And I wait for the page to be loaded
    Then I should see "No results found."

  Scenario: The category search label uses the media category text
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    Then the element "#top-app-bar__search-label" should have a attribute "alt" with value "Search in media category"

  Scenario: Category loads more assets on scroll
    Given I am on "/app/media-library/550e8400-e29b-41d4-a716-446655440001"
    And I wait for the page to be loaded
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440077"
    And I should not see media file with id "650e8400-e29b-41d4-a716-446655440004"
    And I scroll to the bottom of the page
    And I wait 500 milliseconds
    And I should see media file with id "650e8400-e29b-41d4-a716-446655440004"
