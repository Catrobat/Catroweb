@web @docs
Feature: Comments and tips below a docs page, moderated like every comment

  Background:
    Given the docs are synced from the test docs repo
    And there are admins:
      | id | name  | password |
      | 9  | Admin | 123456   |
    And there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And there are comments:
      | id                                   | doc_path       | user_id | text       | upload_date         | parent_id                            |
      | 00000000-0000-0000-0000-000000000010 | tutorials/jump | 1       | jump tip   | 2013-01-01 12:00:00 |                                      |
      | 00000000-0000-0000-0000-000000000011 | tutorials/jump | 2       | jump reply | 2013-01-02 12:00:00 | 00000000-0000-0000-0000-000000000010 |

  Scenario: A guest sees the comments of the page and its replies, in every language
    When I go to "/app/docs/tutorials/jump?locale=de_DE"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#comment-00000000-0000-0000-0000-000000000010" to contain "jump tip"
    And I should not see "jump reply"
    When I click "#comment-00000000-0000-0000-0000-000000000010 .comment-replies-button"
    And I wait for AJAX to finish
    Then I wait for the element "#comment-00000000-0000-0000-0000-000000000011" to contain "jump reply"

  Scenario: A guest's comment waits for the login and is posted after it
    When I go to "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    And I fill in the element ".docs-comments__input" with "tip from a guest"
    And I click ".docs-comments__send"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    When I log in as "Catrobat"
    And I go to "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I click ".docs-comments__send"
    And I wait for AJAX to finish
    Then I wait for the element ".docs-comments__list" to contain "tip from a guest"

  Scenario: A logged-in user comments and replies
    Given I log in as "User2"
    When I go to "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I fill in the element ".docs-comments__input" with "my new tip"
    And I click ".docs-comments__send"
    And I wait for AJAX to finish
    Then I wait for the element ".docs-comments__list" to contain "my new tip"
    When I click "#comment-00000000-0000-0000-0000-000000000010 .comment-reply-button"
    And I fill in the element ".docs-comments__input" with "my reply"
    And I click ".docs-comments__send"
    And I wait for AJAX to finish
    Then I wait for the element "#comment-00000000-0000-0000-0000-000000000010 .docs-comments__replies" to contain "my reply"

  Scenario: A reader reports a comment into the moderation queue
    Given I log in as "User2"
    When I go to "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#comment-00000000-0000-0000-0000-000000000010" to contain "jump tip"
    And I click "#comment-00000000-0000-0000-0000-000000000010 .comment-report-button"
    And I wait 500 milliseconds
    And I click ".report-dialog__option[data-report-category='spam']"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I wait for the element ".swal2-popup" to contain "Your report has been submitted."
    And moderation reports for "comment" "00000000-0000-0000-0000-000000000010" should all have state "new"

  Scenario: An admin hides the reported comment and it leaves the page
    Given there are moderation reports:
      | id                                   | reporter | content_type | content_id                           | category | state | created_at          |
      | 00000000-0000-0000-0000-000000000401 | User2    | comment      | 00000000-0000-0000-0000-000000000010 | spam     | new   | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/list"
    And I wait for the page to be loaded
    When I fill in "message" with "Please keep tips on topic"
    And I click on the button named "Hide + warn"
    And I wait for the page to be loaded
    Then moderation report "00000000-0000-0000-0000-000000000401" should have state "accepted"
    And the comment "00000000-0000-0000-0000-000000000010" should be hidden
    When I go to "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element "#comment-00000000-0000-0000-0000-000000000010" should not exist
