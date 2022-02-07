@admin
Feature: Admin Role Restrictions
  In order to keep the admin secure
  As Admin/User
  I want to be able/not to be able to log into the admin

  Scenario: As a valid admin i want to be able to log in the backend
    Given I am a logged in as admin
    When I GET "/admin/dashboard"
    Then the client response should contain "Admin Panel"

  Scenario: As a ordinary user i want not to be able to log in the backend
    Given I am logged in as normal user
    When I GET "/admin/dashboard"
    Then the client response should contain "Access Denied"

  Scenario: As user i should not see the admin button
    Given I am logged in as normal user
    Given I GET "/"
    Then the client response should not contain "Admin"

  Scenario: As a admin i should see the admin button and get expected uri
    Given I am a logged in as admin
    Given I GET "/"
    Then the client response should contain "Admin"
    Then URI from "Admin" should be "/admin/dashboard"
  