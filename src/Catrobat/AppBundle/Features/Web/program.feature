@homepage
Feature: As a visitor I want to see a program page

  Background:
    Given there are users:
      | name     | password | token      |
      | Superman | 123456   | cccccccccc |
      | Gregor   | 123456   | cccccccccc |
    And there are programs:
      | id | name      | description             | owned by | downloads | views | upload time      | version | visible |
      | 1  | program 1 | my superman description | Superman | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    |
      | 2  | program 2 | abcef                   | Gregor   | 333       | 9     | 22.04.2014 13:00 | 0.8.5   | false   |

    Scenario: Viewing program page
      Given I am on "/pocketcode/program/1"
      Then I should see "program 1"
      And I should see "Superman"
      And I should see "my superman description"
      And I should see "Report as inappropriate"
      And I should see "Catrobat Language version: 1"
      And I should see "more than one year ago"
      And I should see "0.00 MB"
      And I should see "3 downloads"
      And I should see "13 views"

    Scenario: Viewing the uploader's profile page
      Given I am on "/pocketcode/program/1"
      And I click "#program-user a"
      Then I should be on "/pocketcode/profile/1"

    Scenario: report as inapropriate
      Given I am on "/pocketcode/program/1"
      And I click "#report"
      Then I should see "Please login to report this program as inappropriate."
      When I click "#report-container a"
      Then I should be on "/pocketcode/login"
      And I fill in "username" with "Gregor"
      And I fill in "password" with "123456"
      And I press "Login"
      Then I should be on "/pocketcode/program/1#login"
      When I click "#report"
      Then I should see "Why do you think this program is inappropriate?"
      And I fill in "reportReason" with "I do not like this program ... hehe"
      When I click "#report-report"
      And I wait for the server response
      Then I should see "You reported this program as inappropriate!"