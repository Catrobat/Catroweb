@web @project_upload
Feature: Project upload page allows users to upload .catrobat files

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: Not-logged-in user is redirected to login
    Given I am on "/app/project/upload"
    And I wait for the page to be loaded
    Then I should be on "/app/login"

  Scenario: Logged-in user sees the upload form with drop zone
    Given I log in as "Catrobat"
    And I am on "/app/project/upload"
    And I wait for the page to be loaded
    Then the element "#upload-drop-zone" should be visible
    And the element "#upload-file-input" should exist
    And the element "#upload-submit" should be visible
