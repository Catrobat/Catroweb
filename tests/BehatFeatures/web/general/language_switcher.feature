@web @translations
Feature: Users can choose between multiple languages. Text should be automatically translated.

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name    | owned by  |
      | 1  | Minions | Catrobat  |
      | 2  | Galaxy  | OtherUser |
      | 3  | Alone   | Catrobat  |
    And there are project reactions:
      | user      | project | type | created at       |
      | Catrobat  | 1       | 1    | 01.01.2017 12:00 |
      | Catrobat  | 2       | 2    | 01.01.2017 12:00 |
      | OtherUser | 1       | 4    | 01.01.2017 12:00 |
    And there are featured projects:
      | id | name    | active | flavor     | priority | ios_only |
      | 1  | Minions | 1      | pocketcode | 1        | no       |


  Scenario: user can choose their language from a dropdown menu in the footer
    Given I am on homepage
    Then the selected language should be "English"
    And I wait for the page to be loaded
    And I should see "featured"
    But I should not see "Empfohlen"
    Then I switch the language to "Deutsch"
    And I wait for the page to be loaded
    Then the selected language should be "Deutsch"
    And I should see "Empfohlen"
    But I should not see "featured"

  Scenario: Users should be able to switch the language
    Given the selected language is "English"
    And I am on the homepage
    And I wait for the page to be loaded
    Then one of the ".project-list__title" elements should contain "Most downloaded"
    And the element "#home-projects__most_downloaded" should be visible
    When I switch the language to "Russisch"
    And I wait for the page to be loaded
    Then one of the ".project-list__title" elements should contain "Самые скачиваемые"
    And the element "#home-projects__most_downloaded" should be visible
    When I switch the language to "French"
    And I wait for the page to be loaded
    Then one of the ".project-list__title" elements should contain "Les plus téléchargés"
    And the element "#home-projects__most_downloaded" should be visible
    When I switch the language to "Deutsch"
    And I wait for the page to be loaded
    Then one of the ".project-list__title" elements should contain "heruntergeladen"
    And the element "#home-projects__most_downloaded" should be visible

  Scenario: User with selected russian language sees the remix graph button and details on project page
    Given there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |
    And I am on "/app/project/1"
    And the selected language is "English"
    And I wait for the page to be loaded
    Then I should see "REMIX GRAPH"
    And the element "#remixGraphButton-small" should be visible
    When I switch the language to "Russisch"
    And I wait for the page to be loaded
    Then I should see "Графа ремиксов"
    And the element "#remixGraphButton-small" should be visible
