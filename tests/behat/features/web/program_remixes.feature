@homepage @remixgraph
Feature: As a visitor I want to see the full remix graph of a program on the program page

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | Gangster | 123456   | cccccccccc | dev2@pocketcode.org |  2 |
    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | remix_root | debug |
      | 1  | project 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true       | false |
      | 2  | project 2 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | false |
      | 3  | project 3 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | false |
      | 4  | project 4 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | false |
      | 5  | project 5 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | false |
      | 6  | project 6 | abcef                   | Superman | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | false |
      | 7  | project 7 | abcef                   | Superman | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | true  |
      | 8  | project 8 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true       | false |
      | 9  | project 9 | abcef                   | Superman | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | false      | false |

    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like according to the following forward remix relations (closure table):
    #              (1)
    #               \
    #               (2)_____
    #               / \     \
    #             (3) (4)   |       (8)
    #              | \ |    |        |
    #             (5) (6)__/        (9)
    #               \ /
    #               (7)
    #-------------------------------------------------------------------------------------------------------------------
    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 1           | 3             | 2     |
      | 1           | 4             | 2     |
      | 1           | 5             | 3     |
      | 1           | 6             | 2     |
      | 1           | 6             | 3     |
      | 1           | 7             | 3     |
      | 1           | 7             | 4     |
      | 2           | 2             | 0     |
      | 2           | 3             | 1     |
      | 2           | 4             | 1     |
      | 2           | 5             | 2     |
      | 2           | 6             | 1     |
      | 2           | 6             | 2     |
      | 2           | 7             | 2     |
      | 2           | 7             | 3     |
      | 3           | 3             | 0     |
      | 3           | 5             | 1     |
      | 3           | 6             | 1     |
      | 3           | 7             | 2     |
      | 4           | 4             | 0     |
      | 4           | 6             | 1     |
      | 4           | 7             | 2     |
      | 5           | 5             | 0     |
      | 5           | 7             | 1     |
      | 6           | 6             | 0     |
      | 6           | 7             | 1     |
      | 7           | 7             | 0     |
      | 8           | 8             | 0     |
      | 8           | 9             | 1     |
      | 9           | 9             | 0     |

  Scenario: Viewing details of project 8 and number of remixes
    Given I am on "/app/project/8?show_graph=1"
    Then I should see "project 8"
    And I should see "Gangster"
    And I should see "abcef"
    And I should see "Report project"
    And I should see "more than one year ago"
    And I should see "0.00 MB"
    And I should see "336 downloads"
    And I should see "10 views"
    And I should see "1 remix"

  Scenario: Viewing details of project 9 and number of remixes
    Given I am on "/app/project/9?show_graph=1"
    Then I should see "project 9"
    And I should see "Superman"
    And I should see "abcef"
    And I should see "Report project"
    And I should see "more than one year ago"
    And I should see "0.00 MB"
    And I should see "336 downloads"
    And I should see "10 views"
    And I should see "1 remix"

  Scenario: Viewing remix graph of project 8
    Given I am on "/app/project/8?show_graph=1"
    Then I ensure pop ups work
    When I click "#remix-graph-modal-link"
    When I wait 10 milliseconds
    And I should see a node with id "catrobat_8" having name "project 8" and username "Gangster"
    And I should see a node with id "catrobat_9" having name "project 9" and username "Superman"
    And I should see an edge from "catrobat_8" to "catrobat_9"

  Scenario: Viewing remix graph of project 9
    Given I am on "/app/project/9?show_graph=1"
    Then I ensure pop ups work
    When I click "#remix-graph-modal-link"
    When I wait 10 milliseconds
    And I should see a node with id "catrobat_8" having name "project 8" and username "Gangster"
    And I should see a node with id "catrobat_9" having name "project 9" and username "Superman"
    And I should see an edge from "catrobat_8" to "catrobat_9"

  Scenario: Viewing details of project 1 and number of remixes
    Given I am on "/app/project/1?show_graph=1"
    Then I should see "project 1"
    And I should see "Superman"
    And I should see "6 remixes"

  Scenario: Viewing details of project 2 and number of remixes
    Given I am on "/app/project/2?show_graph=1"
    Then I should see "project 2"
    And I should see "Gangster"
    And I should see "6 remixes"

  Scenario: Viewing details of project 2 using debug app
    Given I use a debug build of the Catroid app
    And I am on "/app/project/2?show_graph=1"
    When I click "#remix-graph-modal-link"
    And I wait 10 milliseconds
    Then I should see a node with id "catrobat_7" having name "project 7" and username "Superman"
    And I should see an edge from "catrobat_5" to "catrobat_7"

  Scenario: Viewing details of project 2 using release app
    Given I use a release build of the Catroid app
    And I am on "/app/project/2?show_graph=1"
    When I click "#remix-graph-modal-link"
    And I wait 10 milliseconds
    Then I should see an unavailable node with id "catrobat_7"
    And I should see an edge from "catrobat_5" to "catrobat_7"
