@homepage
Feature: Testcases for nolb user

  Background:
    Given there are users:
      | name        | password | token      | email               | nolb_status |
      | nolbmuser   | 123456   | cccccccccc | dev1@pocketcode.org | true        |
      | nolbteacher | 654321   | cccccccccc | dev2@pocketcode.org | true        |
      | normaluser  | abcdef   | cccccccccc | dev3@pocketcode.org | false       |
    And there are programs:
      | id | name      | description | owned by   | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | nolbmuser  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 | p2          | normaluser | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
    And there are nolb example programs:
      | id | program | active | for_female | female_counter | male_counter |
      | 1  | 1       | true   | true       | 1              | 2            |
      | 2  | 2       | true   | false      | 0              | 4            |

  Scenario: Nolb user cannot edit their profile
    Given I log in as "nolbmuser" with the password "123456"
    And I am on "/pocketcode/profile"
    Then the element "#avatar-upload" should not exist
    Then the element "#edit-country-button" should not exist
    Then the element "#edit-email-button" should not exist
    Then the element "#password-wrapper" should not exist
    Then the element "#account-settings-wrapper" should not exist

  Scenario: Nolb teacher can only edit their password
    Given I log in as "nolbteacher" with the password "654321"
    And I am on "/pocketcode/profile"
    Then the element "#avatar-upload" should not exist
    Then the element "#edit-country-button" should not exist
    Then the element "#edit-email-button" should not exist
    Then the element "#account-settings-wrapper" should not exist
    Then the element "#password-wrapper" should be visible
    Then the element "#edit-password-button" should be visible

  Scenario: Nolb user can only submit own programs (not allowed)
    Given I log in as "nolbmuser" with the password "123456"
    And I am on "/pocketcode/program/2"
    Then I should see 0 "#nolb-project-button"

  Scenario: Normal user should not see the example section
    Given I log in as "normaluser" with the password "abcdef"
    And I am on "/pocketcode/"
    Then I should see 0 "#nolb-example"

