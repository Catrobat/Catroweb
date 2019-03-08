@admin
Feature: Admin Report Notification
  In order to get informed of an upload
  As a subscribed admin
  I want to get an email when a program is uploaded or reported

  Background:
    Given I am a valid user

  Scenario: Email subscribed admins directly after report
    Given there are users:
      | name     | email           |
      | Catrobat | admin@catrob.at |
      | User1    | dog@catrob.at   |
      | User2    | dog2@catrob.at  |
    And there are programs:
      | id | name      |
      | 1  | program 1 |
      | 2  | program 2 |
      | 3  | program 3 |
    And there are notifications:
      | user     | upload | report | summary |
      | Catrobat | 1      | true   | 1       |
      | User1    | 1      | 0      | 0       |
      | User2    | 0      | true   | 0       |
    And I activate the Profiler

    When I report program 1 with category "spam" and note "Bad Program"
    Then I should see 2 outgoing emails
    And I should see a email with recipient "admin@catrob.at"
    And I should see a email with recipient "dog2@catrob.at"
