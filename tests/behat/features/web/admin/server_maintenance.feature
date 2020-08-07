@admin
Feature: Admin Server Maintenance
  In order to keep the server clean
  As Admin
  I want to be able to delete Extracted Catrobatfiles/APKs in the Backend

  Scenario: As a valid admin I want to be able to see the Maintain menu
    Given I am a logged in as super admin
    When I GET "/admin/dashboard"
    Then the client response should contain "Maintain"

  Scenario: As a valid admin I want to be able to delete the resources
    Given I am a logged in as super admin
    When I GET "/admin/maintain/list"
    Then the client response should contain "Delete APKs"
    And the client response should contain "Delete extracted files"
    And the client response should contain "Delete log files"
    And the client response should contain "Archive logs files"


  Scenario: As a valid admin I want to be able to remove the APKs through the backend
  which should result in deleting apk from disk and reset entity state
    Given I am a logged in as super admin
    When I GET "/admin/maintain/apk"
    Then the client response should contain "Generated APKs (0.00 B)"
    And there are programs:
      | id | name      | apk_status | directory_hash |
      | 1  | program 1 | ready      | null           |
    And there is a file "1.apk" with size "4096" bytes in the APK-folder
    When I GET "/admin/maintain/list"
    Then the client response should contain "Generated APKs (4.00 KiB)"
    When I GET "/admin/maintain/apk"
    Then the client response should contain "Generated APKs (0.00 B)"
    And program with id "1" should have no apk

  Scenario: As a valid admin I want to be able to remove the extracted program files through the backend
  which should result in deleting resources from disk and reset entity state
    Given I am a logged in as super admin
    And there are programs:
      | id | name    | apk_status | directory_hash |
      | 1  | program | none       | generated_hash |
    And there is a file "generated_hash/code.xml" with size "4096" bytes in the extracted-folder
    When I GET "/admin/maintain/list"
    Then the client response should contain "Extracted Catrobatfiles (4.00 KiB)"
    When I GET "/admin/maintain/extracted"
    Then the client response should contain "Extracted Catrobatfiles (0.00 B)"
    And program with id "1" should have no directory_hash

