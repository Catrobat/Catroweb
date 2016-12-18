@homepage
Feature: Testcases for nolb user

  Background:
    Given there are users:
      | name        | password  | token      | email               | nolb_status |
      | nolbuser    | 123456    | cccccccccc | dev1@pocketcode.org | true        |
      | nolbteacher | 654321    | cccccccccc | dev2@pocketcode.org | true        |
      | normaluser  | abcdef    | cccccccccc | dev3@pocketcode.org | false       |
    And there are programs:
      | id | name      | description | owned by   | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | nolbuser   | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 | p2          | normaluser | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |

  Scenario: Nolb user can see the nolb submit button
    Given I log in as "nolbuser" with the password "123456"
    And I am on "/pocketcode/program/1"
    Then the element "#nolb-project-button" should be visible

  Scenario: Normal user should not be able to see the nolb submit button
    Given I log in as "normaluser" with the password "abcdef"
    And I am on "/pocketcode/program/2"
    Then I should see 0 "#nolb-project-button"

  Scenario: Nolb user cannot edit their profile
    Given I log in as "nolbuser" with the password "123456"
    And I am on "/pocketcode/profile"
    Then I should see 0 "#edit-icon"

  Scenario: Nolb teacher can only edit their password
    Given I log in as "nolbteacher" with the password "654321"
    And I am on "/pocketcode/profile"
    Then the element "#edit-icon" should be visible
    When I click the "edit" button
    Then the element "#password-information" should be visible
    And I should see 0 "#profile-avatar"
    And I should see 0 "#country-information"
    And I should see 0 "#email-information"

  Scenario: Nolb user can only submit own programs
    Given I log in as "nolbuser" with the password "123456"
    And I am on "/pocketcode/program/2"
    Then I should see 0 "#nolb-project-button"

  #TODO: modifier code, that only the nolbuser, that upload the program can submit it
  #TODO: add tests for nolb submission button (success and fail)
