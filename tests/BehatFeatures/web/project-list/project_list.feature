@homepage
Feature: Project list to provides users with different categories of projects

  Background:
    Given there are projects:
      | id               | name           |
      | the-only-project | TheOnlyProject |

  Scenario Outline: Each project list has a unique category title
    Given I am on <page>
    And I wait for the page to be loaded
    Then the element <category> should be visible
    Then the <category> element should contain <title>
    Examples:
      | category                                               | title              | page |
      | "#home-projects__trending .project-list__title"        | "Trending projects | "/"  |
      | "#home-projects__most_downloaded .project-list__title" | "Most downloaded"  | "/"  |

  Scenario Outline: Each project list has its own show more button
    Given I am on <page>
    And I wait for the page to be loaded
    Then the element <category> should be visible
    Examples:
      | category                                                                 | page |
      | "#home-projects__trending .project-list__title__btn-toggle__icon"        | "/"  |
      | "#home-projects__most_downloaded .project-list__title__btn-toggle__icon" | "/"  |

  Scenario Outline: Each project list has its own container
    Given I am on <page>
    And I wait for the page to be loaded
    Then the element <category> should be visible
    Examples:
      | category                                              | page |
      | "#home-projects__trending .projects-container"        | "/"  |
      | "#home-projects__most_downloaded .projects-container" | "/"  |

  Scenario: A project container is filled with projects
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element ".projects-container .project-list__project" should be visible

  Scenario: A project should have a name
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element ".project-list__project .project-list__project__name" should be visible
    And the ".project-list__project .project-list__project__name" element should contain "TheOnlyProject"

  Scenario: A project should have an image
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element ".project-list__project img" should be visible

  Scenario Outline: A project list project has a unique property
    Given I am on <page>
    And I wait for the page to be loaded
    Then the element <category> should be visible
    And the <category> element should contain <value>
    Examples:
      | category                                                                     | value     | page |
      | "#home-projects__popular .project-list__project__property-uploaded"          | "person"  | "/"  |
      | "#home-projects__most_downloaded .project-list__project__property-downloads" | "get_app" | "/"  |

  Scenario Outline: A click on a project redirects to the project page
    Given I am on <page>
    And I wait for the page to be loaded
    When I click <project>
    And I wait for the page to be loaded
    Then I should be on "/app/project/the-only-project"
    Examples:
      | project                                              | page |
      | "#home-projects__trending .project-list__project"    | "/"  |

  Scenario Outline: The show more button opens a full page only with projects from the category
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element <category> should be visible
    When I click <category>
    And I wait for the page to be loaded
    Then I should be on <page>
    And the "#top-app-bar__title" element should contain <title>
    And the ".project-list__project .project-list__project__name" element should contain "TheOnlyProject"
    When I click "#top-app-bar__title"
    And I wait for the page to be loaded
    Then I should be on <page>
    When I click "#top-app-bar__back__btn-back"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    And the "#top-app-bar__title" element should contain "Catrobat community"
    Examples:
      | category                                                                 | title              | page                                   |
      | "#home-projects__trending .project-list__title__btn-toggle__icon"        | "Trending projects" | "/app/#home-projects__trending"       |
      | "#home-projects__most_downloaded .project-list__title__btn-toggle__icon" | "Most downloaded"  | "/app/#home-projects__most_downloaded" |
