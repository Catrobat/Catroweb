@web @profile_page
Feature: Suspended user sees a banner on their profile page with appeal option

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |

  Scenario: Suspended user sees suspension banner on own profile
    Given the user "Catrobat" profile is hidden
    And I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "has been suspended due to community reports"

  Scenario: Suspended user sees appeal button on own profile
    Given the user "Catrobat" profile is hidden
    And I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then the element "#btn-appeal-user" should be visible

  Scenario: Non-suspended user does not see suspension banner
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "has been suspended due to community reports"
    And the element "#btn-appeal-user" should not exist
