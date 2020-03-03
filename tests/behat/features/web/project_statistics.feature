@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | downloads | owned by | views | apk_ready | upload time      |
      | 1  | project 1 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 |

  Scenario: Viewing project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "Catrobat"
    And I should see "more than one year ago"
    And I should see "0.00 MB"
    And I should see "5 downloads"
    And I should see "43 views"
    And I should see "0 remixes"

  Scenario: Increasing download counter after an APK download
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "5 downloads"
    When I want to download the apk file of "project 1"
    Then I should receive the apk file
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"

  Scenario: Increasing download counter after download
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "5 downloads"
    When I download "/app/download/1.catrobat"
    Then I should receive an application file
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"

  Scenario: Increasing download counter after download
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "43 views"
    When I start a new session
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "44 views"
