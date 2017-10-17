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
      And I should see "Report as inappropriate"
      And I should see "more than one year ago"
      And I should see "0.00 MB"
      And I should see "5 downloads"
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

    Scenario: I want a link to this program
      Given I am on "/pocketcode/program/1"
      Then the element "#copy-link input" should not be visible
      When I click "#copy-link"
      Then the element "#copy-link input" should be visible
      And the element "#copy-link tr:nth-child(1)" should not be visible
      And the copy link should be "pocketcode/program/1"

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
      Then I should see "Dapiest"