@api
Feature: Search tagged programs

    To find programs, users should be able to search all available programs for specific words and tags

#  Background:
#    Given there are users:
#      | name     | password | token      |
#      | Catrobat | 12345    | cccccccccc |
#      | User1    | vwxyz    | aaaaaaaaaa |
#      | NewUser  | 54321    | bbbbbbbbbb |
#    And there are programs:
#      | id | name             | description | owned by | downloads | views | upload time      | version |
#      | 1  | Galaxy War       | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
#      | 2  | Minions          |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
#      | 3  | Fisch            |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
#      | 4  | Ponny            | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
#      | 5  | MarkoTheBest     |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
#      | 6  | Whack the Marko  | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
#      | 7  | Superponny       | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
#      | 8  | Universe         |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
#      | 9  | Webteam          |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
#      | 10 | Fritz the Cat    |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |
#    And the current time is "01.08.2014 13:00"

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are tags:
      | id | en        | de          |
      | 1  | Games     | Spiele      |
      | 2  | Story     | Geschichte  |
      | 3  | Single    | Allein      |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | RemixOf  | tags_id |
      | 1  | Minions   | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | null     | 1,2     |
      | 2  | Galaxy    | p2          | User1    | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | null     | 2       |
      | 3  | Alone     | p3          | User1    | 5         | 1     | 01.03.2013 12:00 | 0.8.5   | null     | 3       |


  Scenario: Search more programs with the same tag over the normal search

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "Story"
    Then I should get following programs:
        | Name      |
        | Galaxy    |
        | Minions   |


  Scenario: Search a program with the tag over the normal search

    When I search for "Single"
    Then I should get following programs:
        | Name      |
        | Alone     |


  Scenario: A request must have specific parameters to succeed with the tag search

    Given I have a parameter "q" with the tag id "1"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/search/tagPrograms.json" with these parameters
    Then I should get following programs:
        | Name      |
        | Minions   |
