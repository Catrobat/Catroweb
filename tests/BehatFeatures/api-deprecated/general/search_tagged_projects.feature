@api
Feature: Search tagged projects

  To find projects, users should be able to search all available projects for specific words and tags

  Background:
    Given there are users:
      | name     | id |
      | Catrobat | 1  |
      | User1    | 2  |
      | Bob      | 3  |
      | NewUser  | 4  |
      | Fritz    | 5  |
      | Frank    | 6  |
      | Emi      | 7  |
      | Judi     | 8  |
      | User2    | 9  |
    And there are tags:
      | internal_title | title_ltm_code |
      | games          | __Spiele       |
      | story          | __Geschichte   |
      | single         | __Allein       |
      | bob            | __Bill         |
      | racing         | __Rennen       |
    And there are projects:
      | id | name            | description | owned by | downloads | views | upload time      | version | tags         |
      | 1  | Minions         | p1          | Catrobat | 3         | 12    | 01.01.2014 12:00 | 0.8.5   | games, story |
      | 2  | Galaxy          | p2          | User1    | 10        | 13    | 01.02.2014 12:00 | 0.8.5   | story        |
      | 3  | Alone           | p3          | User1    | 5         | 1     | 01.03.2014 12:00 | 0.8.5   | single       |
      | 4  | Ponny           | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   | racing       |
      | 5  | MarkoTheBest    |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   | racing       |
      | 6  | Whack the Marko | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   | racing       |
      | 7  | Superponny      | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | racing       |
      | 8  | Universe        |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   | racing       |
      | 9  | Webteam         |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   | bob          |
      | 10 | Fritz the Cat   |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   | racing       |
      | 11 | Bobs Game       | dec1        | Fritz    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | racing       |
      | 12 | Undertale       | dec1 dec3   | Frank    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | racing       |
      | 13 | Pocketmaster    | dec1 dec4   | User2    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | racing       |
      | 14 | Clickendemon    | dec2 dec5   | Emi      | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | racing       |
      | 15 | tap bird        | dec2 dec6   | Bob      | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | racing       |
      | 16 | Wather          | dec7        | Judi     | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | bob          |


  Scenario: A request must have specific parameters to succeed with the tag search

    Given I have a parameter "q" with the tag "games"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search/tagProjects.json" with these parameters
    Then I should get following projects:
      | name    |
      | Minions |

  Scenario: Search more projects with the same tag over the normal search

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "Story"
    Then I should get following projects:
      | name    |
      | Galaxy  |
      | Minions |

  Scenario: Search a project with the tag over the normal search

    When I search for "Single"
    Then I should get following projects:
      | name  |
      | Alone |

  Scenario: Search is using And operation. More keywords reduce the result set.
    Given I use the limit "10"
    And I use the offset "0"
    When I search for "Bot Game"
    Then I should get no projects

  Scenario: Search is using And operation. More keywords reduce the result set.
    Given I use the limit "30"
    And I use the offset "0"
    When I search for "Galaxy Ponny Webteam dec1 Single"
    Then I should get no projects
