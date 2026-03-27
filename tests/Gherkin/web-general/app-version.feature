@web-general @dataset-minimal
Feature: App version marker
  Scenario Outline: App version marker is shown on key pages
    Given I have accepted cookies
    When I open the "<page>" page
    Then the app version marker should contain "TEST_VERSION"

    Examples:
      | page              |
      | homepage          |
      | login             |
      | register          |
      | project details   |
      | profile           |
      | luna landing page |

  Scenario: App version marker is hidden from users
    Given I have accepted cookies
    When I open the "homepage" page
    Then the app version marker should contain "TEST_VERSION"
    And the app version marker should not be visible
