@homepage
Feature: Searching for programs with tags

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc  | dev2@pocketcode.org |
    And there are tags:
      | id | en        | de          |
      | 1  | Game      | Spiel       |
      | 2  | Animation | Animation   |
      | 3  | Story     | Geschichte  |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | tags_id  |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 1,2      |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   | 2        |
      | 3  | myprog 3  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | 3        |
    And I am on "/pocketcode"

  Scenario: Searching other programs wie the same tag
    Given I am on "/pocketcode/program/1"
    And I should see "program 1"
    And I should see "Game"
    And I should see "Animation"
    When I press on the tag "Animation"
    Then I should see "Your search returned 2 results"
    And I should see "program 1"
    And I should see "program 2"
    And I should not see "myprog 3"

  Scenario: search for programs should work
    When I search for "Animation" with the searchbar
    Then I should see "Your search returned 2 results"
    And the "search-input-header" field should contain "Animation"
    And the "search-input-footer" field should contain "Animation"
    And the "searchbar" field should contain "Animation"
    And I should see "program 1"
    And I should see "program 2"
    And I should not see "myprog 3"
