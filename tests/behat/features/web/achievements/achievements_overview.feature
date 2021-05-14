@web @achievements
Feature: Users have an achievement page for their overviews

  Background:
    Given there are achievements:
      | id | internal_title     | title_ltm_code | description_ltm_code | priority |
      | 1  | best_user          | best__         | best__desc           | 2        |
      | 2  | first_achiever     | first__        | first__desc          | 3        |
      | 3  | master_of_disaster | ups__          | ups__desc            | 1        |
    And there are users:
      | id | name      |
      | 1  | Achiever  |
      | 2  | Catrobat  |
      | 3  | ZeroAchiever |
    And there are user achievements:
      | id | user     | achievement        | seen_at | unlocked_at |
      | 1  | Catrobat | master_of_disaster |         | 2021-03-03  |
      | 2  | Achiever | best_user          |         | 2021-05-05  |
      | 2  | Achiever | first_achiever     |         | 2021-05-03  |
      | 2  | Achiever | master_of_disaster |         | 2021-05-02  |

  Scenario: Users must be logged in to see the achievements overview
    Given I am on "/app/achievements"
    And I wait for the page to be loaded
    Then I should be on "/app/login"

  Scenario: The achievement overview title is in the top bar
    Given I log in as "Achiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    And the "#top-app-bar__title" element should contain "Achievements"

  Scenario: Users without achievements must see a message
    Given I log in as "ZeroAchiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    And the element "#no-unlocked-achievements" should be visible

  Scenario: Users without achievements can't have a category for their most recent achievement
    Given I log in as "ZeroAchiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the element ".achievement-top__wrapper" should not exist

  Scenario: Users can see their most recent achievement in a specific category
    Given I log in as "Achiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the element ".achievement-top__wrapper" should be visible
    And the "h2" element should contain "Newest Achievement"
    And the ".achievement-top__badge__banner" element should contain "best__"
    And the ".achievement-top__text-wrapper" element should contain "best__desc"
    And the ".achievement-top__text-wrapper" element should contain "2021-05-05"
    And the ".achievement-top__text-wrapper" element should contain "3 out of 3 unlocked"

  Scenario: Users can see their most recent achievement in a specific category
    Given I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the element ".achievement-top__wrapper" should be visible
    And the ".achievement-top__badge__banner" element should contain "ups__"
    And the ".achievement-top__text-wrapper" element should contain "ups__desc"
    And the ".achievement-top__text-wrapper" element should contain "2021-03-03"
    And the ".achievement-top__text-wrapper" element should contain "1 out of 3 unlocked"

  Scenario: The achievements overview should have two tabs; locked and unlocked
    Given I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the ".tab-bar-container" element should contain "locked"
    And the ".tab-bar-container" element should contain "unlocked"
    And the element "#unlocked-achievements" should be visible
    And the element "#locked-achievements" should not be visible
    When I click "#locked-achievements-tab"
    Then the element "#unlocked-achievements" should not be visible
    And the element "#locked-achievements" should be visible
    When I click "#unlocked-achievements-tab"
    Then the element "#locked-achievements" should not be visible
    And the element "#unlocked-achievements" should be visible

  Scenario: The achievements must only be visible either in the locked or unlocked category
    Given I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "ups__"
    And the "#unlocked-achievements" element should not contain "best__"
    And the "#unlocked-achievements" element should not contain "first__"
    And the element "#no-unlocked-achievements" should not be visible
    When I click "#locked-achievements-tab"
    And the "#locked-achievements" element should not contain "ups__"
    And the "#locked-achievements" element should contain "best__"
    And the "#locked-achievements" element should contain "first__"
    And the element "#no-locked-achievements" should not be visible

  Scenario: There is an information in the locked tab if all achievements are unlocked
    Given I log in as "Achiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "ups__"
    And the "#unlocked-achievements" element should contain "best__"
    And the "#unlocked-achievements" element should contain "first__"
    And the element "#no-unlocked-achievements" should not be visible
    When I click "#locked-achievements-tab"
    And the "#locked-achievements" element should not contain "ups__"
    And the "#locked-achievements" element should not contain "best__"
    And the "#locked-achievements" element should not contain "first__"
    And the element "#no-locked-achievements" should be visible

  Scenario: There is an information in the unlocked tab if no achievements has been unlocked
    Given I log in as "ZeroAchiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "ups__"
    And the "#unlocked-achievements" element should not contain "best__"
    And the "#unlocked-achievements" element should not contain "first__"
    And the element "#no-unlocked-achievements" should be visible
    When I click "#locked-achievements-tab"
    And the "#locked-achievements" element should contain "ups__"
    And the "#locked-achievements" element should contain "best__"
    And the "#locked-achievements" element should contain "first__"
    And the element "#no-locked-achievements" should not be visible

  Scenario: Achievements have a badge, a banner and and a one-line description
    Given I log in as "Achiever"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the element "#unlocked-achievements" should be visible
    Then the element ".achievement" should be visible
    Then the element ".achievement__badge" should be visible
    Then the element ".achievement__badge__image" should be visible
    Then the element ".achievement__badge__banner" should be visible
    Then the element ".achievement__badge__text" should be visible


