@web @authentication
Feature: Verify the email address

  People often register in the app and open the verification mail in another browser or mail app,
  where they are not logged in. The link must work there too.

  Background:
    Given there are users:
      | id | name  | verified |
      | 1  | Alice | false    |

  Scenario: The verification link works without being logged in
    When I open the email verification link for "Alice"
    And I wait for the page to be loaded
    And I log in as "Alice"
    And I am on "/app/"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: An unverified user who logs in is sent to the verify-pending page
    Given I log in as "Alice"
    And I am on "/app/"
    And I wait for the page to be loaded
    Then I should be on "/app/verify-pending"
