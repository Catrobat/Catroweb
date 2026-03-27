@web-general @dataset-minimal
Feature: Help redirects
  Scenario Outline: Help page redirects to the expected documentation
    When I request the help page with language "<language>"
    Then the response should redirect to "<location>"

    Examples:
      | language | location                                 |
      | English  | https://catrobat.org/docs/              |
      | Deutsch  | https://catrobat.org/de/dokumentation/ |
