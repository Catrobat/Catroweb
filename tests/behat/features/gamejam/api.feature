Feature:

  Scenario:
    Given There is an ongoing game jam
    And There are follwing gamejam programs:
      | Name        | Submitted | Accepted |
      | First Entry | yes       | yes      |
      | Unfinished  | yes       | no       |
      | Other       | no        | no       |
    When I GET "/pocketalice/api/gamejam/submissions.json"
    Then I should receive the following programs:
      | Name        |
      | First Entry |
    And The total number of found projects should be 1

  Scenario:
    Given There is an ongoing game jam
    And I already submitted my game
    And I already filled the google form
    When I GET "/pocketalice/api/gamejam/submissions.json"
    Then I should receive my program

  Scenario:
    Given There are following gamejams:
      | Name  | Starts in | Ends in |
      | Jam B | -8 days   | -2 days |
      | Jam C | 1 day     | 4 days  |
      | Jam A | -4 day    | -1 days |
    And There are follwing gamejam programs:
      | Name        | Submitted | Accepted | GameJam |
      | Entry for A | yes       | yes      | Jam A   |
      | Entry for C | yes       | yes      | Jam C   |
    When I GET "/pocketalice/api/gamejam/submissions.json"
    Then I should receive the following programs:
      | Name        |
      | Entry for C |
    And The total number of found projects should be 1
          