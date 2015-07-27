@admin
Feature: Admin Server Maintenance
  In order to keep the server clean
  As Admin
  I want to be able to delete Extracted Catrobatfiles/APKs and Backups in the Backend

Scenario: As a valid admin i want to be able to see the Maintain menu
  Given I am a user with role "ROLE_SUPER_ADMIN"
  When I GET "/admin/dashboard"
  Then the response should contain "Maintain"

Scenario: As a valid admin i want to be able to delete the resources
  Given I am a user with role "ROLE_SUPER_ADMIN"
  When I GET "/admin/maintain/list"
  Then the response should contain "Delete APKs"
  And the response should contain "Delete extracted files"
  And the response should contain "Delete Backups"

Scenario: As a valid admin i want to be able to remove the APKs through the backend
  which should result in deleting apk from disk and reset entity state
  Given I am a user with role "ROLE_SUPER_ADMIN"
  And there are programs:
    | id | name      |  apk_status  | directory_hash  |
    | 1  | program 1 |  2           | null            |
  And there is a file "1.apk" with size "4096" bytes in the APK-folder
  When I GET "/admin/maintain/list"
  Then the response should contain "Generated APKs (4.00 KiB)"
  When I GET "/admin/maintain/apk"
  Then the response should contain "Generated APKs (0.00 B)"
  And program with id "1" should have no apk


  Scenario: As a valid admin i want to be able to delete all backups through the backend
    Given I am a user with role "ROLE_SUPER_ADMIN"
    And there is a file "a_backup.zip" with size "4096" bytes in the backup-folder
    And there is a file "a_second_backup.zip" with size "4096" bytes in the backup-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "Manual Backups (8.00 KiB)"
    When I GET "/admin/maintain/backup"
    Then the response should contain "Manual Backups (0.00 B)"


  Scenario: As a valid admin i want to be able to delete a single backup through the backend
    Given I am a user with role "ROLE_SUPER_ADMIN"
    And there is a file "a_backup.zip" with size "4096" bytes in the backup-folder
    And there is a file "a_second_backup.zip" with size "4096" bytes in the backup-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "Manual Backups (8.00 KiB)"
    When I GET "/admin/maintain/backup?backupFile=a_backup.zip"
    Then the response should contain "Manual Backups (4.00 KiB)"
    And the response should contain "a_second_backup.zip"


  Scenario: As a valid admin i want to be able to remove the extracted program files through the backend
  which should result in deleting resources from disk and reset entity state
    Given I am a user with role "ROLE_SUPER_ADMIN"
    And there are programs:
      | id | name      |  apk_status  | directory_hash  |
      | 1  | program   |  0           | generated_hash  |
    And there is a file "generated_hash/code.xml" with size "4096" bytes in the extracted-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "Extracted Catrobatfiles (4.00 KiB)"
    When I GET "/admin/maintain/extracted"
    Then the response should contain "Extracted Catrobatfiles (0.00 B)"
    And program with id "1" should have no directory_hash