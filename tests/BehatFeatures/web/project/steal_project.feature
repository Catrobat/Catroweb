@homepage
Feature: Steal projects of other users

  Background:
    Given there are users:
      | id | name      |
      | 1  | Me        |
      | 2  | OtherUser |
    And there are projects:
      | id | name          | owned by  |
      | 1  | my project    | Me        |
      | 2  | other project | OtherUser |

  Scenario: When visiting another project I should see a steal button
    Given I log in as "Me"
    When I go to "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "other project"
    And I should see "Steal Project"

  Scenario: When visiting my project I should not see a steal button
    Given I log in as "Me"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "my project"
    And I should not see "Steal Project"

  Scenario: When not logged in I should not see a steal button
    When I go to "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "other project"
    And I should not see "Steal Project"

  Scenario: I should be able to steal another project
    Given I log in as "Me"
    When I go to "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "other project"
    And I should see "Steal Project"
    Then I click "#stealProjectButton"
    And I wait for the page to be loaded
    Then I should see "Me"
    And I should not see "Steal Project"


