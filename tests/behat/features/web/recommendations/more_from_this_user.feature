# Missing in new API - To be fixed with SHARE-369

@web @recommendations
Feature: There should be a more from this user category on project pages

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
      | 3  | User3    |
      | 4  | User4    |
    And there are projects:
      | id | name       | owned by |
      | 1  | oldestProg | Catrobat |
      | 2  | project 02 | Catrobat |
      | 3  | project 03 | User2    |
      | 4  | project 04 | User2    |
      | 5  | project 05 | User2    |
      | 6  | project 06 | User2    |
      | 7  | project 07 | User2    |
      | 8  | project 08 | User2    |
      | 9  | project 09 | User2    |
      | 10 | project 10 | User2    |
      | 11 | project 11 | User2    |
      | 12 | project 12 | User2    |
      | 13 | project 13 | User2    |
      | 14 | project 14 | User2    |
      | 15 | project 15 | User2    |
      | 16 | project 16 | User2    |
      | 17 | project 17 | User2    |
      | 18 | project 18 | User2    |
      | 19 | project 19 | User2    |
      | 20 | project 20 | User2    |
      | 21 | project 21 | User2    |
      | 22 | project 22 | User2    |
      | 23 | project 23 | User3    |

  Scenario: At a projects detail page I should get more projects from this user recommended
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then the element "#recommended-projects__more_from_user" should be visible
    And I should see 19 "#recommended-projects__more_from_user .project-list__project"
    And I should see "More from User2"
    And I should see "project 03"
    And I should see "project 04" in the "#recommended-projects__more_from_user" element
    And I should see "project 05" in the "#recommended-projects__more_from_user" element
    And I should see "project 06" in the "#recommended-projects__more_from_user" element
    And I should see "project 07" in the "#recommended-projects__more_from_user" element
    And I should see "project 08" in the "#recommended-projects__more_from_user" element
    And I should see "project 09" in the "#recommended-projects__more_from_user" element
    And I should see "project 10" in the "#recommended-projects__more_from_user" element
    And I should see "project 11" in the "#recommended-projects__more_from_user" element
    And I should see "project 12" in the "#recommended-projects__more_from_user" element
    And I should see "project 13" in the "#recommended-projects__more_from_user" element
    And I should see "project 14" in the "#recommended-projects__more_from_user" element
    And I should see "project 15" in the "#recommended-projects__more_from_user" element
    And I should see "project 16" in the "#recommended-projects__more_from_user" element
    And I should see "project 17" in the "#recommended-projects__more_from_user" element
    And I should see "project 18" in the "#recommended-projects__more_from_user" element
    And I should see "project 19" in the "#recommended-projects__more_from_user" element
    And I should see "project 20" in the "#recommended-projects__more_from_user" element
    And I should see "project 21" in the "#recommended-projects__more_from_user" element
    And I should see "project 22" in the "#recommended-projects__more_from_user" element

  Scenario: Show more from a user should not show the same project
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#recommended-projects__more_from_user" should be visible
    And I should see 1 "#recommended-projects__more_from_user .project-list__project"
    And I should see "More from Catrobat"
    And I should not see "oldestProg" in the "#recommended-projects__more_from_user" element
    And I should see "project 02" in the "#recommended-projects__more_from_user" element

  Scenario: User has no other projects and therefore the section should not be visible
    And I am on "/app/project/23"
    And I wait for the page to be loaded
    Then the element "#recommended-projects__more_from_user" should not be visible
    And I should see "User3"
    And I should not see "More from User3"
