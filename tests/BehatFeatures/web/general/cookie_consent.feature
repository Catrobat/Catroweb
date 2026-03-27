@web @cookie-consent
Feature: Cookie consent banner for GDPR compliance

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name    | owned by |
      | 1  | Minions | Catrobat |

  Scenario: Cookie consent banner is shown on first visit
    Given I am on homepage
    And I wait for the page to be loaded
    Then I wait for the element ".cookie-consent-banner" to be visible
    And the element ".cookie-consent-accept" should be visible
    And the element ".cookie-consent-decline" should be visible

  Scenario: Accepting cookies hides the banner and sets consent cookie
    Given I am on homepage
    And I wait for the page to be loaded
    And I wait for the element ".cookie-consent-banner" to be visible
    When I click ".cookie-consent-accept"
    And I wait 500 milliseconds
    Then the element ".cookie-consent-banner" should not exist
    And cookie "cookie_consent" with value "accepted" should exist"

  Scenario: Declining cookies hides the banner and sets consent cookie
    Given I am on homepage
    And I wait for the page to be loaded
    And I wait for the element ".cookie-consent-banner" to be visible
    When I click ".cookie-consent-decline"
    And I wait 500 milliseconds
    Then the element ".cookie-consent-banner" should not exist
    And cookie "cookie_consent" with value "declined" should exist"

  Scenario: Banner does not reappear after accepting cookies
    Given I set the cookie "cookie_consent" to "accepted"
    And I am on homepage
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then the element ".cookie-consent-banner" should not exist

  Scenario: Banner does not reappear after declining cookies
    Given I set the cookie "cookie_consent" to "declined"
    And I am on homepage
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then the element ".cookie-consent-banner" should not exist

  Scenario: Cookie settings link in footer resets consent and shows banner
    Given I set the cookie "cookie_consent" to "accepted"
    And I am on homepage
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then the element ".cookie-consent-banner" should not exist
    When I click ".js-cookie-settings"
    And I wait 500 milliseconds
    Then I wait for the element ".cookie-consent-banner" to be visible
