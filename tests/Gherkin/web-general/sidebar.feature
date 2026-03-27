@web-general @dataset-minimal
Feature: Sidebar navigation
  Scenario: Toggle button opens and closes the sidebar
    Given I have accepted cookies
    When I open the "homepage" page
    Then the sidebar should be closed
    When I toggle the sidebar
    Then the current URL should end with "/app/"
    And the sidebar should be open
    When I toggle the sidebar
    Then the current URL should end with "/app/"
    And the sidebar should be closed

  Scenario: Browser back closes the sidebar on the login page
    Given I have accepted cookies
    When I open the "homepage" page
    And I open the sidebar
    And I click the login link from the sidebar
    Then the current URL should end with "/app/login"
    And the page is settled
    When I toggle the sidebar
    Then the sidebar should be open
    When I navigate back in the browser
    Then the current URL should end with "/app/login"
    And the sidebar should be closed
