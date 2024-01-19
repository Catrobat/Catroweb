@web @achievements @bronze_user
Feature: Every registered user should have at least one achievement

  Scenario: Project upload should trigger the bronze_user achievement if there exist followings
    Given I run the update achievements command
    And there are users:
      | name     |
      | Catrobat |
      | User1    |
    And there are followers:
      | name     | following |
      | Catrobat | User1     |
    And I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Apprentice"
    And there are projects:
      | id        | name      | owned by |
      | isxs-adkt | project_1 | Catrobat |
    And I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Apprentice"

  Scenario: Project upload should trigger the bronze_user achievement if there exist followings
    Given I run the update achievements command
    And there are users:
      | name     |
      | Catrobat |
      | User1    |
    And there are projects:
      | id        | name      | owned by |
      | isxs-adkt | project_1 | Catrobat |
    And I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Apprentice"
    When there are followers:
      | name     | following |
      | Catrobat | User1     |
    And I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Apprentice"

  Scenario: Old users must get their achievements too
    Given there are users:
      | name     |
      | Catrobat |
      | User1    |
      | User2    |
    And there are followers:
      | name     | following |
      | Catrobat | User1     |
      | User2    | User1     |
    And there are projects:
      | id        | name     | owned by |
      | isxs-adkt | Webteam  | Catrobat |
      | tvut-irkw | Catroweb | User1    |
    And I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Apprentice"
    And I wait 500 milliseconds
    When I run the update achievements command
    Then the "#unlocked-achievements" element should not contain "Apprentice"
    And I run the add bronze_user user achievements command
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Apprentice"
    And I log in as "User1"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Apprentice"
    And I log in as "User2"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Apprentice"
