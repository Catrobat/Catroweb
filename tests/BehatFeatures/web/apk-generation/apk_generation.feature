Feature:

  Background:
    Given the server name is "pocketcode.org"
    And the token to upload an apk file is "UPLOADTOKEN"

  Scenario: Transmit data needed to build the apk to jenkins
    Given I have a project "My little project" with id "1"
    And the jenkins job id is "Build-Program"
    And the jenkins token is "SECRETTOKEN"
    When I start an apk generation of my project
    Then following parameters are sent to jenkins:
      | parameter | value                                                         |
      | job       | /Build-Program/                                               |
      | token     | /SECRETTOKEN/                                                 |
      | SUFFIX    | /generated(.*?)/                                              |
      | DOWNLOAD  | #http://pocketcode.org/api/project/(.*?)/catrobat#           |
      | UPLOAD    | #http://pocketcode.org/app/ci/upload/(.*?)?token=UPLOADTOKEN# |
      | ONERROR   | #http://pocketcode.org/app/ci/failed/(.*?)?token=UPLOADTOKEN# |
    And the project apk status will be flagged "pending"

  Scenario: Accept the compiled apk from jenkins
    Given I have a project "My little project" with id "1"
    And I requested jenkins to build it
    When jenkins uploads the apk file to the given upload url
    Then it will be stored on the server
    And the project apk status will be flagged "ready"

  Scenario: Only build the apk once
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "pending"
    When I start an apk generation of my project
    Then no build request will be sent to jenkins

  Scenario: Reset flag if a build fails
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "pending"
    When I report a build error
    Then the project apk status will be flagged "none"

  Scenario: only reset flag if status is "pending"
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "ready"
    When I report a build error
    Then the project apk status will still be flagged "ready"

  Scenario: reset apk status after an update
    Given I have a project "My little project" with id "1"
    And the project apk status is flagged "ready"
    When I update this project
    Then the project apk status will still be flagged "none"
    And the apk file will be deleted

