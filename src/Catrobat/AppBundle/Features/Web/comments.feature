@homepage
Feature: As a visitor I want to write, see and report comments.

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |
      | Gregor   | 123456   | cccccccccc | dev2@pocketcode.org |

    And there are admins:
      | name  | password | token      | email                |
      | Admin | 123456   | cccccccccc | admin@pocketcode.org |

    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready | fb_post_url                                                                          |
      | 1  | program 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      | https://www.facebook.com/permalink.php?story_fbid=424543024407491&id=403594093169051 |
      | 2  | program 2 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |                                                                                      |
      | 3  | program 3 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |                                                                                      |

    And there are comments:
      | program_id | user_id | upload_date      | text | user_name | reported |
      | 1          | 0       | 01.01.2013 12:01 | c1   | Superman  | true     |
      | 2          | 1       | 01.01.2013 12:01 | c2   | Gregor    | true     |
      | 2          | 1       | 01.01.2013 12:01 | c3   | Gregor    | true     |
      | 2          | 1       | 01.01.2013 12:01 | c4   | Gregor    | true     |
      | 2          | 0       | 01.01.2013 12:01 | c5   | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | c6   | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | c7   | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | c8   | Superman  | true     |
#      3 has no comments



  Scenario: There should be a commend section on every program page
    Given I am on "/pocketcode/program/1"
    Then I should see "Comments"
    And I should see "Send"

  Scenario: I should not be able to write a comment without being logged in
    Given I am on "/pocketcode/program/1"
    And I write "hello" in textbox
    And I click "#comment-post-button"
    And I wait 200 milliseconds
    Then I should be on "/pocketcode/login"

  Scenario: I should be able to write a comment when I am logged in
    Given I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/program/3"
    And the element ".single-comment" should not exist
    And I write "hello" in textbox
    And I click "#comment-post-button"
    And I wait for a second
    Then I should see "hello"
    And the element ".single-comment" should be visible

  Scenario: I should be able to see existing comments
    Given I am on "/pocketcode/program/1"
    Then I should see "c1"

  Scenario: I should not see any comments when there are none
    Given I am on "/pocketcode/program/3"
    Then the element ".single-comment" should not exist

  Scenario: When there are less than 5 program i should not see the show more/less buttons
    Given I am on "/pocketcode/program/1"
    Then the element "#show-more-comments-button" should not be visible
    And the element "#show-more-comments-button" should not be visible

  Scenario: I should be able to see only the first 5 existing comments (order newest first)
    Given I am on "/pocketcode/program/2"
    Then I should see "c8"
    And I should see "c7"
    And I should see "c6"
    And I should see "c5"
    And I should see "c4"
    And I should not see "c3"
    And the element "#show-more-comments-button" should be visible
    And the element "#show-less-comments-button" should not be visible

  Scenario: Pressing the show more button should result in more displayed comments
    Given I am on "/pocketcode/program/2"
    And I should not see "c3"
    When I click "#show-more-comments-button"
    And I wait 200 milliseconds
    Then I should see "c3"
    And the element "#show-more-comments-button" should not be visible
    And the element "#show-less-comments-button" should be visible

  Scenario: Pressing the show less button should result in less displayed comments
    Given I am on "/pocketcode/program/2"
    And I click "#show-more-comments-button"
    And I wait 1000 milliseconds
    Then I should see "c3"
    When I click "#show-less-comments-button"
    And I wait 200 milliseconds
    Then I should not see "c3"
    And the element "#show-more-comments-button" should be visible
    And the element "#show-less-comments-button" should not be visible

  Scenario: I can't report my own comment
    Given I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/program/1"
    Then the element ".comment-report-button" should not exist

  Scenario: When I click the report button, I should be redirected to the login page
    Given I am on "/pocketcode/program/1"
    When I click ".comment-report-button"
    And I wait 200 milliseconds
    When I click ".swal2-confirm"
    And I wait 200 milliseconds
    Then I should be on "/pocketcode/login"

  Scenario: There should be a confirmation pop up to report comments
    Given I log in as "Gregor" with the password "123456"
    And I am on "/pocketcode/program/1"
    When I click ".comment-report-button"
    And I wait 200 milliseconds
    Then I should see "Are you sure?"
    When I click ".swal2-confirm"
    And I wait 200 milliseconds
    Then I should see "Reported"

  Scenario: When I am logged in as an admin, I should see a delete button
    Given I log in as "Admin" with the password "123456"
    And I am on "/pocketcode/program/1"
    Then the element ".comment-delete-button" should be visible

  Scenario: I should see a delete button only for my own comments when I am no admin
    Given I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/program/1"
    Then the element ".comment-delete-button" should be visible
    When I log in as "Gregor" with the password "123456"
    And I am on "/pocketcode/program/1"
    Then the element ".comment-delete-button" should not exist

  Scenario: When I am logged in as an admin and I delete a comment it should be gone, but there
  should be a confirmation pop up
    Given I log in as "Admin" with the password "123456"
    And I am on "/pocketcode/program/1"
    Then I should see "c1"
    When I click ".comment-delete-button"
    And I wait for a second
    Then I should see "Are you sure?"
    When I click ".swal2-confirm"
    And I wait 200 milliseconds
    Then I should see "Deleted"
    When I click ".swal2-confirm"
    And I wait 200 milliseconds
    Then I should not see "c1"

  Scenario: I should be able to write a comment when I am logged in and it should notify the owner
    Given I log in as "Gregor" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I write "hello" in textbox
    When I click "#comment-post-button"
    And I wait for a second
    Then I should see "hello"
    When I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    Then the element "#catro-notification-1" should be visible
    And I should see "Gregor"

  Scenario: I should be able to write a comment for my own program but I wont get a notification
    Given I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I write "hello" in textbox
    When I click "#comment-post-button"
    And I wait for a second
    Then I should see "hello"
    When I am on "/pocketcode/user/notifications"
    Then the element "#catro-notification-1" should not exist