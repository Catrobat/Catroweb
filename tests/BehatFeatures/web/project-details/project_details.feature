@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | Catrobat2 |
    And there are projects:
      | id | name      | downloads | owned by | views | apk_ready | upload time      |
      | 1  | project 1 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 |
    And I start a new session

  Scenario: Showing statistics on project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "Catrobat"
    And I should see "more than one year ago"
    And I should see "0.00 MB"
    And I should see "5 downloads"
    And I should see "42 views"

  Scenario: Downloading a project is possible
    When I download "/api/project/1/catrobat"
    Then I should receive an application file

  @disabled
  Scenario: Downloading a project apk is possible
    When I want to download the apk file of "project 1"
    Then I should receive the apk file

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
  Scenario: Increasing download counter after download only once! (APK)
    Given I log in as "Catrobat2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "5 downloads"
    When I click "#projectApkDownloadButton-small"
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"
    When I start a new session
    And I log in as "Catrobat2"
    And I am on "/app/project/1"
    When I click "#projectApkDownloadButton-small"
    And I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"
    When I click "#projectDownloadButton-small"
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "6 downloads"

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
    Then I should see "42 views"
    When I start a new session
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "43 views"

  Scenario: View counter is not increased on same session
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "42 views"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "43 views"


  Scenario: User clicks on the steal button
    Given I log in as "Catrobat2"
    And I am on "/app/project/1"
    And I should see "Catrobat"
    When I press "Steal"
    Then I should see "Catrobat2"
