Feature: Filtering programs with specific flavor

  In order provide an index of different flavored programs
  As a site owner
  I want to see only programs with my flavor

  Background:
    Given there are programs:
    | name         | flavor      |
    | Invaders     | pocketcode  |
    | Simple click | pocketphiropro |
    | A new world  | pocketcode  |
    | Soon to be   | pocketphiropro |


  Scenario: Get most viewed programs of flavor
    
    When I get the most viewed programs with "pocketcode/api/projects/mostViewed.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | A new world  |
      | Simple click |
      | Soon to be   |

  Scenario: Get most downloaded programs of flavor

    When I get the most downloaded programs with "pocketcode/api/projects/mostDownloaded.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | A new world  |
      | Simple click |
      | Soon to be   |

  Scenario: Get recent programs of flavor

    When I get the recent programs with "pocketcode/api/projects/recent.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | A new world  |
      | Simple click |
      | Soon to be   |

  Scenario: Get all programs of a user no matter the flavor

    Given All programs are from the same user
    When I get the user's programs with "pocketcode/api/projects/userPrograms.json"
    Then I should get following programs:
      | name          |
      | Invaders      |
      | Simple click  |
      | A new world   |
      | Soon to be    |
    