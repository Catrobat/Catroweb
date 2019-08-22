@homepage
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email and country, upload an avatar image and delete my own programs

  Background:
    Given there are users:
      | name     | password | token      | email        | id |
      | Catrobat | 123456   | cccccccccc | dev1@app.org |  1 |
      | User1    | 654321   | cccccccccc | dev2@app.org |  2 |
    And there are programs:
      | id | name       | description | owned by | downloads | apk_downloads | views | upload time      | version | language version | private |
      | 1  | program 1  | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.6              | 0       |
      | 2  | program 2  |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   | 999              | 1       |
      | 3  | program 3  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 4  | program 4  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 5  | oldestProg |             | User1    | 133       | 63            | 33    | 01.01.2009 13:00 | 0.8.5   | 0.6              | 0       |
      | 6  | newest     |             | User1    | 133       | 63            | 33    | 01.01.2018 13:00 | 0.8.5   | 0.6              | 0       |
      | 7  | program 7  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 8  | program 8  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 9  | program 9  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 10 | program 10 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 11 | program 11 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 12 | program 12 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 13 | program 13 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 14 | program 14 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 15 | program 15 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 16 | program 16 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 17 | program 17 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 18 | program 18 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 19 | program 19 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 20 | program 20 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 21 | program 21 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 22 | program 22 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 23 | program 23 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 24 | program 24 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 25 | program 25 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 26 | program 26 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 27 | program 27 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 28 | program 28 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |
      | 29 | program 29 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 0.6              | 0       |

    
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/user"
    And I should see "My Profile"

  Scenario: changing my username must work
    Given I click "#edit-username-button"
    And I wait 250 milliseconds
    And I fill in "username" with "Mr.Catro"
    And I click "#save-username"
    And I wait for the server response
    And I should be on "/app/user"
    When I go to "/app/logout"
    And I try to log in as "Catrobat" with the password "123456"
    Then I should see "Your password or username was incorrect."
    And I try to log in as "Mr.Catro" with the password "123456"
    Then I should be logged in

  Scenario: When changing the username the min length must be 3
    Given I click "#edit-username-button"
    And I wait 250 milliseconds
    And I fill in "username" with "Mr"
    And I click "#save-username"
    And I wait for the server response
    And I should be on "/app/user"
    Then I should see "This username is not valid."
    When I go to "/app/logout"
    And I try to log in as "Mr" with the password "123456"
    Then I should see "Your password or username was incorrect."

  Scenario: When changing the username the max length must be 180
    Given I click "#edit-username-button"
    And I wait 250 milliseconds
    And I fill in "username" with "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!+++++"
    When I click "#save-username"
    And I wait for the server response
    Then I should be on "/app/user"
    And I should see "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!"
    And I should not see "ThisUsernameConsistOf185CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180CharsThisUsernameConsistOfMoreThan180Chars!!!+"

  Scenario: changing password must work
    Given I click "#edit-password-button"
    And I wait 250 milliseconds
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "abcdef"
    And I click "#save-password"
    And I wait for the server response
    And I should be on "/app/user"
    When I go to "/app/logout"
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
    Then I should be on "/app/user"
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
    Then I should be on "/app/user"
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
    Then I should be on "/app/user"
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
    And I wait 500 milliseconds
    Then I should see "Your chosen picture is too large, please do not use images larger than 5mb."

  Scenario: deleting a program should work
    Given I am on "/app/user"
    And I wait 100 milliseconds
    Then I should see "program 1"
    And I should see "program 2"
    When I go to "/app/userDeleteProject/2"
    And I wait 100 milliseconds
    Then I should not see "program 2"
    And I should see "program 1"
    And there should be "29" programs in the database
    When I go to "/app/project/2"
    And I wait 100 milliseconds
    Then I should see "Ooooops something went wrong."

  Scenario: deleting another user's program should not work
    Given I go to "/app/userDeleteProject/3"
    Then I should see "Ooooops something went wrong."

  Scenario: check deletion PopUp
    Given I am on "/app/user"
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
    Given I am on "/app/user"
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

  Scenario: Programs with too high language version can also be set to visible
    Given I am on "/app/user"
    And I wait 100 milliseconds
    And I should see "program 2"
    And the element "#visibility-lock-2" should be visible
    And the element "#visibility-lock-open-2" should not be visible
    When I click "#visibility-lock-open-2"
    And I wait for the server response
    Then the element "#visibility-lock-2" should not be visible
    And the element "#visibility-lock-open-2" should be visible

  Scenario: I should be able to delete my account
    Given I am on "/app/user"
    Then the element "#delete-account-button" should not be visible
    When I click "#account-settings-button"
    And I wait 250 milliseconds
    Then the element "#delete-account-button" should be visible
    When I click "#delete-account-button"
    And I wait 100 milliseconds
    Then I should see "Account Deletion"
    When I click ".swal2-confirm"
    And I wait 100 milliseconds
    Then I should be on "/app/"

  Scenario: I should be able to delete my account with comments
    Given I am on "/app/user"
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

  Scenario: at a profile page there should always all programs be visible
    Given I log in as "User1" with the password "654321"
    And I am on "/app/user"
    Then I should see "program 3"
    And I should see "program 4"
    And I should see "oldestProg"
    And I should see "newest"
    And I should see "program 7"
    And I should see "program 8"
    And I should see "program 9"
    And I should see "program 10"
    And I should see "program 11"
    And I should see "program 12"
    And I should see "program 13"
    And I should see "program 14"
    And I should see "program 15"
    And I should see "program 16"
    And I should see "program 17"
    And I should see "program 18"
    And I should see "program 19"
    And I should see "program 20"
    And I should see "program 21"
    And I should see "program 22"
    And I should see "program 23"
    And I should see "program 24"
    And I should see "program 25"
    And I should see "program 26"
    And I should see "program 27"
    And I should see "program 28"
    And I should see "program 29"

  Scenario: programs should be ordered newest first
    Given I log in as "User1" with the password "654321"
    And I am on "/app/user"
    When I click ".program"
    Then I am on "/app/project/6"

