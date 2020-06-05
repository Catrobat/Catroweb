@web @recommendations
Feature: There should be a more from this user category on project pages

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
      | 3  | User3    |
      | 4  | User4    |
    And there are projects:
      | id | name       | owned by |
      | 1  | oldestProg | Catrobat |
      | 2  | project 02 | Catrobat |
      | 3  | project 03 | User2    |
      | 4  | project 04 | User2    |
      | 5  | project 05 | User2    |
      | 6  | project 06 | User2    |
      | 7  | project 07 | User2    |
      | 8  | project 08 | User2    |
      | 9  | project 09 | User2    |
      | 10 | project 10 | User2    |
      | 11 | project 11 | User2    |
      | 12 | project 12 | User2    |
      | 13 | project 13 | User2    |
      | 14 | project 14 | User2    |
      | 15 | project 15 | User2    |
      | 16 | project 16 | User2    |
      | 17 | project 17 | User2    |
      | 18 | project 18 | User2    |
      | 19 | project 19 | User2    |
      | 20 | project 20 | User2    |
      | 21 | project 21 | User2    |
      | 22 | project 22 | User2    |
      | 23 | project 23 | User3    |

  Scenario: at a projects detail page I should get more projects from this user recommended
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then I should see 6 "#more-from-this-user-recommendations .program"
    And I should see "More from user2"
    And the element "#more-from-this-user-recommendations .button-show-more" should be visible
    But the element "#more-from-this-user-recommendations .button-show-less" should not be visible
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait for AJAX to finish
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait for AJAX to finish
    Then I should see 18 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait for AJAX to finish
    Then I should see 19 "#more-from-this-user-recommendations .program"
    And the element "#more-from-this-user-recommendations .button-show-less" should be visible
    But the element "#more-from-this-user-recommendations .button-show-more" should not be visible
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait for AJAX to finish
    Then I should see 18 "#more-from-this-user-recommendations .program"
    And the element "#more-from-this-user-recommendations .button-show-more" should be visible
    And the element "#more-from-this-user-recommendations .button-show-less" should be visible

  Scenario: Show more from a user should not show the same project
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see 1 "#more-from-this-user-recommendations .program"
    And I should see "More from Catrobat"
    But the element "#more-from-this-user-recommendations .button-show-more" should not be visible
    And the element "#more-from-this-user-recommendations .button-show-less" should not be visible

  Scenario: Show more from a user should not show the same project. If it's the only project of this user there is no
  show more from a user category
    And I am on "/app/project/23"
    And I wait for the page to be loaded
    Then I should see 0 "#more-from-this-user-recommendations .program"
    And I should see "project 23"
    But I should not see "More from"

  Scenario: Not showing more programs when it's your own program
    Given I log in as "Catrobat"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should not see "More from"
    But I should see 0 "#more-from-this-user-recommendations .program"

  Scenario: When a user has loaded more projects the number of loaded projects should be stored in the session
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then I should see 6 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait for AJAX to finish
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I am on "app/help"
    And I wait for the page to be loaded
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait for AJAX to finish
    Then I should see 18 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-more"
    And I wait for AJAX to finish
    Then I should see 19 "#more-from-this-user-recommendations .program"
    When I reload the page
    And I wait for AJAX to finish
    Then I should see 19 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait for AJAX to finish
    Then I should see 18 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait for AJAX to finish
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I move backward one page
    And I wait for the page to be loaded
    When I move forward one page
    And I wait for the page to be loaded
    Then I should see 12 "#more-from-this-user-recommendations .program"
    When I click "#more-from-this-user-recommendations .button-show-less"
    And I wait for AJAX to finish
    Then I should see 6 "#more-from-this-user-recommendations .program"
