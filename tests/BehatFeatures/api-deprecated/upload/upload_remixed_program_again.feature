@api @upload @remixes @reupload
Feature: Upload a remixed program with multiple parents

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 123456   | cccccccccc | 1  |

    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | remix_root |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | true       |
      | 3  | program 3 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | true       |
      | 4  | program 4 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | false      |
      | 5  | program 5 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 6  | program 6 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 7  | program 7 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |

    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph looks like according to the following forward remix relations (closure table):
    #
    #                  (1) (2)  (3)    (Scratch)
    #                    \ /    /         /
    #                    (4)  (5)________/
    #                      \  /         /
    #                      (6)         /
    #                       |         /
    #                      (7)_______/
    #
    #-------------------------------------------------------------------------------------------------------------------

    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 4             | 1     |
      | 1           | 6             | 2     |
      | 1           | 7             | 3     |
      | 2           | 2             | 0     |
      | 2           | 4             | 1     |
      | 2           | 6             | 2     |
      | 2           | 7             | 3     |
      | 3           | 3             | 0     |
      | 3           | 5             | 1     |
      | 3           | 6             | 2     |
      | 3           | 7             | 3     |
      | 4           | 4             | 0     |
      | 4           | 6             | 1     |
      | 4           | 7             | 2     |
      | 5           | 5             | 0     |
      | 5           | 6             | 1     |
      | 5           | 7             | 2     |
      | 6           | 6             | 0     |
      | 6           | 7             | 1     |
      | 7           | 7             | 0     |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 29495624          | 5                 |
      | 29495624          | 7                 |

  Scenario: reuploading program 4 with only one parent unlinks former parent
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #                  (1) (2)  (3)    (Scratch)                    (1) (2)  (3)    (Scratch)
    #                    \ /    /         /                           \      /         /
    #                    (4)  (5)________/                            (4)  (5)________/
    #                      \  /         /               =>              \  /         /
    #                      (6)         /                                (6)         /
    #                       |         /                                  |         /
    #                      (7)_______/                                  (7)_______/
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a program with "name" set to "program 4" and "url" set to "program 1[/pocketalice/project/1]"
    When I upload a generated program, API version 1
    Then the uploaded program should not be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "4" and depth "0", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "1", API version 1
    And the uploaded program should have no further Catrobat forward ancestors, API version 1
    And the uploaded program should have no Catrobat backward parents, API version 1
    And the uploaded program should have no Scratch parents, API version 1
    And the uploaded program should have a Catrobat forward descendant having id "6" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "7" and depth "2", API version 1
    And the uploaded program should have no further Catrobat forward descendants, API version 1
    And the uploaded program should have RemixOf "program 1[/pocketalice/project/1]" in the xml, API version 1

    And the program "1" should be a remix root
    And the program "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the program "1" should have no Catrobat ancestors except self-relation
    And the program "1" should have no Catrobat backward parents
    And the program "1" should have no Scratch parents
    And the program "1" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "1" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "1" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "1" should have no further Catrobat forward descendants

    And the program "2" should be a remix root
    And the program "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the program "2" should have no Catrobat ancestors except self-relation
    And the program "2" should have no Catrobat backward parents
    And the program "2" should have no Scratch parents
    And the program "2" should have no Catrobat forward descendants except self-relation

    And the program "3" should be a remix root
    And the program "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the program "3" should have no Catrobat ancestors except self-relation
    And the program "3" should have no Catrobat backward parents
    And the program "3" should have no Scratch parents
    And the program "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "3" should have no further Catrobat forward descendants

    And the program "5" should not be a remix root
    And the program "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the program "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the program "5" should have no further Catrobat forward ancestors
    And the program "5" should have no Catrobat backward parents
    And the program "5" should have a Scratch parent having id "29495624"
    And the program "5" should have no further Scratch parents
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "5" should have no further Catrobat forward descendants

    And the program "6" should not be a remix root
    And the program "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the program "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the program "6" should have no further Catrobat forward ancestors
    And the program "6" should have no Catrobat backward parents
    And the program "6" should have no Scratch parents
    And the program "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the program "6" should have no further Catrobat forward descendants

    And the program "7" should not be a remix root
    And the program "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the program "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the program "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "7" should have no further Catrobat forward ancestors
    And the program "7" should have no Catrobat backward parents
    And the program "7" should have a Scratch parent having id "29495624"
    And the program "7" should have no further Scratch parents
    And the program "7" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 6 with only one parent unlinks former parent
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #                  (1) (2)  (3)    (Scratch)                    (1) (2)  (3)    (Scratch)
    #                    \ /    /         /                           \ /    /         /
    #                    (4)  (5)________/                            (4)  (5)________/
    #                      \  /         /               =>                 /         /
    #                      (6)         /                                (6)         /
    #                       |         /                                  |         /
    #                      (7)_______/                                  (7)_______/
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a program with "name" set to "program 6" and "url" set to "program 5[/pocketalice/project/5]"
    When I upload a generated program, API version 1
    Then the uploaded program should not be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "6" and depth "0", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "5" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "3" and depth "2", API version 1
    And the uploaded program should have no further Catrobat forward ancestors, API version 1
    And the uploaded program should have no Catrobat backward parents, API version 1
    And the uploaded program should have no Scratch parents, API version 1
    And the uploaded program should have a Catrobat forward descendant having id "7" and depth "1", API version 1
    And the uploaded program should have no further Catrobat forward descendants, API version 1
    And the uploaded program should have RemixOf "program 5[/pocketalice/project/5]" in the xml, API version 1

    And the program "1" should be a remix root
    And the program "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the program "1" should have no Catrobat ancestors except self-relation
    And the program "1" should have no Catrobat backward parents
    And the program "1" should have no Scratch parents
    And the program "1" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "1" should have no further Catrobat forward descendants

    And the program "2" should be a remix root
    And the program "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the program "2" should have no Catrobat ancestors except self-relation
    And the program "2" should have no Catrobat backward parents
    And the program "2" should have no Scratch parents
    And the program "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "2" should have no further Catrobat forward descendants

    And the program "3" should be a remix root
    And the program "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the program "3" should have no Catrobat ancestors except self-relation
    And the program "3" should have no Catrobat backward parents
    And the program "3" should have no Scratch parents
    And the program "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "3" should have no further Catrobat forward descendants

    And the program "4" should not be a remix root
    And the program "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the program "4" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the program "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the program "4" should have no further Catrobat forward ancestors
    And the program "4" should have no Catrobat backward parents
    And the program "4" should have no Scratch parents
    And the program "4" should have no Catrobat forward descendants except self-relation

    And the program "5" should not be a remix root
    And the program "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the program "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the program "5" should have no further Catrobat forward ancestors
    And the program "5" should have no Catrobat backward parents
    And the program "5" should have a Scratch parent having id "29495624"
    And the program "5" should have no further Scratch parents
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "5" should have no further Catrobat forward descendants

    And the program "7" should not be a remix root
    And the program "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the program "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "7" should have no further Catrobat forward ancestors
    And the program "7" should have no Catrobat backward parents
    And the program "7" should have a Scratch parent having id "29495624"
    And the program "7" should have no further Scratch parents
    And the program "7" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading program 6 with additional backward parent creates backward relation and unlinks former parent
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #                  (1) (2)  (3)    (Scratch)                    (1) (2)  (3)    (Scratch)
    #                    \ /    /         /                           \ /    /         /
    #                    (4)  (5)________/                            (4)  (5)________/
    #                      \  /         /               =>                 /         /
    #                      (6)         /                                (6)         /
    #                       |         /                                  |         /
    #                      (7)_______/                                  (7)_______/
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: program 7 becomes a backward parent of program 6 as well!
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a program with "name" set to "program 6", "url" set to "program 6[/app/project/6], Merge 1[program 5[/pocketalice/project/5], program 7[/pocketalice/project/7]]" and "catrobatLanguageVersion" set to "0.993"
    When I upload a generated program, API version 1
    Then the uploaded program should not be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "6" and depth "0", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "5" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "3" and depth "2", API version 1
    And the uploaded program should have no further Catrobat forward ancestors, API version 1
    And the uploaded program should have a Catrobat backward parent having id "7", API version 1
    And the uploaded program should have no further Catrobat backward parents, API version 1
    And the uploaded program should have no Scratch parents, API version 1
    And the uploaded program should have a Catrobat forward descendant having id "7" and depth "1", API version 1
    And the uploaded program should have no further Catrobat forward descendants, API version 1
    And the uploaded program should have RemixOf "program 6[/app/project/6], Merge 1[program 5[/pocketalice/project/5], program 7[/pocketalice/project/7]]" in the xml, API version 1

    And the program "1" should be a remix root
    And the program "1" should have a Catrobat forward ancestor having id "1" and depth "0"
    And the program "1" should have no Catrobat ancestors except self-relation
    And the program "1" should have no Catrobat backward parents
    And the program "1" should have no Scratch parents
    And the program "1" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "1" should have no further Catrobat forward descendants

    And the program "2" should be a remix root
    And the program "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the program "2" should have no Catrobat ancestors except self-relation
    And the program "2" should have no Catrobat backward parents
    And the program "2" should have no Scratch parents
    And the program "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "2" should have no further Catrobat forward descendants

    And the program "3" should be a remix root
    And the program "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the program "3" should have no Catrobat ancestors except self-relation
    And the program "3" should have no Catrobat backward parents
    And the program "3" should have no Scratch parents
    And the program "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "3" should have no further Catrobat forward descendants

    And the program "4" should not be a remix root
    And the program "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the program "4" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the program "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the program "4" should have no further Catrobat forward ancestors
    And the program "4" should have no Catrobat backward parents
    And the program "4" should have no Scratch parents
    And the program "4" should have no Catrobat forward descendants except self-relation

    And the program "5" should not be a remix root
    And the program "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the program "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the program "5" should have no further Catrobat forward ancestors
    And the program "5" should have no Catrobat backward parents
    And the program "5" should have a Scratch parent having id "29495624"
    And the program "5" should have no further Scratch parents
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "5" should have no further Catrobat forward descendants

    And the program "7" should not be a remix root
    And the program "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the program "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "7" should have no further Catrobat forward ancestors
    And the program "7" should have no Catrobat backward parents
    And the program "7" should have a Scratch parent having id "29495624"
    And the program "7" should have no further Scratch parents
    And the program "7" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading former root program 1 with two new Scratch parents and one additional backward parent
  creates backward relation and two new Scratch relations
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #                                                        (Scratch #2)              (Scratch #1)
    #                                                               \  ___________________/
    #                                                               \ /                  /
    #                  (1) (2)  (3)    (Scratch #1)                 (1) (2)  (3)        /
    #                    \ /    /         /                           \ /    /         /
    #                    (4)  (5)________/                            (4)  (5)________/
    #                      \  /         /               =>              \  /         /
    #                      (6)         /                                (6)         /
    #                       |         /                                  |         /
    #                      (7)_______/                                  (7)_______/
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: program 6 becomes a backward parent of program 1 as well!
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a program with "name" set to "program 1", "url" set to "Test Scratch [https://scratch.mit.edu/projects/1], Merge1[program 6[/app/project/6], Music Inventor [https://scratch.mit.edu/projects/29495624]]" and "catrobatLanguageVersion" set to "0.993"
    When I upload a generated program, API version 1
    Then the uploaded program should be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "0", API version 1
    And the uploaded program should have no Catrobat forward ancestors except self-relation, API version 1
    And the uploaded program should have a Catrobat backward parent having id "6", API version 1
    And the uploaded program should have no further Catrobat backward parents, API version 1
    And the uploaded program should have a Scratch parent having id "29495624", API version 1
    And the uploaded program should have a Scratch parent having id "1", API version 1
    And the uploaded program should have no further Scratch parents, API version 1
    And the uploaded program should have a Catrobat forward descendant having id "4" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "6" and depth "2", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "7" and depth "3", API version 1
    And the uploaded program should have no further Catrobat forward descendants, API version 1
    And the uploaded program should have RemixOf "Test Scratch [https://scratch.mit.edu/projects/1], Merge1[program 6[/app/project/6], Music Inventor [https://scratch.mit.edu/projects/29495624]]" in the xml, API version 1

    And the program "2" should be a remix root
    And the program "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the program "2" should have no Catrobat ancestors except self-relation
    And the program "2" should have no Catrobat backward parents
    And the program "2" should have no Scratch parents
    And the program "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "2" should have no further Catrobat forward descendants

    And the program "3" should be a remix root
    And the program "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the program "3" should have no Catrobat ancestors except self-relation
    And the program "3" should have no Catrobat backward parents
    And the program "3" should have no Scratch parents
    And the program "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "3" should have no further Catrobat forward descendants

    And the program "4" should not be a remix root
    And the program "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the program "4" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the program "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the program "4" should have no further Catrobat forward ancestors
    And the program "4" should have no Catrobat backward parents
    And the program "4" should have no Scratch parents
    And the program "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "4" should have no further Catrobat forward descendants

    And the program "5" should not be a remix root
    And the program "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the program "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the program "5" should have no further Catrobat forward ancestors
    And the program "5" should have no Catrobat backward parents
    And the program "5" should have a Scratch parent having id "29495624"
    And the program "5" should have no further Scratch parents
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "5" should have no further Catrobat forward descendants

    And the program "6" should not be a remix root
    And the program "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the program "6" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the program "6" should have no further Catrobat forward ancestors
    And the program "6" should have no Catrobat backward parents
    And the program "6" should have no Scratch parents
    And the program "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the program "6" should have no further Catrobat forward descendants

    And the program "7" should not be a remix root
    And the program "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the program "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the program "7" should have no Catrobat backward parents
    And the program "7" should have no further Catrobat forward ancestors
    And the program "7" should have a Scratch parent having id "29495624"
    And the program "7" should have no further Scratch parents
    And the program "7" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading former root program 1 with one new Scratch parent and one additional backward parent
  creates backward relation and new Scratch relation
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #                                                                                  (Scratch)
    #                                                                  ___________________/
    #                                                                 /                  /
    #                  (1) (2)  (3)    (Scratch)                    (1) (2)  (3)        /
    #                    \ /    /         /                           \ /    /         /
    #                    (4)  (5)________/                            (4)  (5)________/
    #                      \  /         /               =>              \  /         /
    #                      (6)         /                                (6)         /
    #                       |         /                                  |         /
    #                      (7)_______/                                  (7)_______/
    #
    #-------------------------------------------------------------------------------------------------------------------
    #  Expected result after upload: program 7 becomes a backward parent of program 1 as well!
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a program with "name" set to "program 1", "url" set to "program 7[/app/project/7], Music Inventor [https://scratch.mit.edu/projects/29495624]" and "catrobatLanguageVersion" set to "0.993"
    When I upload a generated program, API version 1
    Then the uploaded program should be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "0", API version 1
    And the uploaded program should have no Catrobat forward ancestors except self-relation, API version 1
    And the uploaded program should have a Catrobat backward parent having id "7", API version 1
    And the uploaded program should have no further Catrobat backward parents, API version 1
    And the uploaded program should have a Scratch parent having id "29495624", API version 1
    And the uploaded program should have no further Scratch parents, API version 1
    And the uploaded program should have a Catrobat forward descendant having id "4" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "6" and depth "2", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "7" and depth "3", API version 1
    And the uploaded program should have no further Catrobat forward descendants, API version 1
    And the uploaded program should have RemixOf "program 7[/app/project/7], Music Inventor [https://scratch.mit.edu/projects/29495624]" in the xml, API version 1

    And the program "2" should be a remix root
    And the program "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the program "2" should have no Catrobat ancestors except self-relation
    And the program "2" should have no Catrobat backward parents
    And the program "2" should have no Scratch parents
    And the program "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "2" should have no further Catrobat forward descendants

    And the program "3" should be a remix root
    And the program "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the program "3" should have no Catrobat ancestors except self-relation
    And the program "3" should have no Catrobat backward parents
    And the program "3" should have no Scratch parents
    And the program "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "3" should have no further Catrobat forward descendants

    And the program "4" should not be a remix root
    And the program "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the program "4" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the program "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the program "4" should have no further Catrobat forward ancestors
    And the program "4" should have no Catrobat backward parents
    And the program "4" should have no Scratch parents
    And the program "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "4" should have no further Catrobat forward descendants

    And the program "5" should not be a remix root
    And the program "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the program "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the program "5" should have no further Catrobat forward ancestors
    And the program "5" should have no Catrobat backward parents
    And the program "5" should have a Scratch parent having id "29495624"
    And the program "5" should have no further Scratch parents
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "5" should have no further Catrobat forward descendants

    And the program "6" should not be a remix root
    And the program "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the program "6" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the program "6" should have no further Catrobat forward ancestors
    And the program "6" should have no Catrobat backward parents
    And the program "6" should have no Scratch parents
    And the program "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the program "6" should have no further Catrobat forward descendants

    And the program "7" should not be a remix root
    And the program "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the program "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the program "7" should have no Catrobat backward parents
    And the program "7" should have no further Catrobat forward ancestors
    And the program "7" should have a Scratch parent having id "29495624"
    And the program "7" should have no further Scratch parents
    And the program "7" should have no Catrobat forward descendants except self-relation

  Scenario: reuploading former root program 1 with program 5 as forward parent appends program 1 accordingly and
  inherits all ancestors of program 5 to program 1
    #-------------------------------------------------------------------------------------------------------------------
    # this is how the remix graph should look like after the program is uploaded:
    #
    #                                                                      (3) (Scratch)
    #                                                                       \ /     \
    #                                                                       (5)     |
    #                                                                       /  \    |
    #                  (1) (2)  (3)    (Scratch)                     (2)  (1)  |    |
    #                    \ /    /         /                            \  /   /     |
    #                    (4)  (5)________/                             (4)   /      |
    #                      \  /         /               =>               \  /      /
    #                      (6)         /                                 (6)      /
    #                       |         /                                   |      /
    #                      (7)_______/                                   (7)____/
    #
    #-------------------------------------------------------------------------------------------------------------------
    Given I am "Catrobat"
    Given I have a program with "name" set to "program 1" and "url" set to "program 5[/app/project/5]"
    When I upload a generated program, API version 1
    Then the uploaded program should not be a remix root, API version 1
    And the uploaded program should have remix migration date NOT NULL, API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "1" and depth "0", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "5" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward ancestor having id "3" and depth "2", API version 1
    And the uploaded program should have no further Catrobat forward ancestors, API version 1
    And the uploaded program should have no Catrobat backward parents, API version 1
    And the uploaded program should have no Scratch parents, API version 1
    And the uploaded program should have a Catrobat forward descendant having id "4" and depth "1", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "6" and depth "2", API version 1
    And the uploaded program should have a Catrobat forward descendant having id "7" and depth "3", API version 1
    And the uploaded program should have no further Catrobat forward descendants, API version 1
    And the uploaded program should have RemixOf "program 5[/app/project/5]" in the xml, API version 1

    And the program "2" should be a remix root
    And the program "2" should have a Catrobat forward ancestor having id "2" and depth "0"
    And the program "2" should have no Catrobat ancestors except self-relation
    And the program "2" should have no Catrobat backward parents
    And the program "2" should have no Scratch parents
    And the program "2" should have a Catrobat forward descendant having id "4" and depth "1"
    And the program "2" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "2" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "2" should have no further Catrobat forward descendants

    And the program "3" should be a remix root
    And the program "3" should have a Catrobat forward ancestor having id "3" and depth "0"
    And the program "3" should have no Catrobat ancestors except self-relation
    And the program "3" should have no Catrobat backward parents
    And the program "3" should have no Scratch parents
    And the program "3" should have a Catrobat forward descendant having id "1" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "4" and depth "3"
    And the program "3" should have a Catrobat forward descendant having id "5" and depth "1"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "2"
    And the program "3" should have a Catrobat forward descendant having id "6" and depth "4"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "3"
    And the program "3" should have a Catrobat forward descendant having id "7" and depth "5"
    And the program "3" should have no further Catrobat forward descendants

    And the program "4" should not be a remix root
    And the program "4" should have a Catrobat forward ancestor having id "4" and depth "0"
    And the program "4" should have a Catrobat forward ancestor having id "1" and depth "1"
    And the program "4" should have a Catrobat forward ancestor having id "2" and depth "1"
    And the program "4" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "4" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "4" should have no further Catrobat forward ancestors
    And the program "4" should have no Catrobat backward parents
    And the program "4" should have no Scratch parents
    And the program "4" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "4" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "4" should have no further Catrobat forward descendants

    And the program "5" should not be a remix root
    And the program "5" should have a Catrobat forward ancestor having id "5" and depth "0"
    And the program "5" should have a Catrobat forward ancestor having id "3" and depth "1"
    And the program "5" should have no further Catrobat forward ancestors
    And the program "5" should have no Catrobat backward parents
    And the program "5" should have a Scratch parent having id "29495624"
    And the program "5" should have no further Scratch parents
    And the program "5" should have a Catrobat forward descendant having id "1" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "4" and depth "2"
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "1"
    And the program "5" should have a Catrobat forward descendant having id "6" and depth "3"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "2"
    And the program "5" should have a Catrobat forward descendant having id "7" and depth "4"
    And the program "5" should have no further Catrobat forward descendants

    And the program "6" should not be a remix root
    And the program "6" should have a Catrobat forward ancestor having id "6" and depth "0"
    And the program "6" should have a Catrobat forward ancestor having id "5" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "5" and depth "3"
    And the program "6" should have a Catrobat forward ancestor having id "4" and depth "1"
    And the program "6" should have a Catrobat forward ancestor having id "3" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "3" and depth "4"
    And the program "6" should have a Catrobat forward ancestor having id "2" and depth "2"
    And the program "6" should have a Catrobat forward ancestor having id "1" and depth "2"
    And the program "6" should have no further Catrobat forward ancestors
    And the program "6" should have no Catrobat backward parents
    And the program "6" should have no Scratch parents
    And the program "6" should have a Catrobat forward descendant having id "7" and depth "1"
    And the program "6" should have no further Catrobat forward descendants

    And the program "7" should not be a remix root
    And the program "7" should have a Catrobat forward ancestor having id "7" and depth "0"
    And the program "7" should have a Catrobat forward ancestor having id "6" and depth "1"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "5" and depth "4"
    And the program "7" should have a Catrobat forward ancestor having id "4" and depth "2"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "3" and depth "5"
    And the program "7" should have a Catrobat forward ancestor having id "2" and depth "3"
    And the program "7" should have a Catrobat forward ancestor having id "1" and depth "3"
    And the program "7" should have no Catrobat backward parents
    And the program "7" should have no further Catrobat forward ancestors
    And the program "7" should have a Scratch parent having id "29495624"
    And the program "7" should have no further Scratch parents
    And the program "7" should have no Catrobat forward descendants except self-relation
