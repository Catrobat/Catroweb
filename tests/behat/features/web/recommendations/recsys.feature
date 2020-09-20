# Missing in new API - To be fixed with ticket: SHARE-370
# Still the feature is very broken and needs a complete rework: SHARE-111

@homepage @recommendations
Feature: Recommendations on homepage (a.k.a. index page)

  Background:
    Given there are users:
      | name      | password | token      | email               | id |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org | 1  |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org | 2  |
    Given there are projects:
      | id | name         | description | owned by  | downloads | apk_downloads | views | upload time      | version |
      | 1  | Minions      | p1          | Catrobat  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Galaxy       | p2          | OtherUser | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   |
      | 3  | Alone        | p3          | Catrobat  | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |
      | 4  | Adventure    | p4          | OtherUser | 7         | 58            | 22    | 01.03.2013 12:00 | 0.8.5   |
      | 5  | TestGame     | p5          | Catrobat  | 22        | 60            | 24    | 01.03.2013 12:00 | 0.8.5   |
      | 6  | Awesome Game | p6          | OtherUser | 7         | 26            | 12    | 01.03.2013 12:00 | 0.8.5   |



  Scenario: Recommended programs on homepage (a.k.a. index page)
    Given there are project reactions:
      | user      | project | type | created at       |
      | Catrobat  | 1       | 1    | 01.01.2017 12:00 |
      | Catrobat  | 2       | 2    | 01.01.2017 12:00 |
      | OtherUser | 2       | 2    | 01.01.2017 12:00 |
      | OtherUser | 1       | 4    | 01.01.2017 12:00 |
      | OtherUser | 5       | 3    | 01.01.2017 12:00 |
      | Catrobat  | 5       | 3    | 01.01.2017 12:00 |
      | Catrobat  | 6       | 2    | 01.01.2017 12:00 |
      | OtherUser | 6       | 2    | 01.01.2017 12:00 |
    When I am on "/app/"
    And I wait for the page to be loaded
    And I should see "Recommended projects"
    And the element "#home-projects__recommended" should be visible
    Then Project with the id "1" should be visible in the "recommended" category
    Then Project with the id "2" should be visible in the "recommended" category
    Then Project with the id "5" should be visible in the "recommended" category
    Then Project with the id "6" should be visible in the "recommended" category
    Then Project with the id "3" should not be visible in the "recommended" category
    Then Project with the id "4" should not be visible in the "recommended" category

  Scenario: No recommended programs on homepage (a.k.a. index page)
    Given the selected language is "English"
    When I am on "/app/"
    And I wait for the page to be loaded
    And I should not see "Recommended programs"
    And the element "#home-projects__recommended" should not be visible
    Then I should not see "Recommended Projects"
    Then Project with the id "1" should not be visible in the "recommended" category
    Then Project with the id "2" should not be visible in the "recommended" category
    Then Project with the id "5" should not be visible in the "recommended" category
    Then Project with the id "6" should not be visible in the "recommended" category
    Then Project with the id "3" should not be visible in the "recommended" category
    Then Project with the id "4" should not be visible in the "recommended" category
