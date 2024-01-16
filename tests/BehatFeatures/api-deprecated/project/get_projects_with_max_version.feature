@api
Feature: MAX version feature; Allows old Apps to request only projects they can support

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | language version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.999            |
      | 2  | project 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.999            |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.999            |
      | 4  | project 4 |             | User1    | 133       | 34    | 01.01.2012 13:10 | 0.993            |
      | 5  | project 5 |             | User1    | 500       | 35    | 01.01.2012 13:20 | 0.993            |
      | 6  | project 6 |             | User1    | 600       | 36    | 01.01.2012 13:30 | 0.991            |
    And the current time is "01.08.2014 13:00"

  Scenario: show most recent projects with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/recent.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 6 |
      | project 5 |

  Scenario: show most recent projects with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/recent.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |

  Scenario: show only visible projects
    Given project "project 6" is not visible
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/recent.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |

# ----------------------------------------------------------------------------------------------------------------------

  Scenario: show most downloaded projects with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/mostDownloaded.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 6 |
      | project 5 |

  Scenario: show most downloaded projects with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/mostDownloaded.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |

  Scenario: show only visible projects
    Given project "project 6" is not visible
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/mostDownloaded.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |

# ----------------------------------------------------------------------------------------------------------------------

  Scenario: show most viewed projects with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 6 |
      | project 5 |

  Scenario: show most viewed projects with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |

  Scenario: show only visible projects
    Given project "project 6" is not visible
    And I have a parameter "max_version" with value "0.993"
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |

# ----------------------------------------------------------------------------------------------------------------------

  Scenario: show random projects with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.991"
    When I GET "/app/api/projects/randomProjects.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | project 6 |
