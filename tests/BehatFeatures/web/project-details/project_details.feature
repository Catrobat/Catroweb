@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | Catrobat2 |
    And there are projects:
      | id | name      | downloads | owned by | views | upload time      |
      | 1  | project 1 | 5         | Catrobat | 42    | 01.01.2013 12:00 |
    And I start a new session

  Scenario: Showing statistics on project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then I should see "project 1"
    And I should see "Catrobat"
    And I wait for the element "#project-age" to contain "more than one year ago"
    And I should see "more than one year ago"
    And I should see "0.00 MB"
    And I should see "5 downloads"
    And I should see "43 views"

  Scenario: Downloading a project is possible
    When I download "/api/projects/1/catrobat"
    Then I should receive an application file

  @disabled
  Scenario: Download counter must not increase of not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "5 downloads"
    When I click "#projectDownloadButton-small"
    And I reload the page
    Then I should see "5 downloads"
    When I click "#projectApkDownloadButton-small"
    And I reload the page
    And I wait for the page to be loaded
    Then I should see "5 downloads"

  @disabled
  Scenario: Increasing download counter after download only once!
    Given I log in as "Catrobat2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "5 downloads"
    When I click "#projectDownloadButton-small"
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"
    When I start a new session
    And I log in as "Catrobat2"
    And I am on "/app/project/1"
    When I click "#projectDownloadButton-small"
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"
    When I click "#projectApkDownloadButton-small"
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"

  Scenario: Increasing view counter after new session page visit
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#details-views" to contain "43 views"
    Then I should see "43 views"
    When I start a new session
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#details-views" to contain "44 views"
    Then I should see "44 views"

  Scenario: View counter is not increased on same session
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#details-views" to contain "43 views"
    Then I should see "43 views"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#details-views" to contain "43 views"
    Then I should see "43 views"
