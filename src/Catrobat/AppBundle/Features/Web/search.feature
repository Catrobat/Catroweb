@homepage
Feature: Searching for programs

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc  | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | myprog 3  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | myprog 4  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
    And I am on "/pocketcode"

  Scenario: search for programs should work
    Given I am on "/pocketcode/search/prog"
    Then I should see "Your search returned 4 results"

  Scenario: search for yahoo myprog should search for all
    Given I fill in "search-input-header" with "yahoo myprog"
    And I click "#search-header"
    Then I should see "Your search returned 2 results"

  Scenario: search for gmail should search for all
    Given I fill in "search-input-header" with "gmail"
    And I click "#search-header"
    Then I should see "Your search returned 4 results"

  Scenario: search for gmx should search for all
    Given I fill in "search-input-header" with "gmx.at"
    And I click "#search-header"
    Then I should see "Your search returned 0 results"