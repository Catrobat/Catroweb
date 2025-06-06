@web @achievements @coding_jam_09_2021
Feature: Projects uploaded with the tag '#catrobatfestival2021' should reward the owner with an achievement

  Background:
    Given I run the update achievements command
    And I run the update tags command
    And there are users:
      | name                 |
      | Catrobat             |

  Scenario: Projects uploaded with the tag '#catrobatfestival2021' in the description get an achievement
    Given I run the update achievements command
    And I run the update tags command
    And the current time is "26.09.2021 12:00"
    When I log in as "Catrobat"
    Given I have a project with "description" set to "Let's go #catrobatfestival2021"
    And I use the "english" app
    When user "Catrobat" uploads this generated project
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Coding Jam '21"

  Scenario: Projects uploaded with the tag '#catrobatfestival2021' should reward the owner with an achievement
    Given I run the update achievements command
    And I run the update tags command
    And the current time is "26.09.2021 12:00"
    When I log in as "Catrobat"
    Given I have a project with "tags" set to "catrobatfestival2021"
    And I use the "english" app
    When user "Catrobat" uploads this generated project
    Then the project should be tagged with "catrobatfestival2021" in the database
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Coding Jam '21"

  Scenario: Achievement via tag is only available during event
    Given I run the update achievements command
    And I run the update tags command
    And the current time is "28.09.2021 12:00"
    When I log in as "Catrobat"
    Given I have a project with "tags" set to "catrobatfestival2021"
    And I use the "english" app
    When user "Catrobat" uploads this generated project
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Coding Jam '21"

  Scenario: Achievement via description is only available during event
    Given I run the update achievements command
    And I run the update tags command
    And the current time is "28.09.2021 12:00"
    When I log in as "Catrobat"
    Given I have a project with "description" set to "Let's go #catrobatfestival2021"
    And I use the "english" app
    When user "Catrobat" uploads this generated project
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Coding Jam '21"
