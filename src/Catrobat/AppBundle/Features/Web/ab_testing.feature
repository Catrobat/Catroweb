@homepage
Feature: A/B testing for recommendation system & remix graph

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |
      | OtherUser| 123456   | dddddddddd  | dev2@pocketcode.org |

    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | remix_root |
      | 1  | Minions   | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | Galaxy    | p2          | OtherUser| 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | false      |
      | 3  | Alone     | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   | true       |

    And there are likes:
      | username  | program_id | type | created at       |
      | Catrobat  | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat  | 2          | 2    | 01.01.2017 12:00 |
      | OtherUser | 1          | 4    | 01.01.2017 12:00 |

  Scenario: User with selected russian language sees the recommendations on homepage (a.k.a. index page)
    Given I am on "/pocketcode/"
    And the selected language is "English"
    And I should see "Recommended programs"
    And the element "#recommended" should be visible
    And I wait for a second
    And I should see a recommended homepage program having ID "1" and name "Minions"
    When I switch the language to "Russisch"
    And I should see "Recommended programs"
    And the element "#recommended" should be visible
    And I should see a recommended homepage program having ID "1" and name "Minions"
    When I switch the language to "French"
    And I should see "Recommended programs"
    And the element "#recommended" should be visible
    And I should see a recommended homepage program having ID "1" and name "Minions"

  Scenario: User with selected russian language sees the remix graph button and details on program page
    Given there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |

    Given I am on "/pocketcode/program/1"
    And the selected language is "English"
    And I should see "Show Remix Graph"
    And I should see "1 remix"
    And the element "#remix-graph-button" should be visible
    And the element "#remix-graph-modal-link" should be visible
    When I switch the language to "Russisch"
    And I should see "Show Remix Graph"
    And I should see "1 remix"
    And the element "#remix-graph-button" should be visible
    And the element "#remix-graph-modal-link" should be visible

  @rec2
  Scenario: User with selected russian language sees the recommended programs that have been downloaded by other users on program page
    Given there are program download statistics:
      | id | program_id | downloaded_at        | ip             | latitude      |  longitude  | country_code  | country_name | street              | postal_code      |  locality   | user_agent | username  | referrer |
      | 1  | 1          |  2017-02-09 16:01:00 | 88.116.169.222 | 47.2          | 10.7        | AT            | Austria      | Duck Street 1       | 1234             | Entenhausen | okhttp     | OtherUser | Facebook |
      | 2  | 3          |  2017-02-09 16:02:00 | 88.116.169.222 | 47.2          | 10.7        | AT            | Austria      | Duck Street 1       | 1234             | Entenhausen | okhttp     | OtherUser | Facebook |

    And I am on "/pocketcode/program/1"
    When the selected language is "English"
    Then There should be recommended specific programs
    And the element "#specific-programs-recommendations" should be visible
    When I switch the language to "Russisch"
    Then There should be recommended specific programs
    And the element "#specific-programs-recommendations" should be visible
