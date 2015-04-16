Feature: Get the APK status of a program

    Scenario: get the apk status pending
        Given I have a program "My little program" with id "1"
        And the program apk status is flagged "pending"
        When I GET "/ci/status/1"
        Then will get the following JSON:
        """
        { 
          "status" : "pending",
          "label" : "Generating Apk"
         }
        """
        
    Scenario: get the apk status ready
        Given I have a program "My little program" with id "1"
        And the program apk status is flagged "ready"
        When I GET "/ci/status/1"
        Then will get the following JSON:
        """
        { 
          "status" : "ready",
          "url" : "http://localhost/ci/download/1",
          "label" : "Download Apk"
        }
        """
        
     Scenario: get the apk status none
        Given I have a program "My little program" with id "1"
        And the program apk status is flagged "none"
        When I GET "/ci/status/1"
        Then will get the following JSON:
        """
        {
          "status" : "none",
          "label" : "Generate Apk"
        }
        """
        
        