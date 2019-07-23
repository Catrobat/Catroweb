Feature:

  Background:
    Given the server name is "pocketcode.org"
    And I use a secure connection
    And the token to upload an apk file is "UPLOADTOKEN"

  Scenario: Transmit data needed to build the apk to jenkins
    Given I have a program "My little program" with id "1"
    And the jenkins job id is "Build-Program"
    And the jenkins token is "SECRETTOKEN"
    When I start an apk generation of my program
    Then following parameters are sent to jenkins:
      | parameter | value                                                          |
      | job       | Build-Program                                                  |
      | token     | SECRETTOKEN                                                    |
      | SUFFIX    | generated1                                                     |
      | DOWNLOAD  | http://pocketcode.org/app/download/1.catrobat           |
      | UPLOAD    | http://pocketcode.org/app/ci/upload/1?token=UPLOADTOKEN |
      | ONERROR   | http://pocketcode.org/app/ci/failed/1?token=UPLOADTOKEN |
    And the program apk status will be flagged "pending"

  Scenario: Accept the compiled apk from jenkins
    Given I have a program "My little program" with id "1"
    And I requested jenkins to build it
    When jenkins uploads the apk file to the given upload url
    Then it will be stored on the server
    And the program apk status will be flagged "ready"

  Scenario: Only build the apk once
    Given I have a program "My little program" with id "1"
    And the program apk status is flagged "pending"
    When I start an apk generation of my program
    Then no build request will be sent to jenkins

  Scenario: Reset flag if a build fails
    Given I have a program "My little program" with id "1"
    And the program apk status is flagged "pending"
    When I report a build error
    Then the program apk status will be flagged "none"

  Scenario: only reset flag if status is "pending"
    Given I have a program "My little program" with id "1"
    And the program apk status is flagged "ready"
    When I report a build error
    Then the program apk status will still be flagged "ready"

  Scenario: reset apk status after an update
    Given I have a program "My little program" with id "1"
    And the program apk status is flagged "ready"
    When I update this program
    Then the program apk status will still be flagged "none"
    And the apk file will be deleted
        
        