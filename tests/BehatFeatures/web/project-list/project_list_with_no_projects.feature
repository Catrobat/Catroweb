@homepage
Feature: Project list must be hidden if there are no projects in a category

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  #  index
  Scenario: When there are no projects there should not be containers on the homepage
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should not see "Newest projects"
    And the element "#home-projects__recent" should not be visible
    And I should not see "Recommended"
    And I should not see "Most downloaded"
    And I should not see "Most viewed"
    And I should not see "Random projects"

  #  user pages
  Scenario: at my profile page when I have no projects there should be a text telling me about it
    Given I log in as "Catrobat"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then I should see 0 "#myprofile-projects .project"
    And I should see "There are currently no projects."

  Scenario: at a profile page when a user has has no projects there should be a text telling us about it
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then I should see 0 "#user-projects .project"
    And I should see "There are currently no projects."
