@api
Feature: Download programs

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
    And there are downloadable projects:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false   |

  Scenario: Projects can be downloaded
    When I download "/app/download/1.catrobat"
    Then i should receive a project file
    And the response code should be "200"

  Scenario: Invisible projects can't be be downloaded
    When I download "/app/download/2.catrobat"
    Then the response code should be "404"

  Scenario: Projects can't be downloaded if they do not exist
    When I download "/app/download/999.catrobat"
    Then the response code should be "404"
