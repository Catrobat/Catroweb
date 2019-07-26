@homepage
Feature: There should be a more from this user category on project pages

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
    And I start a new session

  Scenario: at a projects detail page I should get more projects from this user recommended
  Given I am on "/pocketcode/program/3"
  Then I should see 6 "#more-from-this-user-recommendations .program"
  And I should see "More from user2"
  And the element "#more-from-this-user-recommendations .button-show-more" should be visible
  And the element "#more-from-this-user-recommendations .button-show-less" should not be visible
  When I click "#more-from-this-user-recommendations .button-show-more"
  And I wait 100 milliseconds
  Then I should see 12 "#more-from-this-user-recommendations .program"
  When I click "#more-from-this-user-recommendations .button-show-more"
  And I wait 100 milliseconds
  Then I should see 18 "#more-from-this-user-recommendations .program"
  When I click "#more-from-this-user-recommendations .button-show-more"
  And I wait 100 milliseconds
  Then I should see 19 "#more-from-this-user-recommendations .program"
  And the element "#more-from-this-user-recommendations .button-show-more" should not be visible
  And the element "#more-from-this-user-recommendations .button-show-less" should be visible
  When I click "#more-from-this-user-recommendations .button-show-less"
  And I wait 100 milliseconds
  Then I should see 18 "#more-from-this-user-recommendations .program"
  And the element "#more-from-this-user-recommendations .button-show-more" should be visible
  And the element "#more-from-this-user-recommendations .button-show-less" should be visible

  Scenario: Show more from a user should not show the same project
    And I am on "/pocketcode/program/1"
    Then I should see 1 "#more-from-this-user-recommendations .program"
    And I should see "More from Catrobat"
    And the element "#more-from-this-user-recommendations .button-show-more" should not be visible
    And the element "#more-from-this-user-recommendations .button-show-less" should not be visible

  Scenario: Show more from a user should not show the same project. If it's the only project of this user there is no
            show more from a user category
    And I am on "/pocketcode/program/23"
    Then I should see 0 "#more-from-this-user-recommendations .program"
    And I should see "program 23"
    And I should not see "More from"

  Scenario: When a user has loaded more projects the number of loaded projects should be stored in the session
    Given I am on "/pocketcode/program/3"
    And I wait 100 milliseconds
    Then I should see 6 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait 100 milliseconds
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I am on "pocketcode/help"
    And I wait 100 milliseconds
    And I am on "/pocketcode/program/3"
    And I wait 100 milliseconds
    Then I should see 12 "#more-from-this-user-recommendations .program"
    And I wait 100 milliseconds
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait 100 milliseconds
    Then I should see 18 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait 100 milliseconds
    Then I should see 19 "#more-from-this-user-recommendations .program"
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 19 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait 100 milliseconds
    Then I should see 18 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait 100 milliseconds
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I move backward one page
    When I move forward one page
    And I wait 100 milliseconds
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait 100 milliseconds
    Then I should see 6 "#more-from-this-user-recommendations .program"
