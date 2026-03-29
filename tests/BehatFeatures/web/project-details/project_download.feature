@web @project_page
Feature: As a visitor I want to be able to download projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | downloads | owned by | apk_ready |
      | 1  | project 1 | 5         | Catrobat | true      |
      | 2  | project 2 | 5         | Catrobat | true      |

  Scenario: I want to download a project via the button
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And I click "#projectDownloadButton-small"
    And I wait 500 milliseconds
    Then the element "#downloadProgress-small" should be visible
    And I should see "Downloading"

  @disabled
  Scenario: If download fails user should see popup and the file should not be downloaded | not testable because of timing issues
    When project "1" is missing its files
    And project "2" is missing its files
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And I click "#projectDownloadButton-small"
    And I wait for AJAX to finish
    And I wait 500 milliseconds
    Then the element "#share-snackbar" should be visible
    And I should see "Error occurred while downloading the project"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And I click "#projectDownloadButton-small"
    And I wait for AJAX to finish
    And I wait 500 milliseconds
    Then the element "#share-snackbar" should be visible
    And I should see "Error occurred while downloading the project"

  @disabled
  Scenario: Clicking the download button should show progress and hide the download button
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#projectDownloadButton-small"
    Then the element "#downloadProgress-small" should be visible
    And the element "#projectDownloadButton-small" should not be visible
