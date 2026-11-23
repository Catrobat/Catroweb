@api @docs
Feature: Comments and tips on docs pages, /api/docs/pages/{path}/comments

  Background:
    Given the docs are synced from the test docs folder
    And there are admins:
      | id | name       |
      | 9  | Maintainer |
    And there are users:
      | id | name       |
      | 1  | Catrobat   |
      | 2  | User2      |
      | 3  | Unverified |
      | 4  | Suspended  |
    And the users are created at:
      | name     | created_at          |
      | Catrobat | 2024-01-01 12:00:00 |
      | User2    | 2024-01-01 12:00:00 |
    And the users are unverified:
      | name       |
      | Unverified |
    And the users are profile-hidden:
      | name      |
      | Suspended |
    And there are comments:
      | id                                   | doc_path       | user_id | text          | upload_date         | parent_id                            |
      | 00000000-0000-0000-0000-000000000010 | tutorials/jump | 1       | jump tip      | 2013-01-01 12:00:00 |                                      |
      | 00000000-0000-0000-0000-000000000011 | tutorials/jump | 2       | jump question | 2013-01-02 12:00:00 |                                      |
      | 00000000-0000-0000-0000-000000000012 | tutorials/jump | 2       | jump reply    | 2013-01-03 12:00:00 | 00000000-0000-0000-0000-000000000010 |
      | 00000000-0000-0000-0000-000000000013 | index          | 1       | start tip     | 2013-01-04 12:00:00 |                                      |

  Scenario: A guest sees the comments of a page, newest first, without replies
    When I request "GET" "/api/docs/pages/tutorials/jump/comments?limit=1"
    Then the response status code should be "200"
    And the JSON response field "data.0.message" should be '"jump question"'
    And the JSON response field "has_more" should be 'true'
    And the client response should not contain "jump reply"
    And the client response should not contain "start tip"

  Scenario: The second page follows the cursor
    When I request "GET" "/api/docs/pages/tutorials/jump/comments?limit=1"
    And I save the next_cursor from the response
    And I request page 2 with the saved cursor at "/api/docs/pages/tutorials/jump/comments?limit=1"
    Then the response status code should be "200"
    And the JSON response field "data.0.message" should be '"jump tip"'
    And the JSON response field "data.0.reply_count" should be '1'
    And the JSON response field "has_more" should be 'false'

  Scenario: Replies of a docs comment are public
    When I request "GET" "/api/comments/00000000-0000-0000-0000-000000000010/replies"
    Then the response status code should be "200"
    And the JSON response field "data.0.message" should be '"jump reply"'

  Scenario: A page that does not exist has no comments
    When I request "GET" "/api/docs/pages/no/such/page/comments"
    Then the response status code should be "404"

  Scenario: A logged-in user comments, and the docs maintainers are notified
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Press the green flag first"}
      """
    When I request "POST" "/api/docs/pages/tutorials/jump/comments"
    Then the response status code should be "201"
    And the JSON response field "message" should be '"Press the green flag first"'
    When I use a valid JWT Bearer token for "Maintainer"
    And I request "GET" "/api/notifications?type=comment"
    Then the response status code should be "200"
    And the client response should contain "commented on a docs page: Make your cat jump"
    And the client response should contain "jump#comments"

  Scenario: A logged-in user replies
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Thanks!", "parent_id": "00000000-0000-0000-0000-000000000010"}
      """
    When I request "POST" "/api/docs/pages/tutorials/jump/comments"
    Then the response status code should be "201"
    And the JSON response field "parent_id" should be '"00000000-0000-0000-0000-000000000010"'

  Scenario: A reply must be on the same page as its parent
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Wrong page", "parent_id": "00000000-0000-0000-0000-000000000013"}
      """
    When I request "POST" "/api/docs/pages/tutorials/jump/comments"
    Then the response status code should be "400"

  Scenario: A guest cannot comment
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello"}
      """
    When I request "POST" "/api/docs/pages/tutorials/jump/comments"
    Then the response status code should be "401"

  Scenario: An unverified user cannot comment
    Given I use a valid JWT Bearer token for "Unverified"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello"}
      """
    When I request "POST" "/api/docs/pages/tutorials/jump/comments"
    Then the response status code should be "403"
    And the client response should contain "Email verification required."

  Scenario: A suspended user cannot comment
    Given I use a valid JWT Bearer token for "Suspended"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello"}
      """
    When I request "POST" "/api/docs/pages/tutorials/jump/comments"
    Then the response status code should be "403"
    And the client response should contain "Your account has been suspended."

  Scenario: A docs comment is reported like any comment and leaves the list when hidden
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam", "note": "Not a tip"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "201"
    Given the comment "00000000-0000-0000-0000-000000000010" is auto-hidden
    When I request "GET" "/api/docs/pages/tutorials/jump/comments"
    Then the client response should not contain "jump tip"

  Scenario: Brick reference pages take comments
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Set x to 0 puts the actor in the middle"}
      """
    When I request "POST" "/api/docs/pages/reference/bricks/SetXBrick/comments"
    Then the response status code should be "201"
    When I request "GET" "/api/docs/pages/reference/bricks/SetXBrick/comments"
    Then the JSON response field "data.0.message" should be '"Set x to 0 puts the actor in the middle"'
    When I request "GET" "/api/docs/pages/reference/bricks/not-a-type/comments"
    Then the response status code should be "404"
