@admin
Feature: Admin Server Maintenance
  In order to keep the server clean
  As Admin
  I want to be able to delete Extracted Catrobatfiles/APKs and Backups in the Backend

Scenario: As a valid admin I want to be able to see the Maintain menu
  Given I am a logged in as super admin
  When I GET "/admin/dashboard"
  Then the response should contain "Maintain"

Scenario: As a valid admin I want to be able to delete the resources
  Given I am a logged in as super admin
  When I GET "/admin/maintain/list"
  Then the response should contain "Delete APKs"
  And the response should contain "Delete extracted files"
  And the response should contain "Delete backups"

Scenario: As a valid admin I want to be able to remove the APKs through the backend
  which should result in deleting apk from disk and reset entity state
  Given I am a logged in as super admin
  And there are programs:
    | id | name      |  apk_status  | directory_hash  |
    | 1  | program 1 |  2           | null            |
  And there is a file "1.apk" with size "4096" bytes in the APK-folder
  When I GET "/admin/maintain/list"
  Then the response should contain "Generated APKs (4.00 KiB)"
  When I GET "/admin/maintain/apk"
  Then the response should contain "Generated APKs (0.00 B)"
  And program with id "1" should have no apk


  Scenario: As a valid admin I want to be able to delete all backups through the backend
    Given I am a logged in as super admin
    And there is a file "a_backup.tar.gz" with size "4096" bytes in the backup-folder
    And there is a file "a_second_backup.tar.gz" with size "4096" bytes in the backup-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "a_backup.tar.gz"
    And the response should contain "a_second_backup.tar.gz"
    And the response should contain "Manual backups (8.00 KiB)"
    When I GET "/admin/maintain/delete_backups"
    Then the response should contain "Manual backups (0.00 B)"


  Scenario: As a valid admin I want to be able to delete a single backup through the backend
    Given I am a logged in as super admin
    And there is a file "a_backup.tar.gz" with size "4096" bytes in the backup-folder
    And there is a file "a_second_backup.tar.gz" with size "4096" bytes in the backup-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "Manual backups (8.00 KiB)"
    When I GET "/admin/maintain/delete_backups?backupFile=a_backup.tar.gz"
    Then the response should contain "Manual backups (4.00 KiB)"
    And the response should contain "a_second_backup.tar.gz"


  Scenario: As a valid admin I want to be able to remove the extracted program files through the backend
  which should result in deleting resources from disk and reset entity state
    Given I am a logged in as super admin
    And there are programs:
      | id | name      |  apk_status  | directory_hash  |
      | 1  | program   |  0           | generated_hash  |
    And there is a file "generated_hash/code.xml" with size "4096" bytes in the extracted-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "Extracted Catrobatfiles (4.00 KiB)"
    When I GET "/admin/maintain/extracted"
    Then the response should contain "Extracted Catrobatfiles (0.00 B)"
    And program with id "1" should have no directory_hash

  Scenario: As a valid admin I want to be able to create a single backup through the backend
    Given I am a logged in as super admin
    And there is no file in the backup-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "Create backup"
    When I GET "/admin/maintain/create_backup"
    Then the response should contain "Delete backup"
    And the response should contain "Download backup"

  Scenario: As a valid admin I want to be able to download a single backup through the backend
    Given I am a logged in as super admin
    And there is a file "a_backup.tar.gz" with size "4096" bytes in the backup-folder
    When I GET "/admin/maintain/list"
    Then the response should contain "a_backup.tar.gz"
    When I GET "/pocketcode/download-backup/a_backup.tar.gz"
    Then the response Header should contain the key "Content-Disposition" with the value 'attachment; filename="a_backup.tar.gz"'