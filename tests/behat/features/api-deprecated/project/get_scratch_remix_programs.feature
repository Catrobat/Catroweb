@api
Feature: Get a remixed program from Scratch

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |

    And there are programs:
      | id | name       | description | owned by | downloads | views | upload time      | version | remix_root |
      | 1  | program 1  | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | program 2  |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 3  | program 3  |             | Catrobat | 132       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 4  | program 4  | p1          | Catrobat | 5         | 13    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 5  | program 5  |             | Catrobat | 36        | 7     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 6  | program 6  |             | Catrobat | 135       | 31    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 7  | program 7  |             | Catrobat | 133       | 35    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 8  | program 8  |             | Catrobat | 134       | 30    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 9  | program 9  |             | Catrobat | 131       | 38    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 10 | program 10 |             | Catrobat | 131       | 38    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 11 | program 11 |             | Catrobat | 131       | 38    | 01.01.2012 13:00 | 0.8.5   | false      |

    Given there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 6                 |
      | 70058680          | 5                 |
      | 70058680          | 7                 |
      | 70058680          | 8                 |
      | 70058680          | 9                 |

  Scenario: show scratch remixes programs with limit and offset
    Given I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/scratchRemixes.json" with these parameters
    Then I should get 5 programs in the following order:
      | Name      |
      | program 9 |
      | program 7 |
      | program 6 |
      | program 8 |
      | program 5 |

  Scenario: program upload with parent-URL referring only to Scratch programs and
  Catrobat language version 0.999 should correctly add remix relations
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #       (SCRATCH #1)  (SCRATCH #2)
    #                \      /
    #                \     /
    #                 (10)            <-- to be added (uploaded program will get ID "10")
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a program with "catrobatLanguageVersion" set to "0.999" and "url" set to "Music Inventor [https://scratch.mit.edu/projects/29495624], The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/]"
    When I upload the generated program with the id "18" and name "program 18", API version 1
    Then the uploaded program should be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having its own id and depth "0", API version 1
    And the uploaded program should have no Catrobat ancestors except self-relation, API version 1
    And the uploaded program should have a Scratch parent having id "29495624", API version 1
    And the uploaded program should have a Scratch parent having id "70058680", API version 1
    And the uploaded program should have no further Scratch parents, API version 1
    And the uploaded program should have no Catrobat forward descendants except self-relation, API version 1
    And the uploaded program should have RemixOf "Music Inventor [https://scratch.mit.edu/projects/29495624], The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/]" in the xml, API version 1
    Given I have a parameter "limit" with value "6"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/scratchRemixes.json" with these parameters
    Then I should get 6 programs in the following order:
      | Name      |
      | program 9 |
      | program 7 |
      | program 6 |
      | program 8 |
      | program 5 |
      | program 18 |

  Scenario: program upload with parent-URL referring to existing Catrobat programs and
  Catrobat language version 1.0 should correctly add remix relations (example #2)
    Given there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 29495624          | 10                |
      | 70058680          | 11                |

    And there are backward remix relations:
      | parent_id | child_id |
      | 9         | 8        |
      | 6         | 4        |
      | 6         | 1        |

    Given I have a program with "catrobatLanguageVersion" set to "1.0" and "url" set to "The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/], Merge 2 [Program 2 [/pocketalice/project/2], Merge 1 [Program 6 [/app/project/6], Program 8 [https://share.catrob.at/app/project/8]]]"
    When I upload the generated program with the id "18" and name "program 18", API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having its own id and depth "0", API version 1
    And the uploaded program should have no Catrobat backward parents, API version 1
    And the uploaded program should have a Scratch parent having id "70058680", API version 1
    And the uploaded program should have no further Scratch parents, API version 1
    And the uploaded program should have no Catrobat forward descendants except self-relation, API version 1
    And the uploaded program should have RemixOf "The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/], Merge 2 [Program 2 [/pocketalice/project/2], Merge 1 [Program 6 [/app/project/6], Program 8 [https://share.catrob.at/app/project/8]]]" in the xml, API version 1
    Given I have a parameter "limit" with value "9"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/scratchRemixes.json" with these parameters
    Then I should get 8 programs in the following order:
      | Name       |
      | program 10 |
      | program 11 |
      | program 9  |
      | program 7  |
      | program 6  |
      | program 8  |
      | program 5  |
      | program 18 |












