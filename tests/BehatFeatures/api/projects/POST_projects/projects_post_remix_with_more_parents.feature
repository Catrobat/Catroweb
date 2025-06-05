@api @projects @post @remixes @disabled
Feature: Upload a remixed program with multiple parents

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 12345    |

    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version | remix_root |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 3  | program 3 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 4  | program 4 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | false      |
      | 5  | program 5 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 6  | program 6 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 7  | program 7 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 8  | program 8 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 9  | program 9 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |

    #-------------------------------------------------------------------------------------------------------------------
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
    #-------------------------------------------------------------------------------------------------------------------
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

  Scenario: program upload with parent-URL referring to no existing Catrobat programs should not add any remix relations
  (except self referencing relation)
    Given I have a project with "catrobatLanguageVersion" set to "0.993" and "url" set to "Program 10 [/app/project/10], Program 11 [https://share.catrob.at/app/project/11]"
    When I upload the project with the id "10"
    Then the uploaded project should be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have no Catrobat ancestors except self-relation
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "Program 10 [/app/project/10], Program 11 [https://share.catrob.at/app/project/11]" in the xml

  Scenario: program upload with parent-URL referring to multiple existing Catrobat root programs
  but Catrobat language version 0.992 should not add any remix relations (except self referencing relation)
    Given I have a project with "catrobatLanguageVersion" set to "0.992" and "url" set to "Program 1 [/app/project/1], Program 8 [https://share.catrob.at/app/project/8]"
    When I upload the project with the id "10"
    Then the uploaded project should be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have no Catrobat ancestors except self-relation
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "" in the xml

  Scenario: program upload with parent-URL referring to multiple existing Catrobat root programs
  but Catrobat language version 0.991 should not add any remix relations (except self referencing relation)
    Given I have a project with "catrobatLanguageVersion" set to "0.991" and "url" set to "Program 1 [/app/project/1], Program 8 [https://share.catrob.at/app/project/8]"
    When I upload the project with the id "10"
    Then the uploaded project should be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have no Catrobat ancestors except self-relation
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "" in the xml

  Scenario: program upload with parent-URL referring to multiple existing Catrobat root programs
  but Catrobat language version 0.99 should not add any remix relations (except self referencing relation)
    Given I have a project with "catrobatLanguageVersion" set to "0.99" and "url" set to "Program 1 [/app/project/1], Program 8 [https://share.catrob.at/app/project/8]"
    When I upload the project with the id "10"
    Then the uploaded project should be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have no Catrobat ancestors except self-relation
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "" in the xml

  Scenario: program upload with parent-URL referring to multiple existing Catrobat root programs
  but Catrobat language version 0.92 should not add any remix relations (except self referencing relation)
    Given I have a project with "catrobatLanguageVersion" set to "0.92" and "url" set to "Program 1 [/app/project/1], Program 8 [https://share.catrob.at/app/project/8]"
    When I upload the project with the id "10"
    Then the uploaded project should be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have no Catrobat ancestors except self-relation
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat root programs and
  Catrobat language version 0.993 should correctly add remix relations
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #              (1)______________  (8)
    #               \               \ / \
    #               (2)_____       (10) (9)     <-- to be added (uploaded program will get ID "10")
    #               / \     \
    #             (3) (4)   |
    #              | \ |    |
    #             (5) (6)__/
    #               \ /
    #               (7)
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a project with "catrobatLanguageVersion" set to "0.993" and "url" set to "Program 1 [/app/project/1], Program 8 [https://share.catrob.at/app/project/8]"
    When I upload the project with the id "10"
    Then the uploaded project should not be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "8" and depth "1"
    And the uploaded project should have no further Catrobat forward ancestors
    And the uploaded project should have no Catrobat backward parents
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "Program 1 [/app/project/1], Program 8 [https://share.catrob.at/app/project/8]" in the xml

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
    Given I have a project with "catrobatLanguageVersion" set to "0.999" and "url" set to "Music Inventor [https://scratch.mit.edu/projects/29495624], The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/]"
    When I upload the project with the id "10"
    Then the uploaded project should be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have no Catrobat ancestors except self-relation
    And the uploaded project should have a Scratch parent having id "29495624"
    And the uploaded project should have a Scratch parent having id "70058680"
    And the uploaded project should have no further Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "Music Inventor [https://scratch.mit.edu/projects/29495624], The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/]" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat programs and
  Catrobat language version 1 should correctly add remix relations (example #1)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #              (1)
    #               \
    #               (2)_____
    #               / \     \
    #             (3) (4)   |
    #              | \ |    |
    #             (5) (6)__/|          (8)
    #               \ /     |           |
    #               (7)     |          (9)
    #                |      |           |
    #              (10)____/____________/           <-- to be added (uploaded program will get ID "10")
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a project with "catrobatLanguageVersion" set to "1" and "url" set to "Program 2 [/pocketalice/project/2], Merge 1 [Program 7 [/app/project/7], Program 9 [https://share.catrob.at/app/project/9]]"
    When I upload the project with the id "10"
    Then the uploaded project should not be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "4"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "5"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "4"
    And the uploaded project should have a Catrobat forward ancestor having id "3" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "5" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "7" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "8" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "9" and depth "1"
    And the uploaded project should have no further Catrobat forward ancestors
    And the uploaded project should have no Catrobat backward parents
    And the uploaded project should have no Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "Program 2 [/pocketalice/project/2], Merge 1 [Program 7 [/app/project/7], Program 9 [https://share.catrob.at/app/project/9]]" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat programs and
  Catrobat language version 1.0 should correctly add remix relations (example #2)
    Given there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 29495624          | 2                 |
      | 70058680          | 6                 |

    And there are backward remix relations:
      | parent_id | child_id |
      | 9         | 8        |
      | 6         | 4        |
      | 6         | 1        |

    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)
    #               \ /
    #               (2)_____
    #               / \     \   (SCRATCH #2)
    #             (3) (4)   |____/
    #              | \ |    |     (8)
    #             (5) (6)__/|_____/ \
    #               \ / \  /       (9)
    #               (7) (10)            <-- to be added (uploaded program will get ID "10")
    #
    # Note: there are some additional remix backward relations (see table above) not shown in the graph for clarity reasons.
    #       This test will also check that no backward parents of parent program will be linked to the uploaded program.
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a project with "catrobatLanguageVersion" set to "1.0" and "url" set to "The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/], Merge 2 [Program 2 [/pocketalice/project/2], Merge 1 [Program 6 [/app/project/6], Program 8 [https://share.catrob.at/app/project/8]]]"
    When I upload the project with the id "10"
    Then the uploaded project should not be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "4"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "3" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "8" and depth "1"
    And the uploaded project should have no further Catrobat forward ancestors
    And the uploaded project should have no Catrobat backward parents
    And the uploaded project should have a Scratch parent having id "70058680"
    And the uploaded project should have no further Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/], Merge 2 [Program 2 [/pocketalice/project/2], Merge 1 [Program 6 [/app/project/6], Program 8 [https://share.catrob.at/app/project/8]]]" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat programs and
  Catrobat language version 1.1 should correctly add remix relations (example #2)
    Given there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 29495624          | 2                 |
      | 70058680          | 6                 |

    And there are backward remix relations:
      | parent_id | child_id |
      | 9         | 8        |
      | 6         | 4        |
      | 6         | 1        |

    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)
    #               \ /
    #               (2)_____
    #               / \     \   (SCRATCH #2)
    #             (3) (4)   |____/
    #              | \ |    |     (8)
    #             (5) (6)__/|_____/ \
    #               \ / \  /       (9)
    #               (7) (10)            <-- to be added (uploaded program will get ID "10")
    #
    # Note: there are some additional remix backward relations (see table above) not shown in the graph for clarity reasons.
    #       This test will also check that no backward parents of parent program will be linked to the uploaded program.
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a project with "catrobatLanguageVersion" set to "1.1" and "url" set to "The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/], Merge 2 [Program 2 [/pocketalice/project/2], Merge 1 [Program 6 [/app/project/6], Program 8 [https://share.catrob.at/app/project/8]]]"
    When I upload the project with the id "10"
    Then the uploaded project should not be a remix root
    And the uploaded project should have remix migration date NOT NULL
    And the uploaded project should have a Catrobat forward ancestor having its own id and depth "0"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "4"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "3"
    And the uploaded project should have a Catrobat forward ancestor having id "3" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "2"
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "1"
    And the uploaded project should have a Catrobat forward ancestor having id "8" and depth "1"
    And the uploaded project should have no further Catrobat forward ancestors
    And the uploaded project should have no Catrobat backward parents
    And the uploaded project should have a Scratch parent having id "70058680"
    And the uploaded project should have no further Scratch parents
    And the uploaded project should have no Catrobat forward descendants except self-relation
    And the uploaded project should have RemixOf "The Colour Divide - Trailer [https://scratch.mit.edu/projects/70058680/], Merge 2 [Program 2 [/pocketalice/project/2], Merge 1 [Program 6 [/app/project/6], Program 8 [https://share.catrob.at/app/project/8]]]" in the xml
