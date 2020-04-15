@web @search
Feature: Searching for programs

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |
    And there are tags:
      | id | en        | de         |
      | 1  | Game      | Spiel      |
      | 2  | Animation | Animation  |
      | 3  | Story     | Geschichte |
    And there are projects:
      | id | name                | description | owned by | tags_id|extensions | upload time      | version |
      | 1  | program 1           | p1          | User1    |  1     |  arduino  | 22.04.2014 12:00 | 0.8.5   |
      | 2  | test program        |             | User1    |  2     |  arduino  | 22.04.2014 13:00 | 0.8.5   |
      | 3  | Test advanced app   |             | Catrobat |  3     |  drone    | 22.04.2014 14:00 | 0.8.5   |
      | 4  | Catrobat            | my program  | User1    |  3     |  lego     | 22.04.2014 14:00 | 0.8.5   |
      | 5  | project 3           |     lego    | Catrobat |  1,2   |  phiro    | 22.04.2014 14:00 | 0.8.5   |
      | 6  | test advanced games |             | User1    |  2,3   |  lego     | 22.04.2014 14:00 | 0.8.5   |
      | 7  | test                |             | Catrobat |  3,2   |  lego     | 22.04.2014 14:00 | 0.8.5   |
      | 8  | project test        |   catrobat  | User1    |   1    |  drone    | 22.04.2014 14:00 | 0.8.5   |


  Scenario: search for programs, which contain the word "program"
    Given I am on "/app/search/program"
    And I wait for the page to be loaded
    Then I should see "Your search returned 3 results"
    Then I should see "program 1"
    Then I should see "test program"
    Then I should see "Catrobat"

  Scenario: search for programs, which contain the word "Test"
    Given I am on "/app/search/Test"
    And I wait for the page to be loaded
    Then I should see "Your search returned 5 results"
    Then I should see "test advanced games"
    Then I should see "test advanced app"
    Then I should see "test program"
    Then I should see "test"
    Then I should see "project test"

  Scenario: search for programs, which contain the word "test advanced"
    Given I am on "/app/search/Test%20advanced"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    Then I should see "test advanced games"
    Then I should see "test advanced app"

  Scenario: search for projects, which contain the word "game"
    Given I am on "/app/search/game"
    And I wait for the page to be loaded
    Then I should see "Your search returned 4 results"
    Then I should see "test advanced games"
    Then I should see "program 1"
    Then I should see "project 3"
    Then I should see "project test"

  Scenario: search for projects, which contain the word "program and app"
    Given I am on "/app/search/program%20and%20app"
    And I wait for the page to be loaded
    Then I should see "Your search returned 0 results"

  Scenario: search for projects, which contain the word "catrobat"
    Given I am on "/app/search/catrobat"
    And I wait for the page to be loaded
    Then I should see "Your search returned 5 results"
    Then I should see "Test advanced app"
    Then I should see "Catrobat"
    Then I should see "project 3"
    Then I should see "test"
    Then I should see "project test"

  Scenario: search for projects, which contain the word "lego"
    Given I am on "/app/search/lego"
    And I wait for the page to be loaded
    Then I should see "Your search returned 4 results"
    Then I should see "Test advanced games"
    Then I should see "Catrobat"
    Then I should see "project 3"
    Then I should see "test"

  Scenario: pressing the search icon should spawn the search bar
    Given I am on "/app"
    And I wait for the page to be loaded
    Then at least one ".search-icon-header" element should be visible
    Then no ".search-input-header" element should be visible
    Then no "#btn-search-header" element should be visible
    And I click the currently visible search icon
    Then no ".search-icon-header" element should be visible
    And at least one ".search-input-header" element should be visible
    And at least one "#btn-search-header" element should be visible

  Scenario: consecutive searches should lead to different results
    Given I am on "/app"
    And I wait for the page to be loaded
    Then I click ".search-icon-header"
    And I enter "program" into visible ".input-search"
    And I click "#btn-search-header"
    And I wait for the page to be loaded
    Then I should be on "/app/search/program"
    And I should see "Your search returned 3 results"
    And at least one ".search-input-header" element should be visible
    And at least one "#btn-search-header" element should be visible
    Then I enter "Test advanced" into visible ".input-search"
    And I click "#btn-search-header"
    And I wait for the page to be loaded
    Then I should be on "/app/search/Test%20advanced"
    And I should see "Your search returned 2 results"
    And at least one ".search-input-header" element should be visible
    And at least one "#btn-search-header" element should be visible

  Scenario: search for gmail should find no program
    Given I am on "/app/search/gmail"
    And I wait for the page to be loaded
    Then I should see "Your search returned 0 results"

  Scenario: search for gmx should find no program
    Given I am on "/app/search/gmx.at"
    And I wait for the page to be loaded
    Then I should see "Your search returned 0 results"