@api
Feature: Get recommended programs on homepage

  To find yet unliked programs that were liked by similar users.
  Similar users are users that liked some of the same programs that the current user also liked
  (user-based Collaborative Filtering using Jaccard distance as similarity measure).

  Background:
    Given there are users:
      | id | name      | password | token      |
      | 1  | Catrobat1 | 12345    | cccccccccc |
      | 2  | Catrobat2 | 12345    | cccccccccc |
      | 3  | Catrobat3 | 12345    | cccccccccc |
      | 4  | Catrobat4 | 12345    | cccccccccc |
    And there are programs:
      | id | name    | description | owned by  | downloads | views | upload time      | version | remix_root | debug |
      | 1  | Game    | p4          | Catrobat4 | 5         | 1     | 01.03.2013 12:00 | 0.8.5   | true       | false |
      | 2  | Minions | p1          | Catrobat1 | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | false      | true  |
      | 3  | Galaxy  | p2          | Catrobat2 | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | false      | true  |
      | 4  | Other   | p3          | Catrobat3 | 12        | 9     | 01.02.2013 12:00 | 0.8.5   | true       | false |
      | 5  | Other2  | p5          | Catrobat2 | 3         | 9     | 01.02.2013 12:00 | 0.8.5   | false      | false |
      | 6  | Other3  | p6          | Catrobat1 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | true       | false |
      | 7  | Other4  | p7          | Catrobat4 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | true       | false |
      | 8  | Other5  | p7          | Catrobat3 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | true       | false |
      | 9  | Other6  | p7          | Catrobat2 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | true       | false |

  Scenario Outline: Test if recommendation fallback is active when similar users only like same programs
  (i.e. they don't like any differing programs). Using debug and release app.

    Given I use a <build type> build of the Catroid app
    And there are like similar users:
      | first_user_id | second_user_id | similarity |
      | 1             | 2              | 0.3        |
    And there are likes:
      | username  | program_id | type | created at       |
      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat1 | 2          | 2    | 01.01.2017 12:00 |
      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 2          | 3    | 01.01.2017 12:00 |
    And I have a parameter "test_user_id_for_like_recommendation" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys_general_programs.json" with these parameters
    Then I should get no user-specific recommended projects
    Then I should get a total of <total> projects
    Then I should get the programs "<programs>"

    Examples:
      | build type | total | programs     |
      | debug      | 2     | Minions,Game |
      | release    | 1     | Game         |

  Scenario Outline: No recommendations because there are no liked programs
    Given I use a <build type> build of the Catroid app
    And I have a parameter "test_user_id_for_like_recommendation" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys_general_programs.json" with these parameters
    Then I should get no user-specific recommended projects
    Then I should get a total of 0 projects
    Examples:
      | build type |
      | debug      |
      | release    |

  Scenario: Recommend all other unliked programs, liked by similar user (debug app)
  (example: #1, "Only one similar user, recommend me programs I've not liked so far and only those that are not mine")
    Given I use a debug build of the Catroid app
    And there are like similar users:
      | first_user_id | second_user_id | similarity |
      | 1             | 2              | 0.3        |
    And there are likes:
      | username  | program_id | type | created at       |
      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 2          | 3    | 01.01.2017 12:00 |
      | Catrobat2 | 3          | 2    | 01.01.2017 12:00 |
    Given I have a parameter "test_user_id_for_like_recommendation" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys_general_programs.json" with these parameters
    Then I should get user-specific recommended projects
    Then I should get a total of 1 projects
    Then I should get following programs:
      | Name   |
      | Galaxy |

  Scenario: Recommend all other unliked programs, liked by similar user (release app)
  (example: #1, "Only one similar user, recommend me programs I've not liked so far and only those that are not mine")
    Given I use a release build of the Catroid app
    And there are like similar users:
      | first_user_id | second_user_id | similarity |
      | 1             | 2              | 0.3        |
    And there are likes:
      | username  | program_id | type | created at       |
      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 2          | 3    | 01.01.2017 12:00 |
      | Catrobat2 | 3          | 2    | 01.01.2017 12:00 |
    Given I have a parameter "test_user_id_for_like_recommendation" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys_general_programs.json" with these parameters
    Then I should get a total of 1 projects
    Then I should get following programs:
      | Name   |
      | Game |

  Scenario Outline: Recommend all other unliked programs, liked by similar user
  (example: #2 "Three similar users with different similarity values")
    Given I use a <build type> build of the Catroid app
    And there are like similar users:
      | first_user_id | second_user_id | similarity |
      | 1             | 2              | 0.1        |
      | 1             | 3              | 0.3        |
    And there are likes:
      | username  | program_id | type | created at       |
      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 3          | 3    | 01.01.2017 12:00 |
      | Catrobat3 | 1          | 2    | 01.01.2017 12:00 |
      | Catrobat3 | 3          | 1    | 01.01.2017 12:00 |
      | Catrobat3 | 4          | 4    | 01.01.2017 12:00 |
    Given I have a parameter "test_user_id_for_like_recommendation" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys_general_programs.json" with these parameters
    Then I should get user-specific recommended projects
    Then I should get a total of <total> projects
    Then I should get the programs "<programs>"

    Examples:
      | build type | total | programs     |
      | debug      | 2     | Galaxy,Other |
      | release    | 1     | Other        |

  Scenario Outline: Recommend all other unliked programs, liked by similar user
  (example: #3, "Four similar users with different similarity values")
    Given I use a <build type> build of the Catroid app
    And there are like similar users:
      | first_user_id | second_user_id | similarity |
      | 1             | 2              | 0.1        |
      | 1             | 3              | 0.4        |
      | 1             | 4              | 0.2        |
    And there are likes:
      | username  | program_id | type | created at       |
      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 5          | 3    | 01.01.2017 12:00 |
      | Catrobat2 | 3          | 1    | 01.01.2017 12:00 |
      | Catrobat3 | 1          | 2    | 01.01.2017 12:00 |
      | Catrobat3 | 4          | 4    | 01.01.2017 12:00 |
      | Catrobat4 | 1          | 2    | 01.01.2017 12:00 |
      | Catrobat4 | 5          | 1    | 01.01.2017 12:00 |
    Given I have a parameter "test_user_id_for_like_recommendation" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys_general_programs.json" with these parameters
    Then I should get user-specific recommended projects
    And I should get a total of <total> projects
    And I should get the programs "<programs>"

    Examples:
      | build type | total | programs            |
      | debug      | 3     | Other,Other2,Galaxy |
      | release    | 2     | Other,Other2        |
