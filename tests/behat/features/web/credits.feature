@homepage
Feature: As a program owner, I should be able to give credits for my program.

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | Gregor   | 123456   | cccccccccc | dev2@pocketcode.org |  2 |

    And there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | cccccccccc | admin@pocketcode.org |  3 |

    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready |
      | 1  | program 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      |
      | 2  | program 2 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | 3  | program 3 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |

  Scenario: There should be a credits section on every program page
    Given I am on "/app/project/1"
    Then I should see "Credits"
    And I wait 100 milliseconds
    And the element "#edit-credits-button" should not exist

  Scenario: A button for editing credits should be visible, if I am the owner of the program
    Given I log in as "Superman" with the password "123456"
    And I am on "/app/project/1"
    And I should see "Credits"
    And I wait 100 milliseconds
    And the element "#edit-credits-button" should be visible

  Scenario: A button for editing credits should be not exist, if I am not the owner of the program
    Given I log in as "Superman" with the password "123456"
    And I am on "/app/project/3"
    And I should see "Credits"
    And I wait 100 milliseconds
    And the element "#edit-credits-button" should not exist

   Scenario: If I press the edit credits button, a textarea should appear in which I can write my credits
     Given I log in as "Superman" with the password "123456"
     And I am on "/app/project/1"
     And I should see "Credits"
     And I wait 100 milliseconds
     And the element "#edit-credits-button" should be visible
     And I click "#edit-credits-button"
     And I wait 100 milliseconds
     Then the element "#edit-credits" should be visible

  Scenario: If I press the edit credits button, a textarea should appear in which I can write my credits
    Given I log in as "Superman" with the password "123456"
    And I am on "/app/project/1"
    And I should see "Credits"
    And I wait 100 milliseconds
    And the element "#edit-credits-button" should be visible
    And I click "#edit-credits-button"
    And I wait 100 milliseconds
    Then the element "#edit-credits" should be visible


  Scenario: I should be able to write new credits, if I am the owner of the program
    Given I log in as "Superman" with the password "123456"
    And I am on "/app/project/1"
    And I should see "Credits"
    And I wait 100 milliseconds
    And the element "#edit-credits-button" should be visible
    And I click "#edit-credits-button"
    And I wait 100 milliseconds
    Then the element "#edit-credits" should be visible
    Then I write "This is a credit" in textarea
    Then I click "#edit-credits-submit-button"
    And I am on "/app/project/1"
    Then I should see "This is a credit"


