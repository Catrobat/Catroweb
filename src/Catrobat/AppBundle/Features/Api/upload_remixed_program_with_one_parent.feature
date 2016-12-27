@api @upload @remixes
Feature: Upload a remixed program with one parent

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |

    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | remix_root |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 3  | program 3 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 4  | program 4 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 5  | program 5 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 6  | program 6 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 7  | program 7 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 8  | program 8 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 9  | program 9 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |

    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like according to the following forward remix relations (closure table):
    #              (1)
    #               \
    #               (2)
    #               /
    #             (3)
    #-------------------------------------------------------------------------------------------------------------------
    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 1           | 3             | 2     |
      | 2           | 2             | 0     |
      | 2           | 3             | 1     |
      | 3           | 3             | 0     |

  Scenario: program upload with no parent-URL should not add any remix relations (except self referencing relation)
    Given I have a program with "url" set to ""
    When I upload a program
    Then the uploaded program should be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have no Catrobat ancestors except self-relation
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "" in the xml

  Scenario: program upload with local program name parent-URL should not add any remix relations (except self referencing relation)
    Given I have a program with "url" set to "My first program"
    When I upload a program
    Then the uploaded program should be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have no Catrobat ancestors except self-relation
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "My first program" in the xml

  Scenario: program upload with invalid parent-URL should not add any remix relations (except self referencing relation)
    Given I have a program with "url" set to "https://www.google.com"
    When I upload a program
    Then the uploaded program should be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have no Catrobat ancestors except self-relation
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "https://www.google.com" in the xml

  Scenario: program upload with parent-URL referring to own Catrobat program should not add any remix relations
            (except self referencing relation)
    Given I have a program with "url" set to "/pocketcode/program/10"
    When I upload a program
    Then the uploaded program should be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have no Catrobat ancestors except self-relation
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketcode/program/10" in the xml

  Scenario: program upload with parent-URL referring to no existing Catrobat program should not add any remix relations
            (except self referencing relation)
    Given I have a program with "url" set to "/pocketcode/program/11"
    When I upload a program
    Then the uploaded program should be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have no Catrobat ancestors except self-relation
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketcode/program/11" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat non-root program
            should correctly add remix relations (example #1)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #                (1)
    #               /  \
    #             (2) (10)     <-- to be added (uploaded program will get ID "10")
    #              |
    #             (3)
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a program with "url" set to "/pocketcode/program/1"
    When I upload a program
    Then the uploaded program should not be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "1"
    And the uploaded program should have no further Catrobat forward ancestors
    And the uploaded program should have no Catrobat backward parents
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketcode/program/1" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat non-root program
            should correctly add remix relations (example #2)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #               (1)
    #                |
    #               (2)
    #              /  \
    #            (3) (10)      <-- to be added (uploaded program will get ID "10")
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a program with "url" set to "/pocketalice/program/2"
    When I upload a program
    Then the uploaded program should not be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have a Catrobat forward ancestor having id "2" and depth "1"
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "2"
    And the uploaded program should have no further Catrobat forward ancestors
    And the uploaded program should have no Catrobat backward parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketalice/program/2" in the xml

  Scenario: program upload with parent-URL referring to existing Catrobat non-root program
            should correctly add remix relations (example #3)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #              (1)
    #               \
    #               (2)
    #               /
    #             (3)
    #              \
    #             (10)      <-- to be added (uploaded program will get ID "10")
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a program with "url" set to "/pocketalice/program/3"
    When I upload a program
    Then the uploaded program should not be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have a Catrobat forward ancestor having id "3" and depth "1"
    And the uploaded program should have a Catrobat forward ancestor having id "2" and depth "2"
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "3"
    And the uploaded program should have no further Catrobat forward ancestors
    And the uploaded program should have no Catrobat backward parents
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketalice/program/3" in the xml

  Scenario: program upload with parent-URL referring to Scratch program should correctly add remix relations
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #              (Scratch #70058680)
    #                       |
    #                     (10)      <-- to be added (uploaded program will get ID "10")
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I have a program with "url" set to "https://scratch.mit.edu/projects/70058680"
    When I upload a program
    Then the uploaded program should be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have no Catrobat ancestors except self-relation
    And the uploaded program should have a Scratch parent having id "70058680"
    And the uploaded program should have no further Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "https://scratch.mit.edu/projects/70058680" in the xml

  Scenario: custom graph given (example #1)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like (closure table):
    #
    #                (4)    (SCRATCH) ___
    #                  \    /  |  \      \
    #                   \  /   |   \      |
    #                   (5)    /  (7)     |
    #                  /  \   /__/ |      |
    #                 |    \ /     |      |
    #                 |    (6)    (8)____/|
    #                 |      \    /       |
    #                  \______ (9) _______/
    #                           |
    #                         (10)                 <-- to be added (uploaded program will get ID "10")
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 4           | 4             | 0     |
      | 5           | 5             | 0     |
      | 6           | 6             | 0     |
      | 7           | 7             | 0     |
      | 8           | 8             | 0     |
      | 9           | 9             | 0     |
      | 4           | 5             | 1     |
      | 4           | 6             | 2     |
      | 4           | 9             | 2     |
      | 4           | 9             | 3     |
      | 5           | 6             | 1     |
      | 5           | 9             | 1     |
      | 5           | 9             | 2     |
      | 6           | 9             | 1     |
      | 7           | 6             | 1     |
      | 7           | 8             | 1     |
      | 7           | 9             | 2     |
      | 8           | 9             | 1     |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 5                 |
      | 70058680          | 6                 |
      | 70058680          | 7                 |
      | 70058680          | 8                 |
      | 70058680          | 9                 |

    Given I have a program with "url" set to "/pocketalice/program/9"
    When I upload a program
    Then the uploaded program should not be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have a Catrobat forward ancestor having id "9" and depth "1"
    And the uploaded program should have a Catrobat forward ancestor having id "6" and depth "2"
    And the uploaded program should have a Catrobat forward ancestor having id "5" and depth "2"
    And the uploaded program should have a Catrobat forward ancestor having id "5" and depth "3"
    And the uploaded program should have a Catrobat forward ancestor having id "4" and depth "3"
    And the uploaded program should have a Catrobat forward ancestor having id "4" and depth "4"
    And the uploaded program should have a Catrobat forward ancestor having id "7" and depth "3"
    And the uploaded program should have a Catrobat forward ancestor having id "8" and depth "2"
    And the uploaded program should have no further Catrobat forward ancestors
    And the uploaded program should have no Catrobat backward parents
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketalice/program/9" in the xml

  Scenario: custom graph given (example #2)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like (closure table):
    #
    #                                                          (4)    (SCRATCH) ___
    #                                                            \    /  |  \      \
    #                                                             \  /   |   \      |
    #                                                             (5)    /  (7)     |
    #                                                            /  \   /__/ |      |
    # to be added (uploaded program will get ID "10") -->     (10)   \ /     |      |
    #                                                                (6)    (8)____/
    #                                                                  \    /
    #                                                                   (9)
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 4           | 4             | 0     |
      | 5           | 5             | 0     |
      | 6           | 6             | 0     |
      | 7           | 7             | 0     |
      | 8           | 8             | 0     |
      | 9           | 9             | 0     |
      | 4           | 5             | 1     |
      | 4           | 6             | 2     |
      | 4           | 9             | 2     |
      | 4           | 9             | 3     |
      | 5           | 6             | 1     |
      | 5           | 9             | 2     |
      | 6           | 9             | 1     |
      | 7           | 6             | 1     |
      | 7           | 8             | 1     |
      | 7           | 9             | 2     |
      | 8           | 9             | 1     |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 5                 |
      | 70058680          | 6                 |
      | 70058680          | 7                 |
      | 70058680          | 8                 |

    Given I have a program with "url" set to "/pocketalice/program/5"
    When I upload a program
    Then the uploaded program should not be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have a Catrobat forward ancestor having id "5" and depth "1"
    And the uploaded program should have a Catrobat forward ancestor having id "4" and depth "2"
    And the uploaded program should have no further Catrobat forward ancestors
    And the uploaded program should have no Catrobat backward parents
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketalice/program/5" in the xml

  Scenario: custom graph with backward relation to parent given (example #3)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like (closure table):
    #
    #               (4)    (SCRATCH) ___
    #                 \    /  |  \      \
    #                  \  /   |   \      |
    #                  (5)    /  (7)     |
    #                    \   /__/ | \    |
    #                     \ /     | (10) |    <-- to be added (uploaded program will get ID "10")
    #                     (6)    (8)____/
    #                       \    /
    #                        (9)
    #
    # Note: unlike the previous example there is an additional remix backward relation from program #9 to
    #       the program #7, in this example. But this is not drawn in the graph above for clarity reasons.
    #       Since program #7 will be the parent of the uploaded program #10, this test will also check that no ancestors
    #       of program #9 (including itself) are linked to the uploaded program.
    #-------------------------------------------------------------------------------------------------------------------
    Given there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 4           | 4             | 0     |
      | 5           | 5             | 0     |
      | 6           | 6             | 0     |
      | 7           | 7             | 0     |
      | 8           | 8             | 0     |
      | 9           | 9             | 0     |
      | 4           | 5             | 1     |
      | 4           | 6             | 2     |
      | 4           | 9             | 2     |
      | 4           | 9             | 3     |
      | 5           | 6             | 1     |
      | 5           | 9             | 2     |
      | 6           | 9             | 1     |
      | 7           | 6             | 1     |
      | 7           | 8             | 1     |
      | 7           | 9             | 2     |
      | 8           | 9             | 1     |

    And there are backward remix relations:
      | parent_id | child_id |
      | 9         | 7        |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 5                 |
      | 70058680          | 6                 |
      | 70058680          | 7                 |
      | 70058680          | 8                 |

    Given I have a program with "url" set to "/pocketalice/program/7"
    When I upload a program
    Then the uploaded program should not be a remix root
    And the uploaded program should have remix migration date NOT NULL
    And the uploaded program should have a Catrobat forward ancestor having id "10" and depth "0"
    And the uploaded program should have a Catrobat forward ancestor having id "7" and depth "1"
    And the uploaded program should have no further Catrobat forward ancestors
    And the uploaded program should have no Catrobat backward parents
    And the uploaded program should have no Scratch parents
    And the uploaded program should have no Catrobat forward descendants except self-relation
    And the uploaded program should have RemixOf "/pocketalice/program/7" in the xml
