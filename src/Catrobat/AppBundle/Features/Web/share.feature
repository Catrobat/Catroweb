@homepage
Feature: Sharing and Liking of programs
  I want to be able to like the Website on Google+ and Facebook and share programs on Google+, Facebook, Twitter, WhatsApp and by e-mail.

  Background:
    Given there are users:
      | name            | password  | token      | email                           |
      | Catrobat        | 123456    | cccccccccc | dev1@pocketcode.org             |
    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready | fb_post_url                                                                          |
      | 1  | program 1 | my superman description | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             |  true   | true      | https://www.facebook.com/permalink.php?story_fbid=424543024407491&id=403594093169051 |

  Scenario: In a non-mobile browser the Facebook like button and Google+1 button should be visible in the header
      Given I am on "/pocketcode/program/1"
      Then I should see "program 1"
      And I should see the Facebook Like button in the header
      And I should see the Google Plus 1 button in the header

  Scenario: In a mobile browser the Facebook like button and the Google+ +1 button should be visible on the bottom of the program page
    Given I am browsing with my pocketcode app
    And I am on "/pocketcode/program/1"
    And I wait for a second
    Then I should see "program 1"
    And I should see the Facebook Like button on the bottom of the program page


  Scenario: In a non-mobile browser the Facebook, Google+, Twitter and E-Mail share buttons should be visible
    Given I am on "/pocketcode/program/1"
    Then I should see "program 1"
    And I should see the Facebook Share button
    And I should see the Google Plus share button
    And I should see the Twitter share button
    And I should see the Mail share button

  Scenario: In a mobile browser the Facebook, Google+, Twitter and WhatsApp share buttons should be visible
    Given I am browsing with my pocketcode app
    And I am on "/pocketcode/program/1"
    Then I should see "program 1"
    And I should see the Facebook Share button
    And I should see the Google Plus share button
    And I should see the Twitter share button
    And I should see the WhatsApp share button