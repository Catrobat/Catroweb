@web @search
Feature: Searching for projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Cat      |
      | 2  | User1    |
      | 3  | User2    |
      | 4  | User3    |
      | 5  | testUser |

    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    And there are tags:
      | internal_title | title_ltm_code |
      | game           | __Spiel        |
      | animation      | __Animation    |
      | story          | __Geschichte   |
    And there are projects:
      | id | name                | description | owned by | tags             | extensions | upload time      | version |
      | 1  | project 1           | p1          | User1    | game             | arduino    | 22.04.2014 12:00 | 0.8.5   |
      | 2  | test project        |             | User2    | animation        | arduino    | 22.04.2014 13:00 | 0.8.5   |
      | 3  | Test advanced app   |             | Cat      | story            | drone      | 22.04.2014 14:00 | 0.8.5   |
      | 4  | Catrobat            | my project  | User1    | story            | mindstorms | 22.04.2014 14:00 | 0.8.5   |
      | 5  | notAProject 3       | mindstorms  | Cat      | game, animation  | phiro      | 22.04.2014 14:00 | 0.8.5   |
      | 6  | test advanced games |             | User3    | animation, story | mindstorms | 22.04.2014 14:00 | 0.8.5   |
      | 7  | test                |             | Cat      | story, animation | mindstorms | 22.04.2014 14:00 | 0.8.5   |
      | 8  | notAProject test    | catrobat    | User1    | game             | drone      | 22.04.2014 14:00 | 0.8.5   |
    And I wait 1000 milliseconds


  Scenario: search for projects, which contain the word "project"
    Given I am on "/app/search/project"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "project 1"
    Then I should see "test project"
    Then I should see "Catrobat"
    Then I should not see "Test advanced app"
    Then I should not see "notAProject 3"
    Then I should not see "notAProject test"

  Scenario: search for projects, which contain the word "Test"
    Given I am on "/app/search/Test"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "test advanced games"
    Then I should see "test advanced app"
    Then I should see "test project"
    Then I should see "test"
    Then I should see "notAProject test"
    Then I should see "testUser"

  Scenario: search for projects, which contain the word "test advanced"
    Given I am on "/app/search/Test%20advanced"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "test advanced games"
    Then I should see "test advanced app"

  Scenario: search for projects, which contain the word "game"
    Given I am on "/app/search/game"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "test advanced games"
    Then I should see "project 1"
    Then I should see "notAProject 3"
    Then I should see "notAProject test"

  Scenario: search for projects, which contain the word "project and app"
    Given I am on "/app/search/project%20and%20app"
    And I wait for the page to be loaded
    Then I should see "Search results"

  Scenario: search for projects, which contain the word "mindstorms"
    Given I am on "/app/search/mindstorms"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "Test advanced games"
    Then I should see "Catrobat"
    Then I should see "notAProject 3"
    Then I should see "test"

  Scenario: search for gmail should find no project
    Given I am on "/app/search/gmail"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "No projects found"
    Then I should see "No users found"

  Scenario: search for gmx should find no project
    Given I am on "/app/search/gmx.at"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "No projects found"
    Then I should see "No users found"


  Scenario: search for projects, which contain the word "User"
    Given I am on "/app/search/user"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "User1"
    Then I should see "User2"
    Then I should see "User3"
    Then I should see "project 1"
    Then I should see "test project"
    Then I should see "Catrobat"
    Then I should see "test advanced games"
    Then I should see "project test"


  Scenario: search for projects, which contain the word "Cat"
    Given I am on "/app/search/cat"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "Cat"
    Then I should not see "User1"
    Then I should not see "User2"
    Then I should not see "User3"
    Then I should see "Catrobat"
    Then I should see "Test advanced app"
    Then I should see "notAProject 3"
    Then I should see "test"
    Then I should not see "project 1"
    Then I should not see "test project"
    Then I should not see "test advanced games"
    Then I should see "notAProject test"


  Scenario: search for projects, which contain the word "Story"
    Given I am on "/app/search/story"
    And I wait for the page to be loaded
    Then I should see "Test advanced app"
    Then I should see "Catrobat"
    Then I should see "test"

  Scenario: search for projects with string "Test advanced app"
    Given I am on "/app/search/Test%20advanced%20app"
    And I wait for the page to be loaded
    Then I should see "Test advanced app"
    Then I should see "No users found"






