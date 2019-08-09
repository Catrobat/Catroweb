@homepage
Feature: Searching for programs

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc | dev2@pocketcode.org |
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
    Given I am on "/pocketcode/search/yahoo%20myprog"
    Then I should see "Your search returned 2 results"

  Scenario: search for gmail should search for all
    Given I am on "/pocketcode/search/gmail"
    Then I should see "Your search returned 4 results"

  Scenario: search for gmx should search for all
    Given I am on "/pocketcode/search/gmx.at"
    Then I should see "Your search returned 0 results"

  Scenario: pressing the search icon should spawn the search bar
    Given I am on "/pocketcode"
    Then at least one ".search-icon-header" element should be visible
    Then no ".search-input-header" element should be visible
    Then no "#btn-search-header" element should be visible
    And I click the currently visible search icon
    Then no ".search-icon-header" element should be visible
    Then at least one ".search-input-header" element should be visible
    Then at least one "#btn-search-header" element should be visible

  Scenario: consecutive searches should lead to different results
    Given I am on "/pocketcode"
    And I click the currently visible search icon
    Then I enter "prog" into the currently visible search input
    And I click the currently visible search button
    Then I should be on "/pocketcode/search/prog"
    And I should see "Your search returned 4 results"
    And at least one ".search-input-header" element should be visible
    And at least one "#btn-search-header" element should be visible
    Then I enter "yahoo myprog" into the currently visible search input
    And I click the currently visible search button
    Then I should be on "/pocketcode/search/yahoo%20myprog"
    Then I should see "Your search returned 2 results"
    And at least one ".search-input-header" element should be visible
    And at least one "#btn-search-header" element should be visible
