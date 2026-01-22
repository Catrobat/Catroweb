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
      | 4  | project 4 | OtherUser |
      | 5  | project 5 | OtherUser |

    And there are comments:
      | project_id | user_id | text | parent_id  | is_deleted |
      | 1          | 1       | c1   |  NULL      |            |
      | 2          | 2       | c2   |  NULL      |            |
      | 2          | 2       | c3   |  NULL      |            |
      | 2          | 2       | c4   |  NULL      |            |
      | 2          | 1       | c5   |  NULL      |            |
      | 2          | 1       | c6   |  NULL      |            |
      | 2          | 1       | c7   |  NULL      |            |
      | 2          | 1       | c8   |  NULL      |            |
      | 2          | 1       | c9   |  8         |            |
      | 2          | 1       | c10  |  8         |            |
      | 2          | 1       | c11  |  8         |            |
      | 4          | 2       | c12  |  NULL      |            |
      | 4          | 2       | c13  |  12        |            |
      | 5          | 2       | c14  |  NULL      |   true     |
      | 5          | 2       | c15  |  14        |            |
#      3 has no comments


  Scenario: There should be a commend section on every program page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Comments"
    And the element "#add-comment-button" should be visible

  Scenario: It should be possible to toggle the visibility of the post a comment container
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Comments"
    And the element "#add-comment-button" should be visible
    But the element "#comment-cancel-button" should not be visible
    And the element "#user-comment-wrapper" should not be visible
    When I click "#add-comment-button"
    And I wait for the page to be loaded
    Then the element "#add-comment-button" should not be visible
    But the element "#comment-cancel-button" should be visible
    And the element "#user-comment-wrapper" should be visible

  Scenario: I should not be able to write a comment without being logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for the page to be loaded
    Then I should be on "/app/login"


  Scenario: If a logged out user enters a comment into the textbox, it should be remembered throughout the login process and page reloads
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#add-comment-button"
    And I wait for AJAX to finish
    And I write "comment to remember" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    Then I press "Login"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1#login"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And the element "#add-comment-button" should not be visible
    And the element "#comment-cancel-button" should be visible
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
    When I click "#add-comment-button"
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

  Scenario: I should be able to see the number of replies that belong to a comment - comment with zero replies
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "c1"
    And one of the "#comment-1 .comment-replies-count span" elements should contain "0"
    But I should not see "c2"

  Scenario: I should be able to see the number of replies that belong to a comment - comment with more than one reply
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "c2"
    And I should see "c3"
    And I should see "c4"
    And I should see "c5"
    And I should see "c6"

    And none of the ".comment-replies-count span" elements should contain "3"
    But I should not see "c7"
    And I should not see "c8"
    And I should not see "c9"
    And I should not see "c10"
    And I should not see "c11"

    When I scroll to the bottom of the page
    And I wait 500 milliseconds

    Then I should see "c7"
    And I should see "c8"
    And one of the ".comment-replies-count span" elements should contain "3"
    But I should not see "c9"
    But I should not see "c10"
    But I should not see "c11"

  Scenario: I should be able to see the number of replies that belong to a comment - comment with one reply
    Given I am on "/app/project/4"
    And I wait for the page to be loaded
    Then I should see "c12"
    And one of the "#comment-12 .comment-replies-count span" elements should contain "1"

  Scenario: I should be able to see the deleted comments, however, not their text.
    Given I am on "/app/project/5"
    And I wait for the page to be loaded
    And one of the "#comment-14 .comment-text" elements should contain "**Deleted**"
    And one of the "#comment-14 .comment-replies-count span" elements should contain "1"

    And the element ".comment-translation-button" should not exist
    And the element ".comment-report-button" should not exist
    And the element ".comment-delete-button" should not exist

  Scenario: I should not see any comments when there are none
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then the element ".single-comment" should not exist

  Scenario: I should be able to see only the first 5 existing comments (order newest first) without scrolling
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "c2"
    And I should see "c3"
    And I should see "c4"
    And I should see "c5"
    And I should see "c6"
    But I should not see "c7"

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
    And I click "#comment-report-button-1"
    And I wait 500 milliseconds
    Then I should see "Are you sure?"
    When I click ".swal2-confirm"
    And I wait 500 milliseconds
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

  Scenario: When I am logged in as an admin and I delete a comment it should be gone, but there should be a confirmation pop up
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
    And I click "#add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "hello"
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for AJAX to finish
    Then the element "#catro-notification-1" should be visible
    And I should see "OtherUser"

  Scenario: I should be able to write a comment for my own program but I wont get a notification
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "hello"
    When I am on "/app/user_notifications"
    Then the element "#catro-notification-1" should not exist
