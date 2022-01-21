@web @security
Feature: Terms of Use feature

  Scenario: Terms of Use url should be visible
    Given  I am on "/app/termsOfUse"
    And I wait for the page to be loaded
    Then I should see "Terms Of Use"

  Scenario: I should be informed about the Terms of Use during registration
    Given I am on "/app/register"
    And I wait for the page to be loaded
    Then I should see "By continuing, you are setting up a Pocketcode account and agree to our Privacy Policy and Terms of Use."

  Scenario: I should be informed about the Terms of Use during registration
    Given I am on "/app/register"
    And I wait for the page to be loaded
    When I click "#termsOfUse"
    Given I should be on "/app/termsOfUse"