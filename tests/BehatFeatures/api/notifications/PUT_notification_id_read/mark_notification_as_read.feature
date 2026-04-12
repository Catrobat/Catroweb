@api @notifications
Feature: It should be possible to mark notifications marked as read by id

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are catro notifications:
      | id                                   | user     | type     | message       | title   | seen |
      | 00000000-0000-0000-0000-000000000001 | Catrobat |          |  msg  1       | title1  |   0  |
      | 00000000-0000-0000-0000-000000000002 | Catrobat |          |  msg  2       | title2  |   1  |
      | 00000000-0000-0000-0000-000000000003 | Catrobat |          |  msg  3       | title3  |   0  |


  Scenario: mark notification as read by id
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "PUT" "/api/notifications/00000000-0000-0000-0000-000000000001/read"
    Then the response code should be "204"
    And the following catro notifications exist in the database:
      | id                                   | seen  |
      | 00000000-0000-0000-0000-000000000001 | 1     |
      | 00000000-0000-0000-0000-000000000002 | 1     |


  Scenario: mark non-existent notification an read
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "PUT" "/api/notifications/00000000-0000-0000-0000-000000000005/read"
    Then the response code should be "404"


  Scenario: mark multiple notification as read by id
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "PUT" "/api/notifications/00000000-0000-0000-0000-000000000001/read"
    Then the response code should be "204"
    And I request "PUT" "/api/notifications/00000000-0000-0000-0000-000000000003/read"
    Then the response code should be "204"
    And the following catro notifications exist in the database:
      | id                                   | seen  |
      | 00000000-0000-0000-0000-000000000001 | 1     |
      | 00000000-0000-0000-0000-000000000002 | 1     |
      | 00000000-0000-0000-0000-000000000003 | 1     |
