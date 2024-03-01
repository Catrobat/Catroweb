@api @upload @remixes @reupload
Feature: Upload a remixed program with multiple parents should also work with the new UUIDs

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 123456   | cccccccccc | 1  |

    And there are projects:
      | id                                   | name      | description | owned by | downloads | views | upload time      | version | remix_root |
      | 390a46b7-4dca-11ea-b467-08002765cf2c | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 38389632-4dca-11ea-b467-08002765cf2c | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | true       |
      | 35d70287-4dca-11ea-b467-08002765cf2c | program 3 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | true       |
      | 379dc210-4dca-11ea-b467-08002765cf2c | program 4 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | false      |
      | 110a46b7-4dca-11ea-b467-08002765cf2c | program 5 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false      |
      | 220a46b7-4dca-11ea-b467-08002765cf2c | program 6 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |
      | 330a46b7-4dca-11ea-b467-08002765cf2c | program 7 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | false      |

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
      | ancestor_id                          | descendant_id                        | depth |
      | 390a46b7-4dca-11ea-b467-08002765cf2c | 390a46b7-4dca-11ea-b467-08002765cf2c | 0     |
      | 390a46b7-4dca-11ea-b467-08002765cf2c | 379dc210-4dca-11ea-b467-08002765cf2c | 1     |
      | 390a46b7-4dca-11ea-b467-08002765cf2c | 220a46b7-4dca-11ea-b467-08002765cf2c | 2     |
      | 390a46b7-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 3     |
      | 38389632-4dca-11ea-b467-08002765cf2c | 38389632-4dca-11ea-b467-08002765cf2c | 0     |
      | 38389632-4dca-11ea-b467-08002765cf2c | 379dc210-4dca-11ea-b467-08002765cf2c | 1     |
      | 38389632-4dca-11ea-b467-08002765cf2c | 220a46b7-4dca-11ea-b467-08002765cf2c | 2     |
      | 38389632-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 3     |
      | 35d70287-4dca-11ea-b467-08002765cf2c | 35d70287-4dca-11ea-b467-08002765cf2c | 0     |
      | 35d70287-4dca-11ea-b467-08002765cf2c | 110a46b7-4dca-11ea-b467-08002765cf2c | 1     |
      | 35d70287-4dca-11ea-b467-08002765cf2c | 220a46b7-4dca-11ea-b467-08002765cf2c | 2     |
      | 35d70287-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 3     |
      | 379dc210-4dca-11ea-b467-08002765cf2c | 379dc210-4dca-11ea-b467-08002765cf2c | 0     |
      | 379dc210-4dca-11ea-b467-08002765cf2c | 220a46b7-4dca-11ea-b467-08002765cf2c | 1     |
      | 379dc210-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 2     |
      | 110a46b7-4dca-11ea-b467-08002765cf2c | 110a46b7-4dca-11ea-b467-08002765cf2c | 0     |
      | 110a46b7-4dca-11ea-b467-08002765cf2c | 220a46b7-4dca-11ea-b467-08002765cf2c | 1     |
      | 110a46b7-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 2     |
      | 220a46b7-4dca-11ea-b467-08002765cf2c | 220a46b7-4dca-11ea-b467-08002765cf2c | 0     |
      | 220a46b7-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 1     |
      | 330a46b7-4dca-11ea-b467-08002765cf2c | 330a46b7-4dca-11ea-b467-08002765cf2c | 0     |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id                    |
      | 29495624          | 110a46b7-4dca-11ea-b467-08002765cf2c |
      | 29495624          | 330a46b7-4dca-11ea-b467-08002765cf2c |

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
    Given I have a project with "name" set to "program 4" and "url" set to "program 390a46b7-4dca-11ea-b467-08002765cf2c[/pocketalice/project/390a46b7-4dca-11ea-b467-08002765cf2c]"
    When I upload a generated project, API version 1
    Then the uploaded project should not be a remix root, API version 1
    And the uploaded project should have remix migration date NOT NULL, API version 1
    And the uploaded project should have a Catrobat forward ancestor having id "379dc210-4dca-11ea-b467-08002765cf2c" and depth "0", API version 1
    And the uploaded project should have a Catrobat forward ancestor having id "390a46b7-4dca-11ea-b467-08002765cf2c" and depth "1", API version 1
    And the uploaded project should have no further Catrobat forward ancestors, API version 1
    And the uploaded project should have no Catrobat backward parents, API version 1
    And the uploaded project should have no Scratch parents, API version 1
    And the uploaded project should have a Catrobat forward descendant having id "220a46b7-4dca-11ea-b467-08002765cf2c" and depth "1", API version 1
    And the uploaded project should have a Catrobat forward descendant having id "330a46b7-4dca-11ea-b467-08002765cf2c" and depth "2", API version 1
    And the uploaded project should have no further Catrobat forward descendants, API version 1
    And the uploaded project should have RemixOf "program 390a46b7-4dca-11ea-b467-08002765cf2c[/pocketalice/project/390a46b7-4dca-11ea-b467-08002765cf2c]" in the xml, API version 1

    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should be a remix root
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "390a46b7-4dca-11ea-b467-08002765cf2c" and depth "0"
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have no Catrobat ancestors except self-relation
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have no Catrobat backward parents
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have no Scratch parents
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "379dc210-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "220a46b7-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "330a46b7-4dca-11ea-b467-08002765cf2c" and depth "3"
    And the project "390a46b7-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward descendants

    And the project "38389632-4dca-11ea-b467-08002765cf2c" should be a remix root
    And the project "38389632-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "38389632-4dca-11ea-b467-08002765cf2c" and depth "0"
    And the project "38389632-4dca-11ea-b467-08002765cf2c" should have no Catrobat ancestors except self-relation
    And the project "38389632-4dca-11ea-b467-08002765cf2c" should have no Catrobat backward parents
    And the project "38389632-4dca-11ea-b467-08002765cf2c" should have no Scratch parents
    And the project "38389632-4dca-11ea-b467-08002765cf2c" should have no Catrobat forward descendants except self-relation

    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should be a remix root
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "35d70287-4dca-11ea-b467-08002765cf2c" and depth "0"
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have no Catrobat ancestors except self-relation
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have no Catrobat backward parents
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have no Scratch parents
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "110a46b7-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "220a46b7-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "330a46b7-4dca-11ea-b467-08002765cf2c" and depth "3"
    And the project "35d70287-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward descendants

    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should not be a remix root
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "110a46b7-4dca-11ea-b467-08002765cf2c" and depth "0"
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "35d70287-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward ancestors
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have no Catrobat backward parents
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have a Scratch parent having id "29495624"
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have no further Scratch parents
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "220a46b7-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "330a46b7-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "110a46b7-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward descendants

    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should not be a remix root
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "220a46b7-4dca-11ea-b467-08002765cf2c" and depth "0"
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "379dc210-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "110a46b7-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "390a46b7-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "35d70287-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward ancestors
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have no Catrobat backward parents
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have no Scratch parents
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward descendant having id "330a46b7-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "220a46b7-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward descendants

    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should not be a remix root
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "330a46b7-4dca-11ea-b467-08002765cf2c" and depth "0"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "220a46b7-4dca-11ea-b467-08002765cf2c" and depth "1"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "379dc210-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "110a46b7-4dca-11ea-b467-08002765cf2c" and depth "2"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "390a46b7-4dca-11ea-b467-08002765cf2c" and depth "3"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Catrobat forward ancestor having id "35d70287-4dca-11ea-b467-08002765cf2c" and depth "3"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have no further Catrobat forward ancestors
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have no Catrobat backward parents
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have a Scratch parent having id "29495624"
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have no further Scratch parents
    And the project "330a46b7-4dca-11ea-b467-08002765cf2c" should have no Catrobat forward descendants except self-relation

