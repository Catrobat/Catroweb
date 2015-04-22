@homepage
Feature:
  In order to manage the my profile page
  As a logged in user
  I want to be able to change my password, email and country, upload an avatar image and delete my own programs

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 123456    | cccccccccc |
      | User1    | 654321    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/profile"

  Scenario: changing password must work
    Given I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "abcdef"
    And I press "save changes"
    Then I wait for the server response
    And I should see "saved!"
    When I go to "/logout"
    And I try to log in as "Catrobat" with the password "123456"
    Then I should see "Your password or username was incorrect."
    When I log in as "Catrobat" with the password "abcdef"
    Then I should be logged in

  Scenario: changing password with a typo in repeat-password should not work
    Given I fill in "password" with "abcdef"
    And I fill in "repeat-password" with "fedcba"
    And I press "save changes"
    Then I should see "The passwords didn't match."

  Scenario: a short password should not work
    Given I fill in "password" with "abc"
    And I fill in "repeat-password" with "abc"
    And I press "save changes"
    And I wait for the server response
    Then I should see "The new password must have at least 6 characters."

  Scenario: too long password should not work
    Given I fill in "password" with "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz"
    And I fill in "repeat-password" with "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz"
    And I press "save changes"
    And I wait for the server response
    Then I should see "The new password can have a maximum of 32 characters."

  Scenario: changing email and additional email should work
    Given I fill in "email" with "first@email.com"
    And I fill in "additional-email" with "second@email.com"
    And I press "save changes"
    And I wait for the server response
    Then I should see "saved!"
    When I reload the page
    Then the "email" field should contain "first@email.com"
    And the "additional-email" field should contain "second@email.com"

  Scenario: chaning email adresses with an invalid email should not work
    Given I fill in "email" with "first"
    And I press "save changes"
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second"
    And I press "save changes"
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email"
    And I press "save changes"
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second@email"
    And I press "save changes"
    Then I should see "This email address is not valid."
    When I fill in "email" with "first@email.comcomcom"
    And I press "save changes"
    And I wait for the server response
    Then I should see "This email address is not valid."
    When I fill in "additional-email" with "second@email.comcomcom"
    And I press "save changes"
    And I wait for the server response
    Then I should see "This email address is not valid."

  Scenario: deleting addition email should work but deleting first mail without an additional email should not work
    Given I fill in "email" with "first@email.com"
    And I fill in "additional-email" with "second@email.com"
    And I press "save changes"
    And I wait for the server response
    Then I should see "saved!"
    When I fill in "additional-email" with ""
    And I press "save changes"
    And I wait for the server response
    Then I should see "saved!"
    When I reload the page
    Then the "email" field should contain "first@email.com"
    And the "additional-email" field should contain ""
    When I fill in "email" with ""
    And I press "save changes"
    And I wait for the server response
    Then I should see "Error while updating this e-mail address. You must have at least one validated e-mail address."

  Scenario: when deleting the first mail, the additional mail should become the first mail
    Given I fill in "email" with ""
    And I fill in "additional-email" with "second@email.com"
    And I press "save changes"
    And I wait for the server response
    Then I reload the page
    And the "email" field should contain "second@email.com"
    And the "additional-email" field should contain ""

  Scenario: changing country should work
    Given I select "Austria" from "country"
    And I press "save changes"
    And I wait for the server response
    Then I should see "saved!"
    And I reload the page
    Then "AT" must be selected in "country"

  Scenario: uploading avatar should work
    Given I attach the avatar "logo.png" to "file"
    And I wait for the server response
    Then the avatar img tag should have the "logo.png" data url
    When I reload the page
    Then the avatar img tag should have the "logo.png" data url

#todo:  Scenario: when chaning avatar, it should also appear in the header (desktop + mobile)

  #todo: try not to reload the page, but check if the text "This image type is not supported, ..." is visible
  Scenario: only jpg, png or gif allowed for avatar
    Given I attach the avatar "fail.tif" to "file"
    And I wait for the server response
    When I reload the page
    Then the avatar img tag should not have the "fail.tif" data url

  Scenario: max. 5MB for avatar image
    Given I attach the avatar "galaxy_big.png" to "file"
    Then I should see "Your chosen picture is too large, please do not use images larger than 5mb."

  Scenario: deleting a program should work
    Given I should see "program 1"
    And I should see "program 2"
    When I go to "/profileDeleteProgram/2"
    Then I should not see "program 2"
    And I should see "program 1"
    And there should be "3" programs in the database

#todo:  Scenario: deleting a program should work (with confirm message)

  Scenario: deleting another user's program should not work
    Given I go to "/profileDeleteProgram/3"
    Then I should see "Ooooops something went wrong."

