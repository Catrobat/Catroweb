@admin
Feature: As a member of an AD-Server i want to be able to login and get propper permissions

  Background:
    Given there are users:
      | name     | password | token      | email   | id |
      | Catrobat | 123456   | cccccccccc | c@b.at  |  1 |
      | User1    | 654321   | cccccccccf | c@d.com |  2 |

  Scenario: login with valid username and password on LDAP should succeed
    Given there are LDAP-users:
      | name              | password | groups                            |
      | ldap-user         | 123456   | Webserver-Administrators          |
      | ldap-mediapackage | 654321   | Webserver-MediaPackageMaintainers |
    When I POST login with user "ldap-user" and password "123456"
    Then the client response should contain "Featured"

  Scenario: login with invalid username and password on LDAP should fail
    Given there are LDAP-users:
      | name      | password |
      | ldap-user | 123456   |
    When I POST login with user "ldap-user" and password "not-the-pwd-you-are-looking-for"
    Then the client response should contain "Your password or username was incorrect"


  Scenario: User without group on LDAP should not be able to log into admin-area
    Given there are LDAP-users:
      | name      | password |
      | ldap-user | 123456   |
    When I POST login with user "ldap-user" and password "123456"
    Then the client response should contain "Featured"
    When I GET "/admin/dashboard"
    Then the client response should contain "Access Denied"

  Scenario: User in Catroweb-Group on LDAP should be Super-Admin after login
    Given there are LDAP-users:
      | name      | password | groups                   |
      | ldap-user | 123456   | Webserver-Administrators |
    When I POST login with user "ldap-user" and password "123456"
    And I GET "/admin/dashboard"
    Then the client response should contain "Admin Panel"


  Scenario: login as Mediapackage-User should give access only to mediapackage in admin-area
    Given there are LDAP-users:
      | name              | password | groups                            |
      | ldap-user         | 123456   | Webserver-Administrators          |
      | ldap-mediapackage | 654321   | Webserver-MediaPackageMaintainers |
    When I POST login with user "ldap-mediapackage" and password "654321"
    And I GET "/admin/media_package/list"
    Then the client response should contain "Media Package List"
    When I GET "/admin/upload_notification/list"
    Then the client response should contain "Forbidden"


  Scenario: login as FeaturedProgram-User should give access only to featured programs in admin-area
    Given there are LDAP-users:
      | name          | password | groups                                |
      | ldap-user     | 123456   | Webserver-Administrators              |
      | ldap-featured | 654321   | Webserver-FeaturedProgramsMaintainers |
    When I POST login with user "ldap-featured" and password "654321"
    And I GET "/admin/featured_program/list"
    Then the client response should contain "Featured Program List"
    When I GET "/admin/media_package/list"
    Then the client response should contain "Forbidden"


  Scenario: login as AppApprover-User should give access only to ApproveProgram in admin-area
    Given there are LDAP-users:
      | name         | password | groups                 |
      | ldap-approve | 654321   | Webserver-AppApprovers |
    When I POST login with user "ldap-approve" and password "654321"
    And I GET "/admin/approve/list"
    Then the client response should contain "Program List"
    When I GET "/admin/media_package/list"
    Then the client response should contain "Forbidden"


  Scenario: login as LDAP-User when user with same email already exists should result in "merge"
    Given there are LDAP-users:
      | name           | password | email   |
      | ldap-emailuser | 654321   | c@d.com |
    When I POST login with user "ldap-emailuser" and password "654321"
    Then the client response should contain "User1"

  Scenario: login as User in multiple LDAP groups should give access to multiple admin-areas
    Given there are LDAP-users:
      | name         | password | groups                                                       |
      | ldap-approve | 654321   | Webserver-AppApprovers,Webserver-FeaturedProgramsMaintainers |
    When I POST login with user "ldap-approve" and password "654321"
    And I GET "/admin/approve/list"
    Then the client response should contain "Program List"
    When I GET "/admin/featured_program/list"
    Then the client response should contain "Featured Program List"
    When I GET "/admin/media_package/list"
    Then the client response should contain "Forbidden"


  Scenario: Group-change on LDAP-Server should lead to permissions-change after login
    Given there are LDAP-users:
      | name            | password | groups                                |
      | ldap-fired-user | 654321   | Webserver-FeaturedProgramsMaintainers |
    When I POST login with user "ldap-fired-user" and password "654321"
    And I GET "/admin/featured_program/list"
    Then the client response should contain "Featured Program List"
    When I GET "/app/logout"
    Given there are LDAP-users:
      | name            | password |
      | ldap-fired-user | 654321   |
    When I POST login with user "ldap-fired-user" and password "654321"
    And I GET "/admin/featured_program/list"
    Then the client response should contain "Access Denied"

  Scenario: login with local account should still be possible if LDAP server is not reachable
    Given there are LDAP-users:
      | name      | password | groups                   |
      | ldap-user | 123456   | Webserver-Administrators |
    Given the LDAP server is not available
    When I POST login with user "Catrobat" and password "123456"
    Then the response code should be "200"


  
  