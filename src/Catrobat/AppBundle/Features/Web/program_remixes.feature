@homepage @remixgraph
Feature: As a visitor I want to see the full remix graph of a program on the program page

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |
      | Gangster | 123456   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | remix_root |
      | 1  | program 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             |  true   | true       |
      | 2  | program 2 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |
      | 3  | program 3 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |
      | 4  | program 4 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |
      | 5  | program 5 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |
      | 6  | program 6 | abcef                   | Superman | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |
      | 7  | program 7 | abcef                   | Superman | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |
      | 8  | program 8 | abcef                   | Gangster | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | true       |
      | 9  | program 9 | abcef                   | Superman | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             |  true   | false      |

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

    Scenario: Viewing details of program 8 and number of remixes
      Given I am on "/pocketcode/program/8?show_graph=1"
      Then I should see "program 8"
      And I should see "Gangster"
      And I should see "abcef"
      And I should see "Report program"
      And I should see "more than one year ago"
      And I should see "0.00 MB"
      And I should see "336 downloads"
      And I should see "10 views"
      And I should see "1 remix"

    Scenario: Viewing details of program 9 and number of remixes
      Given I am on "/pocketcode/program/9?show_graph=1"
      Then I should see "program 9"
      And I should see "Superman"
      And I should see "abcef"
      And I should see "Report program"
      And I should see "more than one year ago"
      And I should see "0.00 MB"
      And I should see "336 downloads"
      And I should see "10 views"
      And I should see "1 remix"

    Scenario: Viewing remix graph of program 8
      Given I am on "/pocketcode/program/8?show_graph=1"
      Then I ensure pop ups work
      When I click "#remix-graph-modal-link"
      When I wait 1200 milliseconds
      And I should see a node with id "catrobat_8" having name "program 8" and username "Gangster"
      And I should see a node with id "catrobat_9" having name "program 9" and username "Superman"
      And I should see an edge from "catrobat_8" to "catrobat_9"

    Scenario: Viewing remix graph of program 9
      Given I am on "/pocketcode/program/9?show_graph=1"
      Then I ensure pop ups work
      When I click "#remix-graph-modal-link"
      When I wait 1200 milliseconds
      And I should see a node with id "catrobat_8" having name "program 8" and username "Gangster"
      And I should see a node with id "catrobat_9" having name "program 9" and username "Superman"
      And I should see an edge from "catrobat_8" to "catrobat_9"

    Scenario: Viewing details of program 1 and number of remixes
      Given I am on "/pocketcode/program/1?show_graph=1"
      Then I should see "program 1"
      And I should see "Superman"
      And I should see "6 remixes"

    Scenario: Viewing details of program 2 and number of remixes
      Given I am on "/pocketcode/program/2?show_graph=1"
      Then I should see "program 2"
      And I should see "Gangster"
      And I should see "6 remixes"
