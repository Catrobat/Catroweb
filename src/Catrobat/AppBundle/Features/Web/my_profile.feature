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
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |

    And I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/profile"

  Scenario: changing password must work
    Given I click the "edit" button
    And I wait for the server response
    And I click the "password-edit" button
    And I wait for the server response
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "abcdef"
    And I click the "save-edit" button
    And I wait for the server response
    And I should be on "/pocketcode/profile/0/edit"
    When I go to "/logout"
    And I try to log in as "Catrobat" with the password "123456"
    Then I should see "Your password or username was incorrect."
    When I log in as "Catrobat" with the password "abcdef"
    Then I should be logged in

  Scenario: changing password with a typo in repeat-password should not work
    Given I am on "/pocketcode/passwordEdit"
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "fedcba"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "The passwords didn't match."

  Scenario: a short password should not work
    Given I am on "/pocketcode/passwordEdit"
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abc"
    And I fill in "repeat-password" with "abc"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "The new password must have at least 6 characters."

  Scenario: too long password should not work
    Given I am on "/pocketcode/passwordEdit"
    And I fill in "old-password" with "123456"
    And I fill in "password" with "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz"
    And I fill in "repeat-password" with "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "The new password can have a maximum of 32 characters."

  Scenario: changing email and additional email should work
    Given I click the "edit" button
    And I wait for the server response
    And I click the "email-edit" button
    And I wait for the server response
    And I fill in "email" with "first@email.com"
    And I fill in "additional-email" with "second@email.com"
    When I click the "save-edit" button
    And I wait for the server response
    Then I should be on "/pocketcode/profile/0/edit"
    And I should see "first@email.com"
    And I should see "second@email.com"

  Scenario: changing email adresses with an invalid email should not work
    Given I am on "/pocketcode/emailEdit"
    And I fill in "email" with "first"
    When I click the "save-edit" button
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second@email"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email.comcomcom"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second@email.comcomcom"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "This email address is not valid."

  Scenario: deleting first mail without an additional email should not work
    Given I am on "/pocketcode/emailEdit"
    When I fill in "email" with ""
    And I click the "save-edit" button
    And I wait for the server response
    Then I should see "Error while updating this e-mail address. You must have at least one validated e-mail address."

  Scenario: when deleting the first mail, the additional mail should become the first mail
    Given I am on "/pocketcode/emailEdit"
    And I fill in "email" with ""
    And I fill in "additional-email" with "second@email.com"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should be on "/pocketcode/profile/0/edit"
    And the "#email-text" element should contain "second@email.com"

  Scenario: changing country should work
    Given I click the "edit" button
    And I wait for the server response
    And I click the "country-edit" button
    And I wait for the server response
    Given I select "Austria" from "country"
    And I click the "save-edit" button
    And I wait for the server response
    Then I should be on "/pocketcode/profile/0/edit"
    And the "#country-text" element should contain "Austria"

  Scenario: uploading avatar should work
    Given I click the "edit" button
    And I wait for the server response
    And I click the "avatar-edit" button
    And I wait for the server response
    Given I attach the avatar "logo.png" to "file"
    And I wait for the server response
    Then the avatar img tag should have the "logo.png" data url

#todo:  Scenario: when chaning avatar, it should also appear in the header (desktop + mobile)

  #todo: try not to reload the page, but check if the text "This image type is not supported, ..." is visible
  Scenario: only jpg, png or gif allowed for avatar
    Given I am on "/pocketcode/avatarEdit"
    And I attach the avatar "fail.tif" to "file"
    And I wait for the server response
    When I reload the page
    Then the avatar img tag should not have the "fail.tif" data url

  Scenario: max. 5MB for avatar image
    Given I am on "/pocketcode/avatarEdit"
    And I attach the avatar "galaxy_big.png" to "file"
    Then I should see "Your chosen picture is too large, please do not use images larger than 5mb."

  Scenario: deleting a program should work
    Given I should see "program 1"
    And I should see "program 2"
    When I go to "/pocketcode/profileDeleteProgram/2"
    Then I should not see "program 2"
    And I should see "program 1"
    And there should be "3" programs in the database
    When I go to "/pocketcode/program/2"
    Then I should see "Ooooops something went wrong."

#todo:  Scenario: deleting a program should work (with confirm message)

  Scenario: deleting another user's program should not work
    Given I go to "/pocketcode/profileDeleteProgram/3"
    Then I should see "Ooooops something went wrong."

