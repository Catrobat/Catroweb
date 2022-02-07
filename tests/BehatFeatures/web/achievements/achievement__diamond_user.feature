@web @achievements @silver_user
Feature: Every registered user should have at least one achievement

  Scenario: +1 year active users should have
    Given I run the update achievements command
    And there are users:
      | name                 |
      | OldUser_7yearsActive |
      | OldUser_3yearsActive |
      | NewUser              |
    And the users are created at:
      | name                 | created_at |
      | OldUser_7yearsActive | -8 years   |
      | OldUser_3yearsActive | -8 years   |
      | NewUser              | -3 years   |
    And there are projects:
      | name         | owned by             | upload time |
      | project u7_1 | OldUser_7yearsActive |             |
      | project u7_2 | OldUser_7yearsActive | -1 years    |
      | project u7_3 | OldUser_7yearsActive | -2 years    |
      | project u7_4 | OldUser_7yearsActive | -3 years    |
      | project u7_5 | OldUser_7yearsActive | -4 years    |
      | project u7_6 | OldUser_7yearsActive | -5 years    |
      | project u7_7 | OldUser_7yearsActive | -6 years    |
      | project n1   | NewUser              |             |
      | project n2   | NewUser              |             |
      | project n3   | NewUser              |             |
      | project n4   | NewUser              |             |
      | project n5   | NewUser              |             |
      | project n6   | NewUser              |             |
      | project n7   | NewUser              |             |
      | project n8   | NewUser              |             |
      | project u3_1 | OldUser_3yearsActive |             |
      | project u3_2 | OldUser_3yearsActive |             |
      | project u3_3 | OldUser_3yearsActive | -1 years    |
      | project u3_4 | OldUser_3yearsActive | -1 years    |
      | project u3_5 | OldUser_3yearsActive | -6 years    |
      | project u3_6 | OldUser_3yearsActive | -6 years    |
      | project u3_7 | OldUser_3yearsActive | -6 years    |
    And I run the add diamond_user user achievements command

    When I log in as "NewUser"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Grand Master"

    When I log in as "OldUser_3yearsActive"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Grand Master"

    When I log in as "OldUser_7yearsActive"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Grand Master"
