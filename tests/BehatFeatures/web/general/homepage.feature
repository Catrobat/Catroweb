@web
Feature: Pocketcode homepage
  In order to access and browse the projects
  As a visitor
  I want to be able to see the homepage

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | User1     |
      | 3  | Catrobat2 |

    And there are extensions:
      | id | internal_title |
      | 1  | embroidery     |

    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 3  | embroidery |

    And there are projects:
      | id | name      | owned by  | extensions | flavor     |
      | 1  | project 1 | Catrobat  | embroidery | pocketcode |
      | 2  | project 2 | Catrobat  | embroidery | embroidery |
      | 3  | project 3 | User1     |            | pocketcode |
      | 4  | project 4 | User1     |            | embroidery |
      | 5  | project 5 | User1     |            | pocketcode |
      | 6  | project 6 | Catrobat2 |            | pocketcode |
      | 7  | project 7 | Catrobat2 |            | pocketcode |

    And there are featured banners:
      | name      | url                   | active | priority | type    |
      | project 1 |                       | 0      | 1        | project |
      | project 2 |                       | 1      | 3        | project |
      | project 3 |                       | 1      | 2        | project |
      |           | http://www.google.at/ | 1      | 5        | link    |
      |           | http://www.orf.at/    | 0      | 4        | link    |

    And following projects are examples:
      | name      | active | priority |
      | project 4 | 0      | 1        |
      | project 5 | 1      | 3        |
      | project 6 | 1      | 2        |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 6                 |
      | 70058680          | 7                 |

  Scenario: Viewing the homepage at website root
    Given I am on homepage
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see the featured slider
    Then one of the ".project-list__title" elements should contain "Examples"
    Then one of the ".project-list__title" elements should contain "Trending projects"
    Then one of the ".project-list__title" elements should contain "Random projects"
    Then one of the ".project-list__title" elements should contain "Popular projects"

  Scenario: Featured Programs and Urls
    Given I am on homepage
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see the featured slider
    And I should see the slider with the values "http://www.google.at/,project 2,project 3"

  @disabled
  Scenario: Example Programs
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element "#home-projects__example" should exist
    And the "#home-projects__example" element should contain "project 5"
    And the "#home-projects__example" element should contain "project 6"

  Scenario: Guest user does not see My Projects section on the landing page
    Given I am on homepage
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element "#home-projects__my_projects" should not exist

  Scenario: Logged-in user with projects sees My Projects section on the landing page
    Given I log in as "Catrobat"
    And I go to the homepage
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element "#home-projects__my_projects" should exist
    And one of the ".project-list__title" elements should contain "My shared projects"

  Scenario: My Projects section appears before other categories on the landing page
    Given I log in as "Catrobat"
    And I go to the homepage
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element "#home-projects__my_projects" should exist
    And the element "#home-projects__trending" should exist
    And the element "#home-projects__my_projects ~ #home-projects__trending" should exist

  Scenario: Homepage shows popular studios section
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element "#popular-studios" should exist

  Scenario: User should be able to see legally required links
    Given I am on homepage
    And I wait for the page to be loaded
    And I should see "About Catrobat"
    And I should see "License to play"
    And I should see "Privacy policy"
    And I should see "Terms of Use"
