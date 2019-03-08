@homepage
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email and country, upload an avatar image and delete my own programs

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | language version | private |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.6              | 0       |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   | 999              | 1       |
      | 3  | program 3 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
    And I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/profile"
    And I should see "My Profile"

  Scenario: changing password must work
    Given I click "#edit-password-button"
    And I wait 250 milliseconds
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "abcdef"
    And I click "#save-password"
    And I wait for the server response
    And I should be on "/pocketcode/profile"
    When I go to "/pocketcode/logout"
    And I try to log in as "Catrobat" with the password "123456"
    Then I should see "Your password or username was incorrect."
    When I log in as "Catrobat" with the password "abcdef"
    Then I should be logged in

  Scenario: changing password with a typo in repeat-password should not work
    Given I click "#edit-password-button"
    And I wait 250 milliseconds
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "fedcba"
    And I click "#save-password"
    And I wait for the server response
    Then I should see "The passwords didn't match."

  Scenario: a short password should not work
    Given I click "#edit-password-button"
    And I wait 250 milliseconds
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abc"
    And I fill in "repeat-password" with "abc"
    And I click "#save-password"
    And I wait for the server response
    Then I should see "The new password must have at least 6 characters."

  Scenario: too long password should not work
    Given I click "#edit-password-button"
    And I wait 250 milliseconds
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz"
    And I fill in "repeat-password" with "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz"
    And I click "#save-password"
    And I wait for the server response
    Then I should see "The new password can have a maximum of 32 characters."

  Scenario: changing email and additional email should work (shows pop up)
    Given I click "#edit-email-button"
    And I wait 250 milliseconds
    And I fill in "email" with "first@email.com"
    And I fill in "additional-email" with "second@email.com"
    And I click "#save-email"
    And I wait for the server response
    Then I should be on "/pocketcode/profile"
    And I should see "Success"
    And I should see "An email was sent to your email address"
    When I click ".swal2-confirm"
    And I wait 100 milliseconds
    And I should see "first@email.com"
    And I should see "second@email.com"

  Scenario: changing email addresses with an invalid email should not work
    Given I click "#edit-email-button"
    And I wait 250 milliseconds
    And I fill in "email" with "first"
    And I click "#save-email"
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second"
    And I click "#save-email"
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email"
    And I click "#save-email"
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second@email"
    And I click "#save-email"
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email.comcomcom"
    And I click "#save-email"
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second@email.comcomcom"
    And I click "#save-email"
    And I wait for the server response
    Then I should see "This email address is not valid."

  Scenario: deleting first mail without an additional email should not work
    Given I click "#edit-email-button"
    And I wait 250 milliseconds
    When I fill in "email" with ""
    And I click "#save-email"
    And I wait for the server response
    Then I should see "Error while updating this e-mail address. You must have at least one validated e-mail address."

  Scenario: when deleting the first mail, the additional mail should become the first mail
    Given I click "#edit-email-button"
    And I wait 250 milliseconds
    And I fill in "email" with ""
    And I fill in "additional-email" with "second@email.com"
    When I click "#save-email"
    And I wait for the server response
    Then I should be on "/pocketcode/profile"
    And I should see "Success"
    When I click ".swal2-confirm"
    And I wait 100 milliseconds
    Then the "#first-mail" element should contain "second@email.com"

  Scenario: changing country should work
    Given I click "#edit-country-button"
    And I wait 250 milliseconds
    When I select "Austria" from "country"
    And I click "#save-country"
    And I wait for the server response
    Then I should be on "/pocketcode/profile"
    And the "#country" element should contain "Austria"

  Scenario: uploading avatar should work
    When I click "#avatar-upload"
    And I wait for the server response
    When I attach the avatar "logo.png" to "file"
    And I wait for the server response
    Then the avatar img tag should have the "logo.png" data url

  Scenario: only jpg, png or gif allowed for avatar
    When I click "#avatar-upload"
    And I attach the avatar "fail.tif" to "file"
    And I wait for the server response
    Then the avatar img tag should not have the "fail.tif" data url

  Scenario: max. 5MB for avatar image
    When I click "#avatar-upload"
    And I attach the avatar "galaxy_big.png" to "file"
    Then I should see "Your chosen picture is too large, please do not use images larger than 5mb."

  Scenario: deleting a program should work
    Given I am on "/pocketcode/profile"
    And I wait 100 milliseconds
    Then I should see "program 1"
    And I should see "program 2"
    When I go to "/pocketcode/profileDeleteProgram/2"
    And I wait 100 milliseconds
    Then I should not see "program 2"
    And I should see "program 1"
    And there should be "3" programs in the database
    When I go to "/pocketcode/program/2"
    And I wait 100 milliseconds
    Then I should see "Ooooops something went wrong."

  Scenario: deleting another user's program should not work
    Given I go to "/pocketcode/profileDeleteProgram/3"
    Then I should see "Ooooops something went wrong."

  Scenario: check deletion PopUp
    Given I am on "/pocketcode/profile"
    And I wait 100 milliseconds
    Then I should see "program 1"
    And I should see "program 2"
    When I click "#delete-1"
    And I wait 100 milliseconds
    Then I should see "delete it"
    When I click ".swal2-cancel"
    Then I should see "program 1"
    When I click "#delete-1"
    And I wait 100 milliseconds
    And I click ".swal2-confirm"
    Then I should not see "program 1"

  Scenario: It should be possible toggle the program privacy on myprofile
    Given I am on "/pocketcode/profile"
    And I wait 100 milliseconds
    Then I should see "program 1"
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible
    When I click "#visibility-lock-open-1"
    And I wait for the server response
    And the element "#visibility-lock-open-1" should not be visible
    And the element "#visibility-lock-1" should be visible
    When I click "#visibility-lock-1"
    And I wait for the server response
    And the element "#visibility-lock-open-1" should be visible
    And the element "#visibility-lock-1" should not be visible

  Scenario: Programs with too high language version can't be set visible
    Given I am on "/pocketcode/profile"
    And I wait 100 milliseconds
    And I should see "program 2"
    And the element "#visibility-lock-2" should be visible
    And the element "#visibility-lock-open-2" should not be visible
    When I click "#visibility-lock-open-2"
    And I wait for the server response
    Then I should see "Can not change visibility"
    When I click ".swal2-confirm"
    Then the element "#visibility-lock-2" should be visible
    And the element "#visibility-lock-open-2" should not be visible

  Scenario: I should be able to delete my account
    Given I am on "/pocketcode/profile"
    Then the element "#delete-account-button" should not be visible
    When I click "#account-settings-button"
    And I wait 250 milliseconds
    Then the element "#delete-account-button" should be visible
    When I click "#delete-account-button"
    And I wait 100 milliseconds
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for the server response
    Then I should be on "/pocketcode/"

  Scenario: I should be able to delete my account with comments
    Given I am on "/pocketcode/profile"
    And there are comments:
      | program_id | user_id | upload_date      | text | user_name | reported |
      | 3          | 1       | 01.01.2013 12:01 | c1   | Catrobat  | true     |
      | 2          | 2       | 01.01.2013 12:02 | c2   | User1     | true     |
    And there are catro notifications:
      | user     | title                 | message                                      | type    | commentID |
      | Catrobat | Achievement - Uploads | Congratulations, you uploaded your first app | comment | 2         |
    Then the element "#delete-account-button" should not be visible
    When I click "#account-settings-button"
    And I wait 250 milliseconds
    Then the element "#delete-account-button" should be visible
    When I click "#delete-account-button"
    And I wait 100 milliseconds
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait for the server response
    Then I should be logged out
