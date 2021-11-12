@web @project_page @recommendations @disabled
Feature: Showing similar programs on details page of one program

  Background:
    Given there are users:
      | id | name  |
      | 1  | User1 |
      | 2  | User2 |
      | 3  | User3 |
    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    And there are tags:
      | internal_title | title_ltm_code  |
      | game           | __Spiel         |
      | animation      | __Animation     |
      | story          | __Geschichte    |
      | music          | __Musik         |
      | art            | __Kunst         |
      | experimental   | __Experimentell |
    And there are projects:
      | id | name    | description | owned by | downloads | apk_downloads | views | upload time      | version | extensions       | tags                       |
      | 1  | Minions | p1          | User1    | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | mindstorms,phiro | game,animation,story,music |
      | 2  | Galaxy  | p2          | User2    | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | mindstorms,drone | game,animation,story       |
      | 3  | Alone   | p3          | User2    | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |                  | game,animation             |
      | 4  | Trolol  | p5          | User2    | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   | mindstorms       | art                        |
      | 5  | Nothing | p6          | User3    | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |                  | experimental               |

  Scenario: Showing similar programs
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#recommended-projects__similar" should be visible
    And I should see 3 "#recommended-projects__similar .project-list__project"
    And I should see "Minions"
    And I should see "Similar Projects"
    And I should see "Galaxy"
    And I should see "Alone"
    And I should see "Trolol"
    But I should not see "Nothing"

  Scenario: No similar programs are given
    When I go to "/app/project/5"
    And I wait for the page to be loaded
    Then I should not see "#recommended-projects__similar"
    And I should see "Nothing"
    And I should not see "Similar Programs"
    And I should not see "Trolol"
    And I should not see "Minions"
    And I should not see "Galaxy"
    And I should not see "Alone"
