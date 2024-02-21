@api @disabled
Feature: Computes user similarities by analyzing common likes and remixes between users
  (user-based Collaborative Filtering using Jaccard distance as similarity measure)

  The results of the computed similarities are later used by the recommendation algorithm.
  See tests: get_recommended_projects_homepage.feature

  Scenario: We don't have the import_*.sql files anymore
    Then We can't test anything here

#  Background:
#    Given there are users:
#      | id | name      | password | token      |
#      | 1  | Catrobat1 | 12345    | cccccccccc |
#      | 2  | Catrobat2 | 12345    | cccccccccc |
#      | 3  | Catrobat3 | 12345    | cccccccccc |
#      | 4  | Catrobat4 | 12345    | cccccccccc |
#    And there are projects:
#      | id | name      | description | owned by  | downloads | views | upload time      | version | remix_root |
#      | 1  | Game      | p4          | Catrobat4 | 5         | 1     | 01.03.2013 12:00 | 0.8.5   | true       |
#      | 2  | Minions   | p1          | Catrobat1 | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | false      |
#      | 3  | Galaxy    | p2          | Catrobat2 | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | false      |
#      | 4  | Other     | p3          | Catrobat3 | 12        | 9     | 01.02.2013 12:00 | 0.8.5   | true       |
#      | 5  | Other2    | p5          | Catrobat2 | 3         | 9     | 01.02.2013 12:00 | 0.8.5   | false      |
#      | 6  | Other3    | p6          | Catrobat1 | 1         | 1     | 01.02.2013 12:00 | 0.8.5   | true       |
#      | 7  | Other4    | p7          | Catrobat4 | 2         | 10    | 01.02.2013 12:00 | 0.8.5   | true       |
#      | 8  | Other5    | p7          | Catrobat3 | 1         | 2     | 01.02.2013 12:00 | 0.8.5   | true       |
#      | 9  | Other6    | p7          | Catrobat2 | 2         | 1     | 01.02.2013 12:00 | 0.8.5   | true       |
#
#  Scenario: Example #1, only one like-similarity
#    Given there are likes:
#      | username  | project_id | type | created at       |
#      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
#      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
#      | Catrobat2 | 2          | 3    | 01.01.2017 12:00 |
#      | Catrobat2 | 3          | 3    | 01.01.2017 12:00 |
#      | Catrobat3 | 4          | 3    | 01.01.2017 12:00 |
#    When I compute all like similarities between users
#    Then I should get following like similarities:
#      | first_user_id | second_user_id | similarity |
#      | 1             | 2              | 0.333      |
#
#  Scenario: Example #2, like-similarities between multiple users
#    Given there are likes:
#      | username  | project_id | type | created at       |
#      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
#      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
#      | Catrobat2 | 2          | 3    | 01.01.2017 12:00 |
#      | Catrobat2 | 3          | 2    | 01.01.2017 12:00 |
#      | Catrobat2 | 4          | 4    | 01.01.2017 12:00 |
#      | Catrobat3 | 2          | 2    | 01.01.2017 12:00 |
#      | Catrobat3 | 3          | 1    | 01.01.2017 12:00 |
#      | Catrobat3 | 6          | 1    | 01.01.2017 12:00 |
#      | Catrobat4 | 6          | 1    | 01.01.2017 12:00 |
#      | Catrobat4 | 1          | 1    | 01.01.2017 12:00 |
#    When I compute all like similarities between users
#    Then I should get following like similarities:
#      | first_user_id | second_user_id | similarity |
#      | 1             | 2              | 0.25       |
#      | 1             | 4              | 0.5        |
#      | 2             | 3              | 0.4        |
#      | 2             | 4              | 0.2        |
#      | 3             | 4              | 0.25       |
#
#  Scenario: Example #1, only one remix-similarity
#    Given there are forward remix relations:
#      | ancestor_id | descendant_id | depth |
#      | 1           | 1             | 0     |
#      | 2           | 2             | 0     |
#      | 3           | 3             | 0     |
#      | 7           | 7             | 0     |
#      | 8           | 8             | 0     |
#      | 9           | 9             | 0     |
#      | 1           | 2             | 1     |
#      | 1           | 3             | 1     |
#      | 7           | 3             | 1     |
#      | 8           | 9             | 1     |
#    When I compute all remix similarities between users
#    Then I should get following remix similarities:
#      | first_user_id | second_user_id | similarity |
#      | 1             | 2              | 0.333      |
#
#  Scenario: Example #2, remix-similarities between multiple users
#    Given there are forward remix relations:
#      | ancestor_id | descendant_id | depth |
#      | 1           | 1             | 0     |
#      | 2           | 2             | 0     |
#      | 3           | 3             | 0     |
#      | 7           | 7             | 0     |
#      | 8           | 8             | 0     |
#      | 9           | 9             | 0     |
#      | 1           | 2             | 1     |
#      | 1           | 3             | 1     |
#      | 2           | 3             | 1     |
#      | 7           | 3             | 1     |
#      | 4           | 3             | 1     |
#      | 2           | 8             | 1     |
#      | 7           | 8             | 1     |
#      | 6           | 8             | 1     |
#      | 6           | 7             | 1     |
#      | 1           | 7             | 1     |
#    When I compute all remix similarities between users
#    Then I should get following remix similarities:
#      | first_user_id | second_user_id | similarity |
#      | 1             | 2              | 0.25       |
#      | 1             | 4              | 0.5        |
#      | 2             | 3              | 0.4        |
#      | 2             | 4              | 0.2        |
#      | 3             | 4              | 0.25       |
