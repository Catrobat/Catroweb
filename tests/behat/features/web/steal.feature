@homepage
Feature: Steal program

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | user2    | 123456   | cccccccccc | dev2@pocketcode.org |  2 |

    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready |
      | 1  | program 1 | my superman description | Catrobat | 3         | 2             | 12    | 01.01.2014 12:00 | 0.8.5   | 0.94             | true    | true      |
      | 2  | program 2 | abcef                   | Catrobat | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | 3  | program 3 | abcef                   | user2    | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |

  Scenario: User is not logged in and should be forwarded to login page if the press on steal button
      Given I am on "/app/project/1"
      When I click "#apk-steal"
      Then I should be on "/app/login"

  Scenario: User is logged in and presses the steal button
    Given I log in as "user2" with the password "123456"
    And I am on "/app/project/1"
    And I click "#apk-steal"
    And I wait for a second
    And the "#icon-author" element should contain "user2"

  Scenario: User is logged in and presses the steal button on his own program
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/project/1"
    And I click "#apk-steal"
    And I wait for a second
    And the "#icon-author" element should contain "Catrobat"
