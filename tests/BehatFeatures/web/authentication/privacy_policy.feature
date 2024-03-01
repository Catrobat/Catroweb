@web @security
Feature: Privacy policy feature

  Scenario: Privacy policy url should be visible
    Given  I am on "/app/privacypolicy"
    And I wait for the page to be loaded
    Then I should be on "https://developer.catrobat.org/pages/legal/policies/privacy/"

  Scenario: I should be informed about the privacy policy during registration
    Given I am on "/app/register"
    And I wait for the page to be loaded
    Then I should see "By continuing, you are setting up a Catrobat community account and agree to our Privacy Policy and Terms Of Use"

  Scenario: I should be informed about the privacy policy during registration
    Given I am on "/app/register"
    And I wait for the page to be loaded
    When I click "#privacyPolicy"
    Given I should be on "https://developer.catrobat.org/pages/legal/policies/privacy/"