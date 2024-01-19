@api @projects @post @remixes @repost
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
      | 8  | program 8 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | true       |
      | 9  | program 9 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |

    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like according to the following forward remix relations (closure table):
    #
    #             (1) (SCRATCH #1)
    #               \ /
    #               (2)_____
    #               / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |
    #              | \ |    |        |           (9)
    #             (5) (6)__/________/
    #               \ /
    #               (7)
    #
    # Note: there are some additional remix backward relations (see backward remix table below) not shown in the graph
    #       for clarity reasons.
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

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 29495624          | 2                 |
      | 70058680          | 6                 |

    And there are backward remix relations:
      | parent_id | child_id |
      | 6         | 4        |
      | 6         | 2        |

  Scenario: reuploading program 2 with no parent-URLs should unlink all former remix ancestors (except self referencing relation)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 2" and "url" set to ""
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "0", API version 2
    And the uploaded project should have no Catrobat ancestors except self-relation, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat forward descendants except self-relation
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 2 with parent-URLs referring to no existing Catrobat programs should unlink all former
  remix ancestors (except self referencing relation)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 2", "url" set to "Program 10 [/app/project/10], Program 11 [https://share.catrob.at/app/project/11]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "0", API version 2
    And the uploaded project should have no Catrobat ancestors except self-relation, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 10 [/app/project/10], Program 11 [https://share.catrob.at/app/project/11]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat forward descendants except self-relation
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 2 with parent-URL referring only to Scratch program should unlink all former
  Catrobat remix ancestors (except self referencing relation)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 2" and "url" set to "Music Inventor [https://scratch.mit.edu/projects/29495624]"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "0", API version 2
    And the uploaded project should have no Catrobat ancestors except self-relation, API version 2
    And the uploaded project should have a Scratch parent having id "29495624", API version 2
    And the uploaded project should have no further Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Music Inventor [https://scratch.mit.edu/projects/29495624]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat forward descendants except self-relation
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 2 with parent-URLs referring only to Scratch program but no valid Catrobat programs should
  unlink all former Catrobat remix ancestors (except self referencing relation)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 2", "url" set to "Program 10 [/app/project/10], Music Inventor [https://scratch.mit.edu/projects/29495624]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "0", API version 2
    And the uploaded project should have no Catrobat ancestors except self-relation, API version 2
    And the uploaded project should have a Scratch parent having id "29495624", API version 2
    And the uploaded project should have no further Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 10 [/app/project/10], Music Inventor [https://scratch.mit.edu/projects/29495624]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat forward descendants except self-relation
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 4 with same parent-URLs should change remix graph
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 4", "url" set to "Program 2 [/app/project/2], Program 6 [/app/project/6]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2", API version 2
    And the uploaded project should have a Catrobat backward parent having id "6", API version 2
    And the uploaded project should have no further Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 2 [/app/project/2], Program 6 [/app/project/6]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 4 with parent-URLs referring only to its Catrobat forward ancestor (program 2) should
  unlink its Catrobat backward parent (program 6)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 4" and "url" set to "Program 2 [/app/project/2]"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 2 [/app/project/2]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 4 with only backward parent (program 6) should only
  unlink all former Catrobat forward ancestors
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    /       \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but 6 is a backward parent of 4 as well (not drawn in this graph).
    #       6 is also a backward parent of 2.
    #       Expected result after upload: 6 should remain being backward parent of 4 and 2.
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 4" and "url" set to "Program 6 [/app/project/6]"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "0", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat backward parent having id "6", API version 2
    And the uploaded project should have no further Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 6 [/app/project/6]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 3 with child program (program 6) as new parent should create backward relation
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                      \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: 6 should become backward parent of 3, as 6 is already a child before upload!
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 3" and "url" set to "Program 6 [/app/project/6]"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "3" and depth "0", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat backward parent having id "6", API version 2
    And the uploaded project should have no further Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 6 [/app/project/6]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 3 with child program (program 6) as only parent should create backward relation and
  remove all former forward ancestors
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                      \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: 7 should become backward parent of 3, as 7 is already a descendant of 3 before upload!
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 3" and "url" set to "Program 7 [/app/project/7]"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "3" and depth "0", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat backward parent having id "7", API version 2
    And the uploaded project should have no further Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 7 [/app/project/7]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 2 with other Scratch program and child programs (program 6 and 7)
  as additional parents should create backward relations and replace old Scratch parent with new Scratch parent
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)  (SCRATCH #2)
    #               \ /                                                 \  _______________/|
    #               (2)_____                                            (2)_____           |
    #               / \     \   (SCRATCH #2)     (8)                    / \     \          |           (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |          |            |
    #              | \ |    |        |           (9)                   | \ |    |          |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/__________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: 6 and 7 should be a backward parent of 2.
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 2", "url" set to "Program 6[https://share.catrob.at/app/project/6],Program 7[/pocketalice/project/7/], Merge 1[Program 1 [/flavors/project/1], The Colour Divide - Trailer[https://scratch.mit.edu/projects/70058680]]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "1", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat backward parent having id "7", API version 2
    And the uploaded project should have a Catrobat backward parent having id "6", API version 2
    And the uploaded project should have no further Catrobat backward parents, API version 2
    And the uploaded project should have a Scratch parent having id "70058680", API version 2
    And the uploaded project should have no further Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 6[https://share.catrob.at/app/project/6],Program 7[/pocketalice/project/7/], Merge 1[Program 1 [/flavors/project/1], The Colour Divide - Trailer[https://scratch.mit.edu/projects/70058680]]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading root program 2 becomes backward child of all of its child programs
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   | \ |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: all Catrobat childs (2, 3, 4, 5, 6 and 7) become backward parent of program 1
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 1", "url" set to "program 1[/pocketalice/project/1],Merge 4 [program 7[/pocketalice/project/7], Merge3 [Program 6[/app/project/6], Merge 2[Program 5[https://share.catrob.at/app/project/5],Program 4[/pocketalice/project/4], Merge 1[Program 2 [/flavors/project/2], Program 3 [/flavors/project/3]]]]]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "0", API version 2
    And the uploaded project should have no Catrobat forward ancestors except self-relation, API version 2
    And the uploaded project should have a Catrobat backward parent having id "2", API version 2
    And the uploaded project should have a Catrobat backward parent having id "3", API version 2
    And the uploaded project should have a Catrobat backward parent having id "4", API version 2
    And the uploaded project should have a Catrobat backward parent having id "5", API version 2
    And the uploaded project should have a Catrobat backward parent having id "6", API version 2
    And the uploaded project should have a Catrobat backward parent having id "7", API version 2
    And the uploaded project should have no further Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "4", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "program 1[/pocketalice/project/1],Merge 4 [program 7[/pocketalice/project/7], Merge3 [Program 6[/app/project/6], Merge 2[Program 5[https://share.catrob.at/app/project/5],Program 4[/pocketalice/project/4], Merge 1[Program 2 [/flavors/project/2], Program 3 [/flavors/project/3]]]]]" in the xml, API version 2

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no further Catrobat forward descendants
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 2 with new parent (program 9) should inherit all its forward relations
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #                                                                 (8)
    #                                                                  |
    #                                                                 (9)
    #                                                                  |
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |
    #              | \ |    |        |           (9)                   | \ |    |        |
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: all Catrobat childs (2, 3, 4, 5, 6 and 7) become backward parent of program 1
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 1" and "url" set to "program 9[/pocketalice/project/9]"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "9" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "8" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "6" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "4", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "program 9[/pocketalice/project/9]" in the xml, API version 2

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have a Catrobat forward ancestor having id "9" and depth "2"
    And the project "2" should have a Catrobat forward ancestor having id "8" and depth "3"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have a Catrobat forward ancestor having id "9" and depth "3"
    And the project "3" should have a Catrobat forward ancestor having id "8" and depth "4"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no further Catrobat forward descendants
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have a Catrobat forward ancestor having id "9" and depth "3"
    And the project "4" should have a Catrobat forward ancestor having id "8" and depth "4"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have a Catrobat forward ancestor having id "9" and depth "4"
    And the project "5" should have a Catrobat forward ancestor having id "8" and depth "5"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should not be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "6" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "6" should have a Catrobat forward ancestor having id "9" and depth "3"
    And the project "6" should have a Catrobat forward ancestor having id "9" and depth "4"
    And the project "6" should have a Catrobat forward ancestor having id "8" and depth "4"
    And the project "6" should have a Catrobat forward ancestor having id "8" and depth "5"
    And the project "6" should have no further Catrobat forward ancestors
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have a Scratch parent having id "70058680"
    And the project "6" should have no further Scratch parents
    And the project "6" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "9" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "9" and depth "5"
    And the project "7" should have a Catrobat forward ancestor having id "8" and depth "5"
    And the project "7" should have a Catrobat forward ancestor having id "8" and depth "6"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have a Catrobat forward descendant having id "1" and depth "2"
    And the project "8" should have a Catrobat forward descendant having id "2" and depth "3"
    And the project "8" should have a Catrobat forward descendant having id "3" and depth "4"
    And the project "8" should have a Catrobat forward descendant having id "4" and depth "4"
    And the project "8" should have a Catrobat forward descendant having id "5" and depth "5"
    And the project "8" should have a Catrobat forward descendant having id "6" and depth "4"
    And the project "8" should have a Catrobat forward descendant having id "6" and depth "5"
    And the project "8" should have a Catrobat forward descendant having id "7" and depth "5"
    And the project "8" should have a Catrobat forward descendant having id "7" and depth "6"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have a Catrobat forward descendant having id "1" and depth "1"
    And the project "9" should have a Catrobat forward descendant having id "2" and depth "2"
    And the project "9" should have a Catrobat forward descendant having id "3" and depth "3"
    And the project "9" should have a Catrobat forward descendant having id "4" and depth "3"
    And the project "9" should have a Catrobat forward descendant having id "5" and depth "4"
    And the project "9" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "9" should have a Catrobat forward descendant having id "6" and depth "4"
    And the project "9" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "9" should have a Catrobat forward descendant having id "7" and depth "5"
    And the project "9" should have no further Catrobat forward descendants

  Scenario: reuploading program 6 with one less parent (program 3) should unlink only forward relation to program 3
  but not deeper forward relations since these forward relations (1-6 and 2-6) are still needed
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)_____
    #               / \     \   (SCRATCH #2)     (8)                    / \     \   (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)   |        |            |
    #              | \ |    |        |           (9)                   |   |    |        |           (9)
    #             (5) (6)__/________/                                 (5) (6)__/________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6", "url" set to "Merge 1[Program 4 [/app/project/4], Program 2 [/app/project/2]],The Colour Divide - Trailer[https://scratch.mit.edu/projects/70058680]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "1", API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have a Scratch parent having id "70058680", API version 2
    And the uploaded project should have no further Scratch parents, API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Merge 1[Program 4 [/app/project/4], Program 2 [/app/project/2]],The Colour Divide - Trailer[https://scratch.mit.edu/projects/70058680]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 6 with one less parent (program 3) should unlink all program 3's forward relations
  since they have different depth level than the ones still needed
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)
    #               / \     \   (SCRATCH #2)     (8)                    / \         (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)            |            |
    #              | \ |    |        |           (9)                   |   |             |           (9)
    #             (5) (6)__/________/                                 (5) (6)___________/
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6", "url" set to "Program 4 [/app/project/4],The Colour Divide - Trailer[https://scratch.mit.edu/projects/70058680]" and "catrobatLanguageVersion" set to "0.993"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "1", API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have a Scratch parent having id "70058680", API version 2
    And the uploaded project should have no further Scratch parents, API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 4 [/app/project/4],The Colour Divide - Trailer[https://scratch.mit.edu/projects/70058680]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 6 with removed Scratch parent and with one less Catrobat parent (program 3) should
  unlink Scratch parent relation and also unlink all program 3's forward relations since they have different
  depth level than the ones still needed
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)
    #               / \     \   (SCRATCH #2)     (8)                    / \         (SCRATCH #2)     (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)                         |
    #              | \ |    |        |           (9)                   |   |                         (9)
    #             (5) (6)__/________/                                 (5) (6)
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6" and "url" set to "Program 4 [/app/project/4]"
    When I upload this generated project, API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "3", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "1", API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "Program 4 [/app/project/4]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "6" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have a Catrobat backward parent having id "6"
    And the project "2" should have no further Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have a Catrobat backward parent having id "6"
    And the project "4" should have no further Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the project "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "4" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 6 with no parents any more should convert former backward relation (6 -> 4)
  to a forward ancestor relation
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)   (6)    (SCRATCH #2)
    #               \ /                                                 \ / ______________/
    #               (2)_____                                            (2)              /
    #               / \     \   (SCRATCH #2)     (8)                    / \ ____________/            (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)          /              |
    #              | \ |    |        |           (9)                   |              /              (9)
    #             (5) (6)__/________/                                 (5)            /
    #               \ /                                                 \ __________/
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6" and "url" set to ""
    When I upload this generated project, API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "0", API version 2
    And the uploaded project should have no Catrobat forward ancestors except self-relation, API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have a Catrobat forward descendant having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "3" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "4" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "5" and depth "3", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward descendant having id "7" and depth "4", API version 2
    And the uploaded project should have no further Catrobat forward descendants, API version 2
    And the uploaded project should have RemixOf "" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have no Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "6" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no further Catrobat forward descendants

    And the project "4" should not be a remix root
    And the project "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the project "4" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "4" should have a Catrobat forward ancestor having id "6" and depth "2"
    And the project "4" should have no further Catrobat forward ancestors
    And the project "4" should have no Catrobat backward parents
    And the project "4" should have no Scratch parents
    And the project "4" should have no Catrobat forward descendants except self-relation

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "6" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no further Catrobat forward descendants

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: multiple consecutive reuploads (example #1)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)   (6)    (SCRATCH #2)
    #               \ /                                                 \ / ______________/
    #               (2)_____                                            (2)              /
    #               / \     \   (SCRATCH #2)     (8)                    / \             /            (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)          /              |
    #              | \ |    |        |           (9)                   |              /              (9)
    #             (5) (6)__/________/                                 (5)            /
    #               \ /                                                 \ __________/
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6" and "url" set to ""
    When I upload this generated project, API version 2
    And I upload another project with name set to "program 4" and url set to "Program 2[/app/project/2]", API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "6" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have no Catrobat forward descendants except self-relation, API version 2
    And the uploaded project should have RemixOf "Program 2[/app/project/2]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have no Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "6" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "6" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have no Catrobat forward ancestors except self-relation
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have no Scratch parents
    And the project "6" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "6" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "6" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "6" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "6" should have no further Catrobat forward descendants
    And the project "6" should have RemixOf "" in the xml

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: multiple consecutive reuploads (example #2)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)   (6)    (SCRATCH #2)
    #               \ /                                                 \ / ______________/
    #               (2)_____                                            (2)              /
    #               / \     \   (SCRATCH #2)     (8)                    /               /            (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)          /              |
    #              | \ |    |        |           (9)                   |              /              (9)
    #             (5) (6)__/________/                                 (5)            /
    #               \ /                                                 \ __________/
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6" and "url" set to ""
    When I upload this generated project, API version 2
    And I upload another project with name set to "program 4" and url set to "", API version 2
    Then the uploaded project should be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "0", API version 2
    And the uploaded project should have no Catrobat forward ancestors except self-relation, API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have no Catrobat forward descendants except self-relation, API version 2
    And the uploaded project should have RemixOf "" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have no Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have a Catrobat forward ancestor having id "6" and depth "2"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have a Catrobat forward ancestor having id "6" and depth "3"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have no Catrobat forward ancestors except self-relation
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have no Scratch parents
    And the project "6" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "6" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "6" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "6" should have no further Catrobat forward descendants
    And the project "6" should have RemixOf "" in the xml

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "4"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation

  Scenario: multiple consecutive reuploads (example #3)
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #             (1) (SCRATCH #1)                                    (1) (SCRATCH #1)   (SCRATCH #2)
    #               \ /                                                 \ /
    #               (2)_____                                            (2)
    #               / \     \   (SCRATCH #2)     (8)                    / \                          (8)
    #             (3) (4)   |        |            |        ==>        (3) (4)                         |
    #              | \ |    |        |           (9)                   |                             (9)
    #             (5) (6)__/________/                                 (5) (6)
    #               \ /                                                 \ /
    #               (7)                                                 (7)
    #
    #-------------------------------------------------------------------------------------------------------------------
    # NOTE: In this graph 4 is a forward parent of 6, but in this case 6 is also a backward parent of 4
    #       (not drawn in this graph).
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a project with "name" set to "program 6" and "url" set to ""
    When I upload this generated project, API version 2
    And I upload another project with name set to "program 2", url set to "Program 1[/app/project/1],Music Inventor [https://scratch.mit.edu/projects/29495624]" and catrobatLanguageVersion set to "0.993", API version 2
    And I upload another project with name set to "program 4" and url set to "Program 2[/app/project/2]", API version 2
    Then the uploaded project should not be a remix root, API version 2
    And the uploaded project should have remix migration date NOT NULL, API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "4" and depth "0", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "2" and depth "1", API version 2
    And the uploaded project should have a Catrobat forward ancestor having id "1" and depth "2", API version 2
    And the uploaded project should have no further Catrobat forward ancestors, API version 2
    And the uploaded project should have no Catrobat backward parents, API version 2
    And the uploaded project should have no Scratch parents, API version 2
    And the uploaded project should have no Catrobat forward descendants except self-relation, API version 2
    And the uploaded project should have RemixOf "Program 2[/app/project/2]" in the xml, API version 2

    And the project "1" should be a remix root
    And the project "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the project "1" should have no further Catrobat forward ancestors
    And the project "1" should have no Catrobat backward parents
    And the project "1" should have no Scratch parents
    And the project "1" should have a Catrobat forward descendant having id "2" and depth "1"
    And the project "1" should have a Catrobat forward descendant having id "3" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "4" and depth "2"
    And the project "1" should have a Catrobat forward descendant having id "5" and depth "3"
    And the project "1" should have a Catrobat forward descendant having id "7" and depth "4"
    And the project "1" should have no further Catrobat forward descendants

    And the project "2" should not be a remix root
    And the project "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the project "2" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the project "2" should have no further Catrobat forward ancestors
    And the project "2" should have no Catrobat backward parents
    And the project "2" should have a Scratch parent having id "29495624"
    And the project "2" should have no further Scratch parents
    And the project "2" should have a Catrobat forward descendant having id "3" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the project "2" should have a Catrobat forward descendant having id "5" and depth "2"
    And the project "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the project "2" should have no further Catrobat forward descendants
    And the project "2" should have RemixOf "Program 1[/app/project/1],Music Inventor [https://scratch.mit.edu/projects/29495624]" in the xml

    And the project "3" should not be a remix root
    And the project "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the project "3" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the project "3" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the project "3" should have no further Catrobat forward ancestors
    And the project "3" should have no Catrobat backward parents
    And the project "3" should have no Scratch parents
    And the project "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the project "3" should have a Catrobat forward descendant having id "7" and depth "2"
    And the project "3" should have no further Catrobat forward descendants

    And the project "5" should not be a remix root
    And the project "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the project "5" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the project "5" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the project "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the project "5" should have no further Catrobat forward ancestors
    And the project "5" should have no Catrobat backward parents
    And the project "5" should have no Scratch parents
    And the project "5" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "5" should have no further Catrobat forward descendants

    And the project "6" should be a remix root
    And the project "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the project "6" should have no Catrobat forward ancestors except self-relation
    And the project "6" should have no Catrobat backward parents
    And the project "6" should have no Scratch parents
    And the project "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the project "6" should have no further Catrobat forward descendants
    And the project "6" should have RemixOf "" in the xml

    And the project "7" should not be a remix root
    And the project "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the project "7" should have a Catrobat forward ancestor having id "1" and depth "4"
    And the project "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the project "7" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the project "7" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the project "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the project "7" should have no further Catrobat forward ancestors
    And the project "7" should have no Catrobat backward parents
    And the project "7" should have no Scratch parents
    And the project "7" should have no Catrobat forward descendants except self-relation

    And the project "8" should be a remix root
    And the project "8" should have a Catrobat forward ancestor having id "8" and depth "0"
    And the project "8" should have no Catrobat forward ancestors except self-relation
    And the project "8" should have no Catrobat backward parents
    And the project "8" should have no Scratch parents
    And the project "8" should have a Catrobat forward descendant having id "9" and depth "1"
    And the project "8" should have no further Catrobat forward descendants

    And the project "9" should not be a remix root
    And the project "9" should have a Catrobat forward ancestor having id "9" and depth "0"
    And the project "9" should have a Catrobat forward ancestor having id "8" and depth "1"
    And the project "9" should have no further Catrobat forward ancestors
    And the project "9" should have no Catrobat backward parents
    And the project "9" should have no Scratch parents
    And the project "9" should have no Catrobat forward descendants except self-relation
