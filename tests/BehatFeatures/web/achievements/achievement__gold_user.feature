@web @achievements @silver_user
Feature: Every registered user should have at least one achievement

  Scenario: +1 year active users should have
    Given I run the update achievements command
    And there are users:
      | name                 |
      | OldUser_5yearsActive |
      | OldUser_3yearsActive |
      | NewUser              |
    And the users are created at:
      | name                 | created_at |
      | OldUser_5yearsActive | -5 years   |
      | OldUser_3yearsActive | -5 years   |
      | NewUser              | -3 years   |
    And there are projects:
      | name         | owned by             | upload time |
      | project u5_1 | OldUser_5yearsActive |             |
      | project u5_2 | OldUser_5yearsActive | -1 years    |
      | project u5_3 | OldUser_5yearsActive | -2 years    |
      | project u5_4 | OldUser_5yearsActive | -3 years    |
      | project u5_5 | OldUser_5yearsActive | -4 years    |
      | project n1   | NewUser              |             |
      | project n2   | NewUser              |             |
      | project n3   | NewUser              |             |
      | project n4   | NewUser              |             |
      | project n4   | NewUser              |             |
      | project u3_1 | OldUser_3yearsActive |             |
      | project u3_2 | OldUser_3yearsActive |             |
      | project u3_3 | OldUser_3yearsActive | -1 years    |
      | project u3_4 | OldUser_3yearsActive | -1 years    |
      | project u3_5 | OldUser_3yearsActive | -4 years    |
    And I run the add gold_user user achievements command

    When I log in as "NewUser"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Master"

    When I log in as "OldUser_3yearsActive"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Master"

    When I log in as "OldUser_5yearsActive"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Master"
