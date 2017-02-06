@homepage
Feature: A/B testing for recommendation system & remix graph

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |

    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | remix_root |
      | 1  | Minions   | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | Galaxy    | p2          | Catrobat | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | false      |
      | 3  | Alone     | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   | true       |

    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |

  Scenario: User with selected russian language cannot see the recommendations on homepage (a.k.a. index page)
    Given I am on "/pocketcode/"
    And the selected language is "English"
    And I should see "Recommended programs"
    And the element "#recommended" should be visible
    When I switch the language to "Russisch"
    Then I should not see "Recommended programs"
    And I should not see "#recommended"

  Scenario: User with selected russian language cannot see the remix graph button and details on program page
    Given I am on "/pocketcode/program/1"
    And the selected language is "English"
    And I should see "Show Remix Graph"
    And I should see "1 remix"
    And the element "#remix-graph-button" should be visible
    And the element "#remix-graph-modal-link" should be visible
    When I switch the language to "Russisch"
    Then I should not see "Show Remix Graph"
    And I should not see "1 remix"
    And I should not see "#remix-graph-button"
    And the element "#remix-graph-modal-link" should not be visible
