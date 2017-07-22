@homepage
Feature: As a visitor I want to write, see and report comments.

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |
      | Gregor   | 123456   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready | fb_post_url                                                                          |
      | 1  | program 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             |  true   | true      | https://www.facebook.com/permalink.php?story_fbid=424543024407491&id=403594093169051 |
      | 2  | program 2 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | true      |                                                                                      |
      | 3  | program 3 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | true      |                                                                                      |

    And there are comments:
      | program_id | user_id | upload_date      | text                | user_name | reported |
      | 1          | 0       | 01.01.2013 12:01 | 1 | Superman  | true     |
      | 1          | 1       | 01.01.2013 12:01 | 2 | Superman  | true     |
      | 2          | 1       | 01.01.2013 12:01 | 3 | Superman  | true     |
      | 2          | 1       | 01.01.2013 12:01 | 4 | Superman  | true     |
      | 2          | 1       | 01.01.2013 12:01 | 5 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 6 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 7 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 8 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 9 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 10 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 11 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 12 | Superman  | true     |
      | 2          | 0       | 01.01.2013 12:01 | 13 | Superman  | true     |

    And there are admins:
      | name     | password | token      | email                |
      | Admin    | 123456   | cccccccccc | admin@pocketcode.org |

  Scenario: I should see user comment wrapper
    Given I am on "/pocketcode/program/1"
    Then I should see "Comments"
    And I should see "Send"

  Scenario: I should not be able to write a comment without being logged in
    Given I am on "/pocketcode/program/1"
    And I write "hello" in textbox
    And I click the "send" button
    And I wait 200 milliseconds
    Then I should be on "/pocketcode/login"

  Scenario: I should be able to write a comment when I am logged in
    Given I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I write "hello" in textbox
    And I click the "send" button
    And I wait for a second
    Then I should see "hello"

  Scenario: I should be able to see existing comments
    Given I am on "/pocketcode/program/1"
    Then I should see "1"

  Scenario: I should not see any comments when there are none
    Given I am on "/pocketcode/program/3"
    Then I should see 0 ".single-comment"

  Scenario: Pressing the show more button should result in more displayed comments
    Given I am on "/pocketcode/program/2"
    And I should see 1 ".single-comment.hidden"
    When I click the "show-more" button
    And I wait for a second
    Then I should see 0 ".single-comment.hidden"

  Scenario: When there are no comments I should not see the show more button
    Given I am on "/pocketcode/program/3"
    Then I should not see "Show More"

  Scenario: When there are more comments to show and I click on show more, the button should vanish
    if there are no more comments afterwards.
    Given I am on "/pocketcode/program/2"
    Then I should see "Show More"
    When I click the "show-more" button
    Then I should not see "Show More"

  Scenario: When I click the report button, I should receive a pop-up
    Given I log in as "Superman" with the password "123456"
    And I am on "/pocketcode/program/2"
    When I click the "report-comment" button
    And I wait for a second
    Then I should see 1 "#comment-successfully-reported"

  Scenario: When I click the report button, I should be redirected to the login page
    Given I am on "/pocketcode/program/2"
    When I click the "report-comment" button
    And I wait 200 milliseconds
    Then I should be on "/pocketcode/login"

  Scenario: When I am logged in as an admin, I should see a delete button
    Given I log in as "Admin" with the password "123456"
    And I am on "/pocketcode/program/2"
    Then I should see "Delete"

  Scenario: When I am logged in as an admin and I delete a comment it should be gone
    Given I log in as "Admin" with the password "123456"
    And I am on "/pocketcode/program/2"
    When I click the "delete-comment" button
    And I wait for a second
    Then I should see 0 "#comment-4"



