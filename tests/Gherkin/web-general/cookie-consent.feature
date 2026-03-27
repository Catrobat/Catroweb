@web-general @dataset-minimal
Feature: Cookie consent banner
  Scenario: Banner is shown on the first visit
    When I open the "homepage" page
    Then the cookie banner should be visible

  Scenario: Accepting cookies stores consent
    When I open the "homepage" page
    And I accept cookies
    Then the cookie banner should not be visible
    And the "cookie_consent" cookie should be "accepted"

  Scenario: Declining cookies stores consent
    When I open the "homepage" page
    And I decline cookies
    Then the cookie banner should not be visible
    And the "cookie_consent" cookie should be "declined"

  Scenario: Accepted consent suppresses the banner
    Given cookie consent is "accepted"
    When I open the "homepage" page
    Then the cookie banner should not be visible

  Scenario: Declined consent suppresses the banner
    Given cookie consent is "declined"
    When I open the "homepage" page
    Then the cookie banner should not be visible

  Scenario: Cookie settings reopen the banner
    Given cookie consent is "accepted"
    When I open the "homepage" page
    And I open cookie settings
    Then the cookie banner should be visible
    And the "cookie_consent" cookie should be cleared
