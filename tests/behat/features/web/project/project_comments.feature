@web @project_page
Feature: As a visitor I want to write, see and report comments.

  Background:
    Given there are users:
      | id | name      | admin |
      | 1  | Catrobat  | false |
      | 2  | OtherUser | false |
      | 3  | Admin     | true  |

    And there are projects:
      | id | name      | owned by  |
      | 1  | project 1 | Catrobat  |
      | 2  | project 2 | OtherUser |
      | 3  | project 3 | OtherUser |

    And there are comments:
      | program_id | user_id | upload_date      | text | user_name | reported |
      | 1          | 1       | 01.01.2013 12:01 | c1   | Catrobat  | true     |
      | 2          | 2       | 01.01.2013 12:01 | c2   | OtherUser | true     |
      | 2          | 2       | 01.01.2013 12:01 | c3   | OtherUser | true     |
      | 2          | 2       | 01.01.2013 12:01 | c4   | OtherUser | true     |
      | 2          | 1       | 01.01.2013 12:01 | c5   | Catrobat  | true     |
      | 2          | 1       | 01.01.2013 12:01 | c6   | Catrobat  | true     |
      | 2          | 1       | 01.01.2013 12:01 | c7   | Catrobat  | true     |
      | 2          | 1       | 01.01.2013 12:01 | c8   | Catrobat  | true     |
#      3 has no comments


  Scenario: There should be a commend section on every program page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Comments"
    And the element ".add-comment-button" should be visible

  Scenario: It should be possible to toggle the visibility of the post a comment container
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Comments"
    And the element "#show-add-comment-button" should be visible
    But the element "#hide-add-comment-button" should not be visible
    And the element "#user-comment-wrapper" should not be visible
    When I click "#show-add-comment-button"
    And I wait for AJAX to finish
    Then the element "#show-add-comment-button" should not be visible
    But the element "#hide-add-comment-button" should be visible
    And the element "#user-comment-wrapper" should be visible

  Scenario: I should not be able to write a comment without being logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#show-add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for the page to be loaded
    Then I should be on "/app/login"


  Scenario: If a logged out user enters a comment into the textbox, it should be remembered throughout the login process and page reloads
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#show-add-comment-button"
    And I wait for AJAX to finish
    And I write "comment to remember" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then I press "Login"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1#login"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And the element "#show-add-comment-button" should not be visible
    And the element "#hide-add-comment-button" should be visible
    And the element "#user-comment-wrapper" should be visible
    And I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "comment to remember"
    And the element ".single-comment" should be visible

  Scenario: I should be able to write a comment when I am logged in
    Given I log in as "Catrobat"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    Then the element ".single-comment" should not exist
    When I click "#show-add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    And I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "hello"
    And the element ".single-comment" should be visible

  Scenario: I should be able to see existing comments
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "c1"
    But I should not see "c2"

  Scenario: I should not see any comments when there are none
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then the element ".single-comment" should not exist

  Scenario: When there are less than 5 comments i should not see the show more/less buttons
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#show-more-comments-button" should not be visible
    And the element "#show-more-comments-button" should not be visible

  Scenario: I should be able to see only the first 5 existing comments (order newest first)
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "c8"
    And I should see "c7"
    And I should see "c6"
    And I should see "c5"
    And I should see "c4"
    But I should not see "c3"
    And the element "#show-more-comments-button" should be visible
    But the element "#show-less-comments-button" should not be visible

  Scenario: Pressing the show more/less button should result in more/less displayed comments
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    And I should not see "c3"
    When I click "#show-more-comments-button"
    And I wait for AJAX to finish
    Then I should see "c3"
    And the element "#show-less-comments-button" should be visible
    But the element "#show-more-comments-button" should not be visible
    When I click "#show-less-comments-button"
    And I wait for AJAX to finish
    Then I should not see "c3"
    And the element "#show-less-comments-button" should not be visible
    But the element "#show-more-comments-button" should be visible

  Scenario: I can't report my own comment
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element ".comment-report-button" should not exist

  Scenario: When I click the report button and I am not logged in, I should be redirected to the login page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click ".comment-report-button"
    And I wait for AJAX to finish
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should be on "/app/login"

  Scenario: There should be a confirmation pop up to report comments
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click ".comment-report-button"
    And I wait for AJAX to finish
    Then I should see "Are you sure?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Reported"

  Scenario: When I am logged in as an admin, I should see a delete button
    Given I log in as "Admin"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element ".comment-delete-button" should be visible

  Scenario: I should see a delete button only for my own comments when I am no admin
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element ".comment-delete-button" should be visible
    When I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element ".comment-delete-button" should not exist

  Scenario: When I am logged in as an admin and I delete a comment it should be gone, but there
  should be a confirmation pop up
    Given I log in as "Admin"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "c1"
    When I click ".comment-delete-button"
    And I wait for AJAX to finish
    Then I should see "Are you sure?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Deleted"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should not see "c1"

  Scenario: I should be able to write a comment when I am logged in and it should notify the owner
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#show-add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "hello"
    When I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for AJAX to finish
    Then the element "#catro-notification-1" should be visible
    And I should see "OtherUser"

  Scenario: I should be able to write a comment for my own program but I wont get a notification
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#show-add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "hello"
    When I am on "/app/notifications/allNotifications"
    Then the element "#catro-notification-1" should not exist