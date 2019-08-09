@homepage
Feature: There should be all projects of a user presented on a profile page

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | User2    | 654321   | cccccccccc | dev2@pocketcode.org |
      | User3    | 654321   | cccccccccc | dev3@pocketcode.org |
      | User4    | 654321   | cccccccccc | dev4@pocketcode.org |
    And there are programs:
      | id | name       | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | oldestProg | p1          | Catrobat | 3         | 2             | 12    | 01.01.2009 12:00 | 0.8.5   |
      | 2  | program 02 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 03 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | program 04 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | program 05 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | program 06 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7  | program 07 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8  | program 08 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | program 09 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | program 10 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11 | program 11 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12 | program 12 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13 | program 13 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14 | program 14 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15 | program 15 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16 | program 16 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17 | program 17 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18 | program 18 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19 | program 19 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20 | program 20 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21 | program 21 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22 | program 22 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 23 | program 23 |             | User3    | 1         |  1            | 1     | 01.01.2011 13:00 | 0.8.5   |

  Scenario: at my profile page there should always all programs be visible
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/profile"
    Then I should see 2 "#myprofile-programs .program"
    And the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist

  Scenario: at my profile page there should always all programs be visible
    Given I log in as "User2" with the password "654321"
    And I am on "/app/profile"
    Then I should see 20 "#myprofile-programs .program"
    And the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist

  Scenario: at a profile page there should always all programs be visible
    Given I am on "/app/profile/2"
    Then I should see 20 "#user-programs .program"
    And the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist

  Scenario: at a profile page there should always all programs be visible
    Given I am on "/app/profile/1"
    Then I should see 2 "#user-programs .program"
    And the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist
