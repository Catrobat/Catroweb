@admin
Feature: Admin Role Restrictions
  In order to keep the admin secure
  As Admin/User
  I want to be able/not to be able to log into the admin

Scenario: As a valid admin i want to be able to log in the backend
  Given I am a user with role "ROLE_ADMIN"
  When I GET "/admin/dashboard"
  Then the response should contain "Admin Panel"


Scenario: As a ordinary user i want not to be able to log in the backend
  Given I am a user with role "ROLE_USER"
  When I GET "/admin/dashboard"
  Then the response should not contain "Admin Panel"