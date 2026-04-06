@web @studio
Feature: A studio has a project section

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |

    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |

    And there are projects:
      | id | name      | description    | owned by    |
      | 1  | project 1 | my description | StudioAdmin |
      | 2  | project 2 | my description | StudioAdmin |

    And there are studio users:
      | id | user        | studio_id | role  |
      | 1  | StudioAdmin | 1         | admin |

    And there are studio projects:
      | id | studio_id | project   | user        |
      | 1  | 1         | project 2 | StudioAdmin |

  Scenario: Studio detail page shows projects loaded via AJAX
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "[data-studio--project-list-target='container']" to contain "project 2"

  Scenario: Add Project FAB is visible for studio members
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studios-fab" should be visible

  Scenario: Admin can remove a project
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 2"
    When I click ".projects-list-item--menu-btn"
    And I wait 500 milliseconds
    And I click "[data-menu-action='remove']"
    And I wait 500 milliseconds
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "[data-studio--project-list-target='container']" should not contain "project 2"
