@web @achievements @verified_developer
Feature: Every registered user should have at least one achievement

  Scenario: On registration users should get their first achievement
    Given I run the update achievements command
    And there are users:
      | name    |
      | NewUser |
    And I log in as "NewUser"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Novice"

  Scenario: Old users must get their achievements too
    And there are users:
      | name    |
      | OldUser |
    And I log in as "OldUser"
    And I run the update achievements command
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Novice"
    And I run the add verified_developer_silver user achievements command
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Novice"

  Scenario: Already verified users must get their achievements too
    And there are users:
      | name    |
      | OldUser |
    And I log in as "OldUser"
    And I run the update achievements command
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Verified"
    And I run the add verified_developer_gold user achievements command
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Verified"
