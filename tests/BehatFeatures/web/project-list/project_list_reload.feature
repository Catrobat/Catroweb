@homepage
Feature: Project list automatically loads more projects on scrolling

  Background:
    Given there are projects:
      | id | name       | upload time      |
      | 1  | project 01 | 01.01.2013 12:00 |
      | 2  | project 02 | 01.01.2013 13:00 |
      | 3  | project 03 | 01.01.2013 14:00 |
      | 4  | project 04 | 01.01.2013 15:00 |
      | 5  | project 05 | 01.01.2013 16:00 |
      | 6  | project 06 | 01.01.2013 17:00 |
      | 7  | project 07 | 01.01.2013 18:00 |
      | 8  | project 08 | 01.01.2013 19:00 |
      | 9  | project 09 | 01.01.2013 20:00 |
      | 10 | project 10 | 01.01.2013 21:00 |
      | 11 | project 11 | 01.01.2013 22:00 |
      | 12 | project 12 | 01.01.2013 23:00 |
      | 13 | project 13 | 01.02.2013 11:00 |
      | 14 | project 14 | 01.02.2013 12:00 |
      | 15 | project 15 | 01.02.2013 13:00 |
      | 16 | project 16 | 01.02.2013 14:00 |
      | 17 | project 17 | 01.02.2013 15:00 |
      | 18 | project 18 | 01.02.2013 16:00 |
      | 19 | project 19 | 01.02.2013 17:00 |
      | 20 | project 20 | 01.02.2013 18:00 |
      | 21 | project 21 | 01.02.2013 19:00 |
      | 22 | project 22 | 01.02.2013 20:00 |
      | 23 | project 23 | 01.02.2013 21:00 |
      | 24 | project 24 | 01.02.2013 22:00 |
      | 25 | project 25 | 01.02.2013 23:00 |
      | 26 | project 26 | 01.02.2013 23:30 |
      | 27 | project 27 | 01.04.2013 11:00 |
      | 28 | project 28 | 01.04.2013 12:00 |
      | 29 | project 29 | 01.04.2013 13:00 |
      | 30 | project 30 | 01.04.2013 14:00 |
      | 31 | project 31 | 01.04.2013 16:00 |
      | 32 | project 32 | 01.05.2013 12:00 |
      | 33 | project 33 | 01.06.2013 12:00 |
      | 34 | project 34 | 01.07.2013 12:00 |

  Scenario: A project list is only initialized with 30 projects
    Given I am on homepage
    And I wait for the page to be loaded
    Then the "#home-projects__trending .projects-container" element should contain "project 34"
    And the "#home-projects__trending .projects-container" element should contain "project 33"
    And the "#home-projects__trending .projects-container" element should contain "project 32"
    And the "#home-projects__trending .projects-container" element should contain "project 31"
    And the "#home-projects__trending .projects-container" element should contain "project 30"
    And the "#home-projects__trending .projects-container" element should contain "project 29"
    And the "#home-projects__trending .projects-container" element should contain "project 28"
    And the "#home-projects__trending .projects-container" element should contain "project 27"
    And the "#home-projects__trending .projects-container" element should contain "project 26"
    And the "#home-projects__trending .projects-container" element should contain "project 25"
    And the "#home-projects__trending .projects-container" element should contain "project 24"
    And the "#home-projects__trending .projects-container" element should contain "project 23"
    And the "#home-projects__trending .projects-container" element should contain "project 22"
    And the "#home-projects__trending .projects-container" element should contain "project 21"
    And the "#home-projects__trending .projects-container" element should contain "project 20"
    And the "#home-projects__trending .projects-container" element should contain "project 19"
    And the "#home-projects__trending .projects-container" element should contain "project 18"
    And the "#home-projects__trending .projects-container" element should contain "project 17"
    And the "#home-projects__trending .projects-container" element should contain "project 16"
    And the "#home-projects__trending .projects-container" element should contain "project 15"
    And the "#home-projects__trending .projects-container" element should contain "project 14"
    And the "#home-projects__trending .projects-container" element should contain "project 13"
    And the "#home-projects__trending .projects-container" element should contain "project 12"
    And the "#home-projects__trending .projects-container" element should contain "project 11"
    And the "#home-projects__trending .projects-container" element should contain "project 10"
    And the "#home-projects__trending .projects-container" element should contain "project 09"
    And the "#home-projects__trending .projects-container" element should contain "project 08"
    And the "#home-projects__trending .projects-container" element should contain "project 07"
    And the "#home-projects__trending .projects-container" element should contain "project 06"
    And the "#home-projects__trending .projects-container" element should contain "project 05"
    And the "#home-projects__trending .projects-container" element should not contain "project 04"
    And the "#home-projects__trending .projects-container" element should not contain "project 03"
    And the "#home-projects__trending .projects-container" element should not contain "project 02"
    And the "#home-projects__trending .projects-container" element should not contain "project 01"
    When I scroll horizontal on "home-projects__trending" "projects-container" using a value of "300000"
    And I wait for the page to be loaded
    And the "#home-projects__trending .projects-container" element should contain "project 01"
    And the "#home-projects__trending .projects-container" element should contain "project 02"
    And the "#home-projects__trending .projects-container" element should contain "project 03"
    And the "#home-projects__trending .projects-container" element should contain "project 04"

  Scenario: The project list show more page presents all projects while scrolling down
    Given I am on homepage
    And I wait for the page to be loaded
    When I click "#home-projects__trending .project-list__title__btn-toggle__icon"
    And I wait for the page to be loaded
    Then the "#home-projects__trending .projects-container" element should contain "project 34"
    And the "#home-projects__trending .projects-container" element should contain "project 33"
    And the "#home-projects__trending .projects-container" element should contain "project 32"
    And the "#home-projects__trending .projects-container" element should contain "project 31"
    And the "#home-projects__trending .projects-container" element should contain "project 30"
    And the "#home-projects__trending .projects-container" element should contain "project 29"
    And the "#home-projects__trending .projects-container" element should contain "project 28"
    And the "#home-projects__trending .projects-container" element should contain "project 27"
    And the "#home-projects__trending .projects-container" element should contain "project 26"
    And the "#home-projects__trending .projects-container" element should contain "project 25"
    And the "#home-projects__trending .projects-container" element should contain "project 24"
    And the "#home-projects__trending .projects-container" element should contain "project 23"
    And the "#home-projects__trending .projects-container" element should contain "project 22"
    And the "#home-projects__trending .projects-container" element should contain "project 21"
    And the "#home-projects__trending .projects-container" element should contain "project 20"
    And the "#home-projects__trending .projects-container" element should contain "project 19"
    And the "#home-projects__trending .projects-container" element should contain "project 18"
    And the "#home-projects__trending .projects-container" element should contain "project 17"
    And the "#home-projects__trending .projects-container" element should contain "project 16"
    And the "#home-projects__trending .projects-container" element should contain "project 15"
    And the "#home-projects__trending .projects-container" element should contain "project 14"
    And the "#home-projects__trending .projects-container" element should contain "project 13"
    And the "#home-projects__trending .projects-container" element should contain "project 12"
    And the "#home-projects__trending .projects-container" element should contain "project 11"
    And the "#home-projects__trending .projects-container" element should contain "project 10"
    And the "#home-projects__trending .projects-container" element should contain "project 09"
    And the "#home-projects__trending .projects-container" element should contain "project 08"
    And the "#home-projects__trending .projects-container" element should contain "project 07"
    And the "#home-projects__trending .projects-container" element should contain "project 06"
    And the "#home-projects__trending .projects-container" element should contain "project 05"
    And the "#home-projects__trending .projects-container" element should not contain "project 04"
    And the "#home-projects__trending .projects-container" element should not contain "project 03"
    And the "#home-projects__trending .projects-container" element should not contain "project 02"
    And the "#home-projects__trending .projects-container" element should not contain "project 01"
    When I scroll vertical on "home-projects__trending" using a value of "300000"
    And I wait for the page to be loaded
    And the "#home-projects__trending .projects-container" element should contain "project 01"
    And the "#home-projects__trending .projects-container" element should contain "project 02"
    And the "#home-projects__trending .projects-container" element should contain "project 03"
    And the "#home-projects__trending .projects-container" element should contain "project 04"




