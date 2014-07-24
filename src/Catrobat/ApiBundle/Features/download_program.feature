@api
Feature: Download programs

  Background: 
    Given the upload folder is empty
    And the extract folder is empty
    And there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are downloadable programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false   |

  Scenario:
    When i download "/downloads/1.catrobat"
    Then i should receive a file
    And the response code should be "200"
    
  Scenario:
    When i download "/downloads/2.catrobat"
    Then the response code should be "404"
    
  Scenario:
    When i download "/downloads/999.catrobat"
    Then the response code should be "404"
    