@admin
Feature: Admin Report Notification
  In order to get informed of an upload
  As a subscribed admin
  I want to get an email when a program is uploaded or reported

  Background:
    Given I am a valid user

Scenario: Email subscribed admins directly after report
  Given there are users:
    | name     | password | token      | SuperAdmin | email           |
    | Catrobat | 12345    | cccccccccc | true       | admin@catrob.at |
    | User1    | vwxyz    | aaaaaaaaaa | true      | dog@catrob.at   |
    | User2    | vwxyz    | aaaaaaaaaa | true      | dog2@catrob.at   |
  And there are programs:
    | id | name      | description | owned by | downloads | views | upload time      | version |
    | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
    | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
    | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
  And there are notifications:
    |user     |upload   |report | summary |
    |Catrobat |1        |true   | 1       |
    |User1    |1        |0      | 0       |
    |User2    |0        |true   | 0       |
  And I activate the Profiler

  When I report program 1 with note "Bad Program"
  Then I should see 2 outgoing emails
  And I should see a email with recipient "admin@catrob.at"
  And I should see a email with recipient "dog2@catrob.at"

