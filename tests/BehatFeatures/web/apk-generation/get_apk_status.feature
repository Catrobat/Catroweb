Feature: Get the APK status of a project

  Scenario: get the apk status pending
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "pending"
    When I GET "/app/ci/status/1"
    Then will get the following JSON:
        """
        {
          "status" : "pending",
          "label" : "Generating apk"
         }
        """

  Scenario: get the apk status ready
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "ready"
    When I GET "/app/ci/status/1"
    Then will get the following JSON:
        """
        {
          "status" : "ready",
          "url" : "http://localhost/app/ci/download/(.*)?fname=My%20little%20project",
          "label" : "Download apk"
        }
        """

  Scenario: get the apk status none
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "none"
    When I GET "/app/ci/status/1"
    Then will get the following JSON:
        """
        {
          "label" : "Generate apk",
          "status" : "none"
        }
        """
