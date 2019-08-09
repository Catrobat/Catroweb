@admin
Feature: Admin Upload Notification
  In order to get informed of an upload
  As a subscribed admin
  I want to get an email when a program is uploaded or reported

  Background:
    Given I am a valid user

  Scenario: Email only subscribed admins directly after upload
    Given there are users:
      | name     | email           | id |
      | Catrobat | admin@catrob.at |  1 |
      | User1    | dog@catrob.at   |  2 |
      | User2    | dog2@catrob.at  |  3 |
    And there are notifications:
      | user     | upload | report | summary |
      | Catrobat | true   | 1      | 1       |
      | User1    | true   | 0      | 0       |
      | User2    | 0      | 1      | 0       |
    And I activate the Profiler

    When I upload a program with valid parameters
    Then I should see 2 outgoing emails
    And I should see a email with recipient "admin@catrob.at"
    And I should see a email with recipient "dog@catrob.at"
