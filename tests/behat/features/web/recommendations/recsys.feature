# Missing in new API - To be fixed with ticket: SHARE-370
# Still the feature is very broken and needs a complete rework: SHARE-111

@homepage @recommendations @disabled
Feature: Recommendations on homepage (a.k.a. index page)

  Background:
    Given there are users:
      | name      | password | token      | email               | id |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org | 1  |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org | 2  |

  Scenario: Recommended programs on homepage (a.k.a. index page)
    Given there are projects:
      | id | name    | description | owned by  | downloads | apk_downloads | views | upload time      | version |
      | 1  | Minions | p1          | Catrobat  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Galaxy  | p2          | OtherUser | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   |
      | 3  | Alone   | p3          | Catrobat  | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |

    And there are project reactions:
      | user      | project | type | created at       |
      | Catrobat  | 1       | 1    | 01.01.2017 12:00 |
      | Catrobat  | 2       | 2    | 01.01.2017 12:00 |
      | OtherUser | 1       | 4    | 01.01.2017 12:00 |

    When I am on "/app/"
    And I wait for the page to be loaded
    And I should see "Recommended projects"
    And the element "#recommended" should be visible
    Then I should see a recommended homepage program having ID "1" and name "Minions"

  Scenario: No recommended programs on homepage (a.k.a. index page)
    Given there are projects:
      | id | name    | description | owned by  | downloads | apk_downloads | views | upload time      | version |
      | 1  | Minions | p1          | Catrobat  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Galaxy  | p2          | OtherUser | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   |
      | 3  | Alone   | p3          | Catrobat  | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |

    And the selected language is "English"
    When I am on "/app/"
    And I wait for the page to be loaded
    And I should not see "Recommended programs"
    And the element "#recommended" should not be visible
    Then I should not see any recommended homepage programs
