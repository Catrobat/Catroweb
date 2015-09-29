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
    Given I fill in "search-input-header" with "prog"
    And I click "#search-header"
    Then I should see "Your search returned 4 results"
    And the "search-input-header" field should contain "prog"
    And the "search-input-footer" field should contain "prog"
    And the "searchbar" field should contain "prog"


  Scenario: footer search should work too
    Given I fill in "search-input-footer" with "mypro"
    And I click "#search-footer"
    Then I should see "Your search returned 2 results"
    And the "search-input-header" field should contain "mypro"
    And the "search-input-footer" field should contain "mypro"
    And the "searchbar" field should contain "mypro"

  @Mobile
  Scenario: search on mobile view
    Given the element "#search-header-mobile" should be visible
    And the element "#searchbar" should not be visible
    When I click "#search-header-mobile"
    Then the element "#search-header-mobile" should not be visible
    And the element "#searchbar" should be visible
    When I fill in "searchbar" with "prog"
    And I press enter in the search bar
    Then I should see "Your search returned 4 results"