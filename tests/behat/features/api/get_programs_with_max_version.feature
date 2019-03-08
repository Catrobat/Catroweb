@api
Feature: Get the most downloaded programs

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | program 4 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.7.5   |
      | 5  | program 5 |             | User1    | 500       | 33    | 01.01.2012 13:00 | 0.7.5   |
      | 6  | program 6 |             | User1    | 600       | 33    | 01.01.2012 13:00 | 0.6.5   |
    And the current time is "01.08.2014 13:00"

  Scenario: show most recent programs with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/recent.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show most recent programs with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/recent.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/recent.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show most recent programs with limit and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/recentIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show most recent programs with limit, offset and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/recentIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/recentIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show most downloaded programs with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostDownloaded.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show most downloaded programs with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostDownloaded.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostDownloaded.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show most downloaded programs with limit and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostDownloadedIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show most downloaded programs with limit, offset and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostDownloadedIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostDownloadedIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show most viewed programs with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostViewed.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show most viewed programs with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostViewed.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostViewed.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show most viewed programs with limit and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostViewedIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show most viewed programs with limit, offset and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostViewedIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/mostViewedIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show random programs with limit and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/randomPrograms.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show random programs with limit, offset and max_version
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/randomPrograms.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/randomPrograms.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------
  Scenario: show random programs with limit and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/randomProgramIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 6 |
      | program 5 |

  Scenario: show random programs with limit, offset and max_version (IDs)
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/randomProgramIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |

  Scenario: show only visible programs
    Given program "program 6" is not visible
    And I have a parameter "max_version" with value "0.7.5"
    When I GET "/pocketcode/api/projects/randomProgramIDs.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 5 |
      | program 4 |
# ------------------------------------------------------------------

      