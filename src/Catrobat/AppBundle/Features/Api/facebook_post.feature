@api
Feature: Post to Facebook

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    |

  @RealFacebook
  Scenario: When a program with with valid data is uploaded, a Facebook post with a link to project should be made
    When I upload a valid program
    Then the project should be posted to Facebook with message "test" and the correct project ID

  @RealFacebook
  Scenario: When a program with valid data and an existing Facebook post is reported, the Facebook post should be removed
    When I upload a valid program
    Then the project should be posted to Facebook with message "test" and the correct project ID
    When I report the program
    Then the Facebook Post should be deleted

  @RealFacebook
  Scenario: When a program with an exising Facebook post is set invisible, then the Facebook post should be removed
    When I upload a valid program
    Then the project should be posted to Facebook with message "test" and the correct project ID
    When program "test" is not visible
    Then the Facebook Post should be deleted
