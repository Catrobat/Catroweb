@web @debug
Feature: Using a release app I should not see debug projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name          | owned by | downloads | views | upload time      | version | debug |
      | 1  | project 1     | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.9.10  | false |
      | 2  | project 2     | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.9.10  | false |
      | 3  | debug project | Catrobat | 450       | 80    | 01.04.2019 09:00 | 1.0.12  | true  |
      | 4  | project 4     | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.9.10  | false |

  Scenario: Viewing homepage with debug app
    Given I use a debug build of the Catroid app
    And I am on homepage
    And I wait for the page to be loaded
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "project 1"
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "project 2"
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "debug project"
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "project 4"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "project 1"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "project 2"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "debug project"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "project 4"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "project 1"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "project 2"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "debug project"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "project 4"

  Scenario: Viewing homepage with release app
    Given I use a release build of the Catroid app
    And I am on homepage
    And I wait for the page to be loaded
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "project 1"
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "project 2"
    Then none of the "#home-projects__recent .project-list__project__name" elements should contain "debug project"
    Then one of the "#home-projects__recent .project-list__project__name" elements should contain "project 4"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "project 1"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "project 2"
    Then none of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "debug project"
    Then one of the "#home-projects__most_downloaded .project-list__project__name" elements should contain "project 4"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "project 1"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "project 2"
    Then none of the "#home-projects__random .project-list__project__name" elements should contain "debug project"
    Then one of the "#home-projects__random .project-list__project__name" elements should contain "project 4"

  Scenario: Viewing profile with debug app
    Given I use a debug build of the Catroid app
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "project 2"
    And I should see "debug project"
    And I should see "project 4"

  Scenario: Viewing profile with release app
    Given I use a release build of the Catroid app
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "project 2"
    # In new API, debug projects are only visible using debug app
    And I should not see "debug project"
    And I should see "project 4"

  Scenario: Viewing project marked as debug using debug app
    Given I use a debug build of the Catroid app
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    And I should see "debug project"

  Scenario: Viewing project marked as debug using release app
    Given I use a release build of the Catroid app
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    And I should not see "debug project"
    And I should be on "/app/"
