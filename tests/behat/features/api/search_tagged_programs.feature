@api
Feature: Search tagged programs

  To find programs, users should be able to search all available programs for specific words and tags

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc |  1 |
      | User1    | vwxyz    | aaaaaaaaaa |  2 |
      | Bob      | vewqw    | eeeeeeeeee |  3 |
    And there are tags:
      | id | en     | de         |
      | 1  | Games  | Spiele     |
      | 2  | Story  | Geschichte |
      | 3  | Single | Allein     |
      | 4  | Bob    | Bill       |
      | 5  | Racing | Rennen     |
    And there are programs:
      | id | name            | description | owned by | downloads | views | upload time      | version | tags_id |
      | 1  | Minions         | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | 1,2     |
      | 2  | Galaxy          | p2          | User1    | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | 2       |
      | 3  | Alone           | p3          | User1    | 5         | 1     | 01.03.2013 12:00 | 0.8.5   | 3       |
      | 4  | Ponny           | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   | 5       |
      | 5  | MarkoTheBest    |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   | 5       |
      | 6  | Whack the Marko | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   | 5       |
      | 7  | Superponny      | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 5       |
      | 8  | Universe        |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   | 5       |
      | 9  | Webteam         |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   | 4       |
      | 10 | Fritz the Cat   |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   | 5       |
      | 11 | Bobs Game       | dec1        | Fritz    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 5       |
      | 12 | Undertale       | dec1 dec3   | Frank    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 5       |
      | 13 | Pocketmaster    | dec1 dec4   | User2    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 5       |
      | 14 | Clickendemon    | dec2 dec5   | Emi      | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 5       |
      | 15 | tap bird        | dec2 dec6   | Bob      | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 5       |
      | 16 | Wather          | dec7        | Judi     | 4         | 33    | 01.01.2012 12:00 | 0.8.5   | 4       |


  Scenario: A request must have specific parameters to succeed with the tag search

    Given I have a parameter "q" with the tag id "1"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search/tagPrograms.json" with these parameters
    Then I should get following programs:
      | Name    |
      | Minions |


  Scenario: Search more programs with the same tag over the normal search

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "Story"
    Then I should get following programs:
      | Name    |
      | Galaxy  |
      | Minions |


  Scenario: Search a program with the tag over the normal search

    When I search for "Single"
    Then I should get following programs:
      | Name  |
      | Alone |

  Scenario: Search for programs assuming search will respect every term individually
    Given I use the limit "10"
    And I use the offset "0"
    When I search for "Bob Game"
    Then I should get following programs:
      | Name      |
      | Minions   |
      | Webteam   |
      | Bobs Game |
      | tap bird  |
      | Wather    |

  Scenario: Search for programs with many terms
    Given I use the limit "30"
    And I use the offset "0"
    When I search for "Galaxy Ponny Webteam dec1 Single"
    Then I should get following programs:
      | Name         |
      | Alone        |
      | Galaxy       |
      | Ponny        |
      | Webteam      |
      | Superponny   |
      | Bobs Game    |
      | Undertale    |
      | Pocketmaster |
