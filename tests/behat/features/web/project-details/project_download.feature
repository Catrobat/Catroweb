@web @project_page
Feature: As a visitor I want to be able to download projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | downloads | owned by | apk_ready |
      | 1  | project 1 | 5         | Catrobat | true      |
      | 2  | project 2 | 5         | Catrobat | true      |

  Scenario: I want to download a project via the button
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And I click "#projectDownloadButton-small"
    And I wait 150 milliseconds
    And the element "#share-snackbar" should be visible
    And I should not see "Error occurred while downloading the project"
    And I should see "Download has started..."

  @disabled
  Scenario: If download fails user should see popup and the file should not be downloaded | not testable because of timing issues
    When project "1" is missing its files
    And project "2" is missing its files
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And the element "#share-snackbar" should not be visible
    And I click "#projectDownloadButton-small"
    And I wait 150 milliseconds
    Then the element "#share-snackbar" should be visible
    And I should see "Error occurred while downloading the project"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And I click "#projectDownloadButton-small"
    And I wait 150 milliseconds
    Then the element "#share-snackbar" should be visible
    And I should see "Error occurred while downloading the project"

  @disabled
  Scenario: Clicking the download button should deactivate the download button until download is finished
    # Disabled due to its flakiness
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#projectDownloadButton-small"
    Then the element "#download-progressbar-small" should be visible
    Then the button "#projectDownloadButton-small" should be disabled until download is finished
