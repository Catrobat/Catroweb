@web @profile_page
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email and country, upload an avatar image and delete my own programs

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are projects:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | language version | private |
      | 1  | project 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.6              | false   |
      | 2  | project 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   | 999              | true    |
      | 3  | project 3 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | false   |

    And I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"

  Scenario: changing my username must work
    Given I click "#edit-username-button"
    And I wait for AJAX to finish
    When I fill in "username" with "Mr.Catro"
    And I click "#save-username"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Catrobat"
    Then I should see "Your password or username was incorrect."
    When I try to log in as "Mr.Catro"
    Then I should be logged in

  Scenario: When changing the username the min length must be 3
    Given I click "#edit-username-button"
    And I wait for AJAX to finish
    When I fill in "username" with "Mr"
    And I click "#save-username"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And I should see "This username is not valid."
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Mr"
    Then I should see "Your password or username was incorrect."

  Scenario: When changing the username the max length must be 180
    Given I click "#edit-username-button"
    And I wait for AJAX to finish
    When I fill in "username" with "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!+++++"
    And I click "#save-username"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And I should see "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!"
    And I should not see "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!+"

  Scenario: changing password must work
    Given I click "#edit-password-button"
    And I wait for AJAX to finish
    When I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "abcdef"
    And I click "#save-password"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Catrobat"
    Then I should see "Your password or username was incorrect."
    When I log in as "Catrobat" with the password "abcdef"
    Then I should be logged in

  Scenario: changing password with a typo in repeat-password should not work
    Given I click "#edit-password-button"
    And I wait for AJAX to finish
    When I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "fedcba"
    And I click "#save-password"
    And I wait for AJAX to finish
    Then I should see "The passwords didn't match."

  Scenario: a short password should not work
    Given I click "#edit-password-button"
    And I wait for AJAX to finish
    When I fill in "old-password" with "123456"
    And I fill in "password" with "abc"
    And I fill in "repeat-password" with "abc"
    And I click "#save-password"
    And I wait for AJAX to finish
    Then I should see "The new password must have at least 6 characters."

  Scenario: too long password should not work
    Given I click "#edit-password-button"
    And I wait for AJAX to finish
    When I fill in "old-password" with "123456"
    And I fill in "password" with "ThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLong!!!!!"
    And I fill in "repeat-password" with "ThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLongThisPasswordIs5000CharsLong!!!!!"
    And I click "#save-password"
    And I wait for AJAX to finish
    Then I should see "The new password can have a maximum of 4096 characters."

  Scenario: changing email and additional email should work (shows pop up)
    Given I click "#edit-email-button"
    And I wait for AJAX to finish
    When I fill in "email" with "first@email.com"
    And I click "#save-email"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And I should see "Success"
    And I should see "An email was sent to your email address"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "first@email.com"

  Scenario: changing email addresses with an invalid email should not work
    Given I click "#edit-email-button"
    And I wait for AJAX to finish
    When I fill in "email" with "first"
    And I click "#save-email"
    And I wait for AJAX to finish
    Then I should see "This email address is not valid."
    And I click "#save-email"
    And I wait for AJAX to finish
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email"
    And I click "#save-email"
    And I wait for AJAX to finish
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email.comcomcom"
    And I click "#save-email"
    And I wait for AJAX to finish
    Then I should see "This email address is not valid."

  Scenario: deleting first mail without an additional email should not work
    Given I click "#edit-email-button"
    And I wait for AJAX to finish
    When I fill in "email" with ""
    And I click "#save-email"
    And I wait for AJAX to finish
    Then I should see "Error while updating this e-mail address. You must have at least one validated e-mail address."

  Scenario: changing country should work
    Given I click "#edit-country-button"
    And I wait for AJAX to finish
    When I select "Austria" from "country"
    And I click "#save-country"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And the "#country" element should contain "Austria"

  Scenario: deleting a project should set the project to invisible
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "project 2"
    When I go to "/app/userDeleteProject/2"
    And I wait for AJAX to finish
    Then I should not see "project 2"
    And I should see "project 1"
    And there should be "3" programs in the database
    When I go to "/app/project/2"
    And I wait for AJAX to finish
    Then I should see "Ooooops something went wrong."

  Scenario: deleting another user's project should not work
    Given I go to "/app/userDeleteProject/3"
    Then I should see "Ooooops something went wrong."

  Scenario: check deletion PopUp
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "project 2"
    When I click "#delete-1"
    And I wait for AJAX to finish
    Then I should see "delete it"
    When I click ".swal2-cancel"
    And I wait for AJAX to finish
    Then I should see "project 1"
    When I click "#delete-1"
    And I wait for AJAX to finish
    And I click ".swal2-confirm"
    Then I should not see "project 1"

  Scenario: It should be possible toggle the project privacy on myprofile
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible
    When I click "#visibility-lock-open-1"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    And the element "#visibility-lock-open-1" should not be visible
    And the element "#visibility-lock-1" should be visible
    When I click "#visibility-lock-1"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible

  Scenario: Project visibility should not get updated if the user clicks on cancel
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible
    When I click "#visibility-lock-open-1"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-cancel"
    And I wait for AJAX to finish
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible

  Scenario: It should be possible toggle the project privacy on myprofile for more than 1 project
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "project 2"
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible
    And the element "#visibility-lock-open-2" should not be visible
    And the element "#visibility-lock-2" should be visible
    When I click "#visibility-lock-open-1"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    And the element "#visibility-lock-open-1" should not be visible
    And the element "#visibility-lock-1" should be visible
    When I click "#visibility-lock-2"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    And the element "#visibility-lock-open-2" should be visible
    And the element "#visibility-lock-2" should not be visible

  Scenario: Programs with too high language version can also be set to visible
    Given I am on "/app/user"
    And I wait for the page to be loaded
    And I should see "project 2"
    And the element "#visibility-lock-2" should be visible
    But the element "#visibility-lock-open-2" should not be visible
    When I click "#visibility-lock-open-2"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "#visibility-lock-2" should not be visible
    But the element "#visibility-lock-open-2" should be visible

  Scenario: I should be able to delete my account
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then the element "#delete-account-button" should not be visible
    When I click "#account-settings-button"
    Then the element "#delete-account-button" should be visible
    When I click "#delete-account-button"
    And I wait for AJAX to finish
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: I should be able to delete my account with comments and notifications
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And there are comments:
    | id  | program_id | user_id | text |
    | 1   | 1          | 1       | c1   |
    | 2   | 2          | 2       | c2   |
    | 3   | 3          | 1       | c1   |
    And there are catro notifications:
      | user     | title                 | message         | type                     | commentID | like_from | follower_id | program_id  | parent_program | child_program |
      | Catrobat |                       |                 | comment                  | 2         |           |             |             |                |               |
      | Catrobat |                       |                 | like                     |           | 2         |             | 2           |                |               |
      | Catrobat |                       |                 | follower                 |           |           | 2           |             |                |               |
      | Catrobat | title                 | msg             | default                  |           |           |             |             |                |               |
      | Catrobat |                       |                 | follow_program           |           |           |             | 2           |                |               |
      | Catrobat |                       |                 | remix                    |           |           |             |             | 1              | 3             |
      | User1    |                       |                 | comment                  | 1         |           |             |             |                |               |
      | User1    |                       |                 | like                     |           | 1         |             | 2           |                |               |
      | User1    |                       |                 | follower                 |           |           | 1           |             |                |               |
      | User1    | title                 | msg             | default                  |           |           |             |             |                |               |
      | User1    |                       |                 | follow_program           |           |           |             | 2           |                |               |
      | User1    |                       |                 | remix                    |           |           |             |             | 3              | 2             |
      | Catrobat | title                 | msg             | broadcast                |           |           |             |             |                |               |
      | User1    | title                 | msg             | broadcast                |           |           |             |             |                |               |
    Then the element "#delete-account-button" should not be visible
    When I click "#account-settings-button"
    And I wait for AJAX to finish
    Then the element "#delete-account-button" should be visible
    When I click "#delete-account-button"
    And I wait for AJAX to finish
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should be logged out
    And the user "Catrobat" should not exist
    And I log in as "User1" with the password "123456"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then the element "#delete-account-button" should not be visible
    When I click "#account-settings-button"
    And I wait for AJAX to finish
    Then the element "#delete-account-button" should be visible
    When I click "#delete-account-button"
    And I wait for AJAX to finish
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should be logged out
    And the user "User1" should not exist
    And comments or catro notifications should not exist

  Scenario: When changing the username it shouldn't contain an email address
    Given I click "#edit-username-button"
    And I wait for AJAX to finish
    When I fill in "username" with "catro catro@gmail.com"
    And I click "#save-username"
    And I wait for the page to be loaded
    Then I should be on "/app/user"
    And I should see "Username shouldn't contain an email address."
    When I go to "/app/logout"
    And I wait for the page to be loaded
    And I log in as "Mr"
    Then I should see "Your password or username was incorrect."
