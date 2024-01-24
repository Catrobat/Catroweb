Feature: Filtering programs with specific flavor

  In order provide an index of different flavored programs
  As a site owner
  I want to see only programs with my flavor

  Background:
    Given there are projects:
      | name          | flavor        |
      | Invaders      | pocketcode    |
      | Simple click  | luna          |
      | A new world   | pocketcode    |
      | Soon to be    | luna          |
      | Just for fun  | luna          |
      | New adventure | create@school |
      | Amazing race  | create@school |
      | Test game     | pocketcode    |


  Scenario: Get most viewed programs of flavor pocketcode

    When I get the most viewed projects with "app/api/projects/mostViewed.json"
    Then I should get following projects:
      | name          |
      | Invaders      |
      | A new world   |
      | Simple click  |
      | Soon to be    |
      | Just for fun  |
      | New adventure |
      | Amazing race  |
      | Test game     |

  Scenario: Get most viewed programs of flavor luna

    When I get the most viewed projects with "luna/api/projects/mostViewed.json"
    Then I should get following projects:
      | name          |
      | Simple click  |
      | Soon to be    |
      | Just for fun  |
      | Invaders      |
      | A new world   |
      | New adventure |
      | Amazing race  |
      | Test game     |

  Scenario: Get most downloaded programs of pocketcode

    When I get the most downloaded projects with "app/api/projects/mostDownloaded.json"
    Then I should get following projects:
      | name          |
      | Invaders      |
      | A new world   |
      | Simple click  |
      | Soon to be    |
      | Just for fun  |
      | New adventure |
      | Amazing race  |
      | Test game     |

  Scenario: Get most downloaded programs of flavor luna

    When I get the most downloaded projects with "luna/api/projects/mostDownloaded.json"
    Then I should get following projects:
      | name          |
      | Simple click  |
      | Soon to be    |
      | Just for fun  |
      | Invaders      |
      | A new world   |
      | New adventure |
      | Amazing race  |
      | Test game     |

  Scenario: Get recent programs of flavor pocketcode

    When I get the recent projects with "app/api/projects/recent.json"
    Then I should get following projects:
      | name          |
      | Simple click  |
      | Soon to be    |
      | Just for fun  |
      | Invaders      |
      | A new world   |
      | New adventure |
      | Amazing race  |
      | Test game     |

  Scenario: Get recent programs of flavor luna

    When I get the recent projects with "luna/api/projects/recent.json"
    Then I should get following projects:
      | name         |
      | Simple click |
      | Soon to be   |
      | Just for fun |

  Scenario: Get recent projects of flavor create@school

    When I get the recent projects with "create@school/api/projects/recent.json"
    Then I should get following projects:
      | name          |
      | New adventure |
      | Amazing race  |

  Scenario: Get all programs of a user no matter the flavor (pocketcode)

    Given I get the user's projects with "app/api/projects/userProjects.json"
    Then I should get following projects:
      | name          |
      | Invaders      |
      | Simple click  |
      | A new world   |
      | Soon to be    |
      | Just for fun  |
      | New adventure |
      | Amazing race  |
      | Test game     |

  Scenario: Get all programs of a user no matter the flavor (luna)

    Given I get the user's projects with "luna/api/projects/userProjects.json"
    Then I should get following projects:
      | name          |
      | Invaders      |
      | Simple click  |
      | A new world   |
      | Soon to be    |
      | Just for fun  |
      | New adventure |
      | Amazing race  |
      | Test game     |

    