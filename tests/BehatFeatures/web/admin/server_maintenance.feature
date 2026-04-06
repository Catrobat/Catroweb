@admin
Feature: Admin Server Maintenance
  In order to keep the server clean
  As Admin
  I want to be able to delete Extracted Catrobatfiles in the Backend

  Scenario: As a valid admin I want to be able to see the Disk Space menu
    Given I am a logged in as super admin
    When I GET "/admin/dashboard"
    Then the client response should contain "System Dashboard"

  Scenario: As a valid admin I want to be able to delete the resources
    Given I am a logged in as super admin
    When I GET "/admin/system/maintenance/list"
    Then the client response should contain "Delete compressed files"
    And the client response should contain "Delete log files"
    And the client response should contain "Archive logs files"

  Scenario: As a valid admin I want to be able to remove the compressed program files through the backend
  which should result in deleting resources from disk and reset entity state
    Given I am a logged in as super admin
    And there are projects:
      | id | name    |
      | 1  | program |
    And there is a file "1.catrobat" with size "4096" bytes in the compressed-folder
    When I GET "/admin/system/maintenance/list"
    Then the client response should contain "Compressed Catrobatfiles (4.00 KiB)"
    When I GET "/admin/system/maintenance/compressed"
    Then the client response should contain "Compressed Catrobatfiles (0.00 B)"

