Feature: Limited Accounts

  In a gamejam for schools, a class may use the same account for all students

  In order to prevent students from deleting programs that belong to another student or hijaking the account
  As a teacher
  I need the possibility to prevent program deletions and password changes


  @web
  Scenario: My Profile of a limited account does not show edit fields

    Given I am logged in
    And I have a limited account
    When I visit my profile
    Then I do not see a form to edit my profile
    And I do not see a button to change the profile picture


  @javascript
  Scenario: My Profile of a limited account does not allow deletion of programs

    Given I am logged in
    And I have a limited account
    And I have a program named "Pink Pony"
    When I visit my profile
    Then I see the program "Pink Pony"
    But I do not see a delete button
     