@web-general @dataset-statistics-footer
Feature: Footer statistics
  Scenario: Footer shows project and user totals
    Given I have accepted cookies
    When I open the "homepage" page
    Then the footer should show:
      | 10 |
      | 17 |
