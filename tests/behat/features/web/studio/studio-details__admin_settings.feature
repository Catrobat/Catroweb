@web @studio
Feature: As studio admin I must be able to configure a studio

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 1  | Catrobat    | 1         | member |

  Scenario: If I am not logged in I must not see the button to open the settings modal
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-settings" should not exist

  Scenario: If I am not the admin of the studio, I must not see the button to open the settings modal
    Given I log in as "Catrobat"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-settings" should not exist

  Scenario: If I am the admin of the studio, I have access to an settings modal
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-settings" should be visible
    Then I should not see "General Settings"
    When I click "#top-app-bar__btn-settings"
    And I wait for the page to be loaded
    Then I should see "General Settings"

  Scenario: As Studio admin I can change the studio name
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I should see "CatrobatStudio01"
    And the element "#studio-settings__submit-button" should not be visible
    When I click "#top-app-bar__btn-settings"
    And I wait for the page to be loaded
    And the element "#studio-settings__submit-button" should be visible
    When I fill in "studio_name" with "CatrobatStudio02"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And the element "#studio-settings__submit-button" should not be visible
    And I should not see "CatrobatStudio01"
    And I should see "CatrobatStudio02"

  Scenario: As Studio admin I can change the studio description
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I should see "hasADescription"
    And the element "#studio-settings__submit-button" should not be visible
    When I click "#top-app-bar__btn-settings"
    And I wait for the page to be loaded
    And the element "#studio-settings__submit-button" should be visible
    When I fill in "studio_description" with "hasANewDescription"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And the element "#studio-settings__submit-button" should not be visible
    And I should not see "hasADescription"
    And I should see "hasANewDescription"

  Scenario: The settings modal can be closed without saving
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I should see "CatrobatStudio01"
    And the element "#studio-settings__close-button" should not be visible
    When I click "#top-app-bar__btn-settings"
    And I wait for the page to be loaded
    Then the element "#studio-settings__close-button" should be visible
    When I fill in "studio_name" with "CatrobatStudio02"
    And I click "#studio-settings__close-button"
    And I wait 500 milliseconds
    Then the element "#studio-settings__close-button" should not be visible
    And I should see "CatrobatStudio01"

  Scenario: As Studio admin I can enable and disable comments (ToDo)
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And the element "#studio-settings__submit-button" should not be visible
    When I click "#top-app-bar__btn-settings"

  Scenario: As Studio admin I can toggle the privacy of a studio (ToDo)
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And the element "#studio-settings__submit-button" should not be visible
    When I click "#top-app-bar__btn-settings"