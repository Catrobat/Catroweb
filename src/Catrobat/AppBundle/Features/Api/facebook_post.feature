@api
Feature: Post to Facebook

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |

  Scenario: When a program with with valid data is uploaded, a Facebook post with a link to project should be made
    Given I use the real FacebookPostService
    When I upload a valid program
    And I make a real Facebook post
    Then the project should be posted to Facebook with message "test" and the correct project ID


  Scenario: When a program with valid data and an existing Facebook post is reported, the Facebook post should be removed
    Given I use the real FacebookPostService
    When I upload a valid program
    And I make a real Facebook post
    Then the project should be posted to Facebook with message "test" and the correct project ID
    When I report the program
    And I really delete the Facebook post
    Then the Facebook Post should be deleted
