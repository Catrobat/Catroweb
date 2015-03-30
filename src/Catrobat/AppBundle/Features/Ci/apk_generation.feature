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
          | parameter | value                                                |
          | job       | Build-Program                                        |
          | token     | SECRETTOKEN                                          |
          | PROJECT   | 1                                                    |
          | download  | https://pocketcode.org/download/1.catrobat           |
          | upload    | https://pocketcode.org/ci/upload/1?token=UPLOADTOKEN |
          