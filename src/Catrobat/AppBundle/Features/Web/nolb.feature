@homepage
Feature: Testcases for nolb user

  Background:
    Given there are users:
      | name         | password  | token      | email               | nolb_status |
      | nolbmuser    | 123456    | cccccccccc | dev1@pocketcode.org | true        |
      | nolbteacher  | 654321    | cccccccccc | dev2@pocketcode.org | true        |
      | normaluser   | abcdef    | cccccccccc | dev3@pocketcode.org | false       |
    And there are programs:
      | id | name      | description | owned by     | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | nolbmuser    | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 | p2          | normaluser   | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
    And there are nolb example programs:
      | id | program  | active | for_female  | female_counter | male_counter |
      | 1  | 1        | true   | true        | 1              | 2            |
      | 2  | 2        | true   | false       | 0              | 4            |

  Scenario: Normal user should not be able to see the nolb submit button
    Given I log in as "normaluser" with the password "abcdef"
    And I am on "/pocketcode/program/2"
    Then I should see 0 "#nolb-project-button"

  Scenario: Nolb user cannot edit their profile
    Given I log in as "nolbmuser" with the password "123456"
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

  Scenario: Nolb user can only submit own programs (not allowed)
    Given I log in as "nolbmuser" with the password "123456"
    And I am on "/pocketcode/program/2"
    Then I should see 0 "#nolb-project-button"

  Scenario: Nolb user can only submit own programs (allowed)
    Given I log in as "nolbmuser" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I should see 1 "#nolb-project-button"
    When I click "#nolb-project-button"
    And I should see 1 "#nolb-submission-box"

  #TODO: add tests for nolb submission button (success and fail)

  Scenario: Normal user should not see the example section
    Given I log in as "normaluser" with the password "abcdef"
    And I am on "/pocketcode/"
    Then I should see 0 "#nolb-example"

