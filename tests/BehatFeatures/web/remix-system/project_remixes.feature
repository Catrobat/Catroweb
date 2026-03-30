@homepage @remixgraph
Feature: As a visitor I want to see the full remix graph inline on the program page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Superman |
      | 2  | Gangster |
    And there are projects:
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

    #---------------------------------------------------------------------------------------------------------------
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
    #---------------------------------------------------------------------------------------------------------------
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

  Scenario: Project page shows the remix graph toggle button
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#remix-graph-inline" should exist
    And the element "#remix-graph-toggle" should be visible

  Scenario: Remix graph panel is hidden by default
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#remix-graph-panel" should not be visible

  Scenario: Clicking the toggle button loads the remix graph inline
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#remix-graph-toggle"
    And I wait for AJAX to finish
    And I wait 2500 milliseconds
    Then the element "#remix-graph-panel" should be visible
    And the element "#remix-graph-network" should exist
    And the element "#remix-graph-remix-count" should contain "6"
    And the element "#remix-graph-project-count" should contain "7"

  Scenario: Clicking the toggle again hides the remix graph panel
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#remix-graph-toggle"
    And I wait for AJAX to finish
    And I wait 1500 milliseconds
    Then the element "#remix-graph-panel" should be visible
    When I click "#remix-graph-toggle"
    Then the element "#remix-graph-panel" should not be visible

  Scenario: Viewing remix graph of project 8 inline
    Given I am on "/app/project/8"
    And I wait for the page to be loaded
    When I click "#remix-graph-toggle"
    And I wait for AJAX to finish
    And I wait 2500 milliseconds
    Then the element "#remix-graph-remix-count" should contain "1"
    And I should see a node with id "catrobat_8" having name "project 8" and username "Gangster"
    And I should see a node with id "catrobat_9" having name "project 9" and username "Superman"
    And I should see an edge from "catrobat_8" to "catrobat_9"

  Scenario: Viewing details of project 2 using debug app
    Given I use a debug build of the Catroid app
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    When I click "#remix-graph-toggle"
    And I wait for AJAX to finish
    And I wait 2500 milliseconds
    Then I should see a node with id "catrobat_7" having name "project 7" and username "Superman"
    And I should see an edge from "catrobat_5" to "catrobat_7"

  Scenario: Viewing remix graph using release app
    Given I use a release build of the Catroid app
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    When I click "#remix-graph-toggle"
    And I wait for AJAX to finish
    And I wait 2500 milliseconds
    Then I should see an unavailable node with id "catrobat_7"
    And I should see an edge from "catrobat_5" to "catrobat_7"
