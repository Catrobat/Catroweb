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
    And the client response should contain "Delete compressed files"
    And the client response should contain "Delete log files"
    And the client response should contain "Archive logs files"

  Scenario: As a valid admin I want to be able to remove the APKs through the backend
  which should result in deleting apk from disk and reset entity state
    Given I am a logged in as super admin
    When I GET "/admin/maintain/apk"
    Then the client response should contain "Generated APKs (0.00 B)"
    And there are projects:
      | id | name      | apk_status | directory_hash |
      | 1  | program 1 | ready      | null           |
    And there is a file "1.apk" with size "4096" bytes in the APK-folder
    When I GET "/admin/maintain/list"
    Then the client response should contain "Generated APKs (4.00 KiB)"
    When I GET "/admin/maintain/apk"
    Then the client response should contain "Generated APKs (0.00 B)"
    And project with id "1" should have no apk

  Scenario: As a valid admin I want to be able to remove the compressed program files through the backend
  which should result in deleting resources from disk and reset entity state
    Given I am a logged in as super admin
    And there are projects:
      | id | name    | apk_status |
      | 1  | program | none       |
    And there is a file "1.catrobat" with size "4096" bytes in the compressed-folder
    When I GET "/admin/maintain/list"
    Then the client response should contain "Compressed Catrobatfiles (4.00 KiB)"
    When I GET "/admin/maintain/compressed"
    Then the client response should contain "Compressed Catrobatfiles (0.00 B)"

