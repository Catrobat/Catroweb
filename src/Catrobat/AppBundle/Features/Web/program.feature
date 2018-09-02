@homepage
Feature: As a visitor I want to see a program page

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |
      | Gregor   | 123456   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready | fb_post_url                                                                          |
      | 1  | program 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             |  true   | true      | https://www.facebook.com/permalink.php?story_fbid=424543024407491&id=403594093169051 |
      | 2  | program 2 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | true      |                                                                                      |
    And there are reportable programs:
      | id | name      | owned by | visible | reported |
      | 4  | Dap       | Gregor   | true    | false    |
      | 5  | Dapier    | Gregor   | true    | false    |
      | 6  | Dapiest   | Gregor   | false   | true     |
    And following programs are featured:
      | id | program      | url                   | active | priority |
      | 1  | Dapiest      |                       | yes    | 1        |

    Scenario: Viewing program page
      Given I am on "/pocketcode/program/1"
      Then I should see "program 1"
      And I should see "Superman"
      And I should see "my superman description"
      And I should see "Report program"
      And I should see "more than one year ago"
      And I should see "0.00 MB"
      And I should see "5 downloads"
      And I should see "13 views"

    Scenario: Viewing the uploader's profile page
      Given I am on "/pocketcode/program/1"
      And I click "#program-user a"
      Then I should be on "/pocketcode/profile/1"

    Scenario: report as inappropriate
      Given I am on "/pocketcode/program/1"
      And I click "#report"
      Then I should see "Please login to report this program."
      When I click "#report-container a"
      Then I should be on "/pocketcode/login"
      And I fill in "username" with "Gregor"
      And I fill in "password" with "123456"
      And I press "Login"
      Then I should be on "/pocketcode/program/1#login"
      When I click "#report"
      Then I should see "Why do you want to report this program?"
      And I click the "#report-inappropriate" RadioButton
      And I fill in "reportReason" with "I do not like this program ... hehe"
      When I click "#report-report"
      And I wait for the server response
      Then I should see "You reported this program!"

  Scenario: report as copyright infringement
    Given I am on "/pocketcode/program/1"
    And I click "#report"
    Then I should see "Please login to report this program."
    When I click "#report-container a"
    Then I should be on "/pocketcode/login"
    And I fill in "username" with "Gregor"
    And I fill in "password" with "123456"
    And I press "Login"
    Then I should be on "/pocketcode/program/1#login"
    When I click "#report"
    Then I should see "Why do you want to report this program?"
    And I click the "#report-copyright" RadioButton
    And I fill in "reportReason" with "That was my idea!!!"
    When I click "#report-report"
    And I wait for the server response
    Then I should see "You reported this program!"


  Scenario: report as spam
    Given I am on "/pocketcode/program/1"
    And I click "#report"
    Then I should see "Please login to report this program."
    When I click "#report-container a"
    Then I should be on "/pocketcode/login"
    And I fill in "username" with "Gregor"
    And I fill in "password" with "123456"
    And I press "Login"
    Then I should be on "/pocketcode/program/1#login"
    When I click "#report"
    Then I should see "Why do you want to report this program?"
    And I click the "#report-spam" RadioButton
    And I fill in "reportReason" with "This is spam :D"
    When I click "#report-report"
    And I wait for the server response
    Then I should see "You reported this program!"

  Scenario: report as dislike
    Given I am on "/pocketcode/program/1"
    And I click "#report"
    Then I should see "Please login to report this program."
    When I click "#report-container a"
    Then I should be on "/pocketcode/login"
    And I fill in "username" with "Gregor"
    And I fill in "password" with "123456"
    And I press "Login"
    Then I should be on "/pocketcode/program/1#login"
    When I click "#report"
    Then I should see "Why do you want to report this program?"
    And I click the "#report-dislike" RadioButton
    And I fill in "reportReason" with "I do not like this program ... hehe"
    When I click "#report-report"
    And I wait for the server response
    Then I should see "You reported this program!"

    Scenario: I want a link to this program
      Given I am on "/pocketcode/program/1"
      Then the element ".btn-copy" should be visible

    Scenario: I want to download a program from the browser
      Given I am on "/pocketcode/program/1"
      Then the link of "download" should open "download"

    Scenario: I want to download a program from the app with the correct language version
      Given I am browsing with my pocketcode app
      And I am on "/pocketcode/program/2"
      Then the link of "download" should open "download"

    Scenario: I want to download a program from the app with an an old language version
      Given I am browsing with my pocketcode app
      And I download "/pocketcode/download/1.catrobat"

    Scenario: Increasing download counter after apk download
      Given I am on "/pocketcode/program/1"
      Then I should see "5 downloads"
      When I want to download the apk file of "program 1"
      Then I should receive the apk file
      And I am on "/pocketcode/program/1"
      Then I should see "6 downloads"

    Scenario: Increasing download counter after download
      Given I am on "/pocketcode/program/1"
      Then I should see "5 downloads"
      When I download "/pocketcode/download/1.catrobat"
      Then I should receive an application file
      When I am on "/pocketcode/program/1"
      Then I should see "6 downloads"

    Scenario: Clicking the download button should deactivate the download button for 5 seconds
      Given I am on "/pocketcode/program/1"
       When I click "#url-download"
       Then the href with id "url-download" should be void

    Scenario: Clicking the download button again after 5 seconds should work
      Given I am on "/pocketcode/program/1"
      When I click "#url-download"
      And I wait 5000 milliseconds
      Then the href with id "url-download" should not be void

    Scenario: Clicking on a reported featured program should still show its page
      Given I am on homepage
      Then I should see the featured slider
      When I click on the first featured homepage program
      Then I wait 500 milliseconds
      Then I should see "Dapiest"

    Scenario: Changing description is not possible if not logged in
      Given I am on "/pocketcode/program/1"
      Then the element "#edit-description-button" should not exist
      And the element "#edit-description-ui" should not exist

    Scenario: Changing description is not possible if it's not my program
      Given I am on "/pocketcode/login"
      And I fill in "username" with "Gregor"
      And I fill in "password" with "123456"
      And I press "Login"
      And I am on "/pocketcode/program/1"
      Then the element "#edit-description-button" should not exist
      And the element "#edit-description-ui" should not exist

    Scenario: Changing description is possible if it's my program
      Given I am on "/pocketcode/login"
      And I fill in "username" with "Gregor"
      And I fill in "password" with "123456"
      And I press "Login"
      And I am on "/pocketcode/program/2"
      Then the element "#edit-description-button" should be visible
      When I click "#edit-description-button"
      Then the element "#description" should not be visible
      And the element "#edit-description" should be visible
      And the element "#edit-description-submit-button" should be visible
      When I fill in "edit-description" with "This is a new description"
      And I click "#edit-description-submit-button"
      And I wait 250 milliseconds
      Then the element "#description" should be visible
      And the element "#edit-description-ui" should not be visible
      And I should see "This is a new description"