@homepage
Feature: Recommendations on homepage (a.k.a. index page)

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |
      | OtherUser| 123456   | dddddddddd  | dev2@pocketcode.org |

  Scenario: Recommended programs on homepage (a.k.a. index page)
    Given there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | remix_root |
      | 1  | Minions   | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | Galaxy    | p2          | OtherUser| 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | false      |
      | 3  | Alone     | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   | true       |

    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |

    When I am on "/pocketcode/"
    And the selected language is "English"
    And I should see "Recommended programs"
    And the element "#recommended" should be visible
    Then I should see a recommended homepage program having ID "1" and name "Minions"

  Scenario: No recommended programs on homepage (a.k.a. index page)
    Given there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | remix_root |
      | 1  | Minions   | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | Galaxy    | p2          | OtherUser| 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | true       |
      | 3  | Alone     | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   | true       |

    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |

    When I am on "/pocketcode/"
    And the selected language is "English"
    And I should see "Recommended programs"
    And the element "#recommended" should be visible
    Then I should not see any recommended homepage programs
