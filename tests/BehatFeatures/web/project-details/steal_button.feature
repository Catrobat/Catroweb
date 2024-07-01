@web @project_page
Feature: Steal Button

  Scenario: User clicks on the steal button
    Given I am logged in as "simon20330"
    And I am on the project details page of a project owned by "Dom"
    When I press "Steal"
    Then I should see "Project stolen successfully!"
    And the project should now be owned by "simon20330"