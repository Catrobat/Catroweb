@homepage
Feature: Using a release app I should not see debug programs

  Background:

    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
    And there are programs:
      | id | name          | owned by | downloads | views | upload time      | version | debug |
      | 1  | program 1     | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.9.10  | false |
      | 2  | program 2     | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.9.10  | false |
      | 3  | debug program | Catrobat | 450       | 80    | 01.04.2019 09:00 | 1.0.12  | true  |
      | 4  | program 4     | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.9.10  | false |

  Scenario: Viewing homepage with debug app
    Given I use a debug build of the Catroid app
    And I am on homepage
    Then I should see 1 "#newest #program-1"
    And I should see 1 "#newest #program-2"
    And I should see 1 "#newest #program-3"
    And I should see 1 "#newest #program-4"
    Then I should see 1 "#mostDownloaded #program-1"
    And I should see 1 "#mostDownloaded #program-2"
    And I should see 1 "#mostDownloaded #program-3"
    And I should see 1 "#mostDownloaded #program-4"
    Then I should see 1 "#mostViewed #program-1"
    And I should see 1 "#mostViewed #program-2"
    And I should see 1 "#mostViewed #program-3"
    And I should see 1 "#mostViewed #program-4"
    Then I should see 1 "#random #program-1"
    And I should see 1 "#random #program-2"
    And I should see 1 "#random #program-3"
    And I should see 1 "#random #program-4"

  Scenario: Viewing homepage with release app
    Given I use a release build of the Catroid app
    And I am on homepage
    Then I should see 1 "#newest #program-1"
    And I should see 1 "#newest #program-2"
    And I should not see "#newest #program-3"
    And I should see 1 "#newest #program-4"
    Then I should see 1 "#mostDownloaded #program-1"
    And I should see 1 "#mostDownloaded #program-2"
    And I should not see "#mostDownloaded #program-3"
    And I should see 1 "#mostDownloaded #program-4"
    Then I should see 1 "#mostViewed #program-1"
    And I should see 1 "#mostViewed #program-2"
    And I should not see "#mostViewed #program-3"
    And I should see 1 "#mostViewed #program-4"
    Then I should see 1 "#random #program-1"
    And I should see 1 "#random #program-2"
    And I should not see "#random #program-3"
    And I should see 1 "#random #program-4"

  Scenario: Viewing profile with debug app
    Given I use a debug build of the Catroid app
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/profile"
    Then I should see "program 1"
    And I should see "program 2"
    And I should see "debug program"
    And I should see "program 4"

  Scenario: Viewing profile with release app
    Given I use a release build of the Catroid app
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/profile"
    Then I should see "program 1"
    And I should see "program 2"
    And I should not see "debug program"
    And I should see "program 4"

  Scenario: Viewing program marked as debug using debug app
    Given I use a debug build of the Catroid app
    And I am on "/app/program/3"
#    Then the response status code should be 200
    And I should see "debug program"

  Scenario: Viewing program marked as debug using release app
    Given I use a release build of the Catroid app
    And I am on "/app/program/3"
#    Then the response status code should be 404
    And I should not see "debug program"
    And I should see "Ooooops something went wrong"
