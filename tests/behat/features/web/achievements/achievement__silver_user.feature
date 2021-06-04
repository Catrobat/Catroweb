@web @achievements @silver_user
Feature: Every registered user should have at least one achievement

  Scenario: +1 year active users should have
    Given I run the update achievements command
    And there are users:
      | name           |
      | OldUser_with_1 |
      | OldUser_with_0 |
      | NewUser        |
    And the users are created at:
      | name           | created_at |
      | OldUser_with_1 | -2 years   |
      | OldUser_with_0 | -3 years   |
      | NewUser        | -120 days  |
    And there are projects:
      | name      | owned by       |
      | project 1 | OldUser_with_1 |
      | project 2 | NewUser        |
    And I run the add silver_user user achievements command

    When I log in as "NewUser"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Artisan"

    When I log in as "OldUser_with_0"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Artisan"

    When I log in as "OldUser_with_1"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Artisan"
