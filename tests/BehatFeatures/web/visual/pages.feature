@web @visual
Feature: Visual snapshots of key pages
  Screenshots land in tests/TestReports/Visual and are compared against tests/visual/baselines
  by tests/visual/compare.sh. Differences are reported as CI warnings, never as failures.
  See docs/contributor-guide/Visual-Regression.md.

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | upload time      |
      | 1  | project 1 | Catrobat | 01.01.2024 12:00 |
    And there are achievements:
      | id | internal_title     | title_ltm_code | description_ltm_code | priority | badge_svg_path                                     |
      | 1  | best_user          | best__         | best__desc           | 2        | images/achievements/badge2-user-gold.svg           |
      | 2  | first_achiever     | first__        | first__desc          | 3        | images/achievements/badge1-perfect-profile.svg     |
      | 3  | master_of_disaster | ups__          | ups__desc            | 1        | images/achievements/badge2-user-silver.svg         |

  Scenario Outline: Login page (<scheme>, <size>)
    Given I use a <width> x <height> viewport
    And I use the "<scheme>" color scheme
    And I am on "/app/login"
    And I wait for the page to be loaded
    Then I take a visual snapshot "login-<scheme>-<size>"

    Examples:
      | scheme | size    | width | height |
      | light  | mobile  | 412   | 823    |
      | dark   | mobile  | 412   | 823    |
      | light  | desktop | 1280  | 900    |
      | dark   | desktop | 1280  | 900    |

  Scenario Outline: Project page (<scheme>, <size>)
    Given I use a <width> x <height> viewport
    And I use the "<scheme>" color scheme
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 1"
    Then I take a visual snapshot "project-<scheme>-<size>"

    Examples:
      | scheme | size    | width | height |
      | light  | mobile  | 412   | 823    |
      | dark   | mobile  | 412   | 823    |
      | light  | desktop | 1280  | 900    |
      | dark   | desktop | 1280  | 900    |

  Scenario Outline: New achievement popup (<scheme>, <size>)
    Given there are user achievements:
      | id | user     | achievement        | seen_at    | unlocked_at |
      | 1  | Catrobat | master_of_disaster |            | 2021-03-04  |
      | 2  | Catrobat | best_user          | 2021-03-03 | 2021-03-03  |
    And I use a <width> x <height> viewport
    And I log in as "Catrobat"
    And I use the "<scheme>" color scheme
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element ".swal2-popup" to contain "Congratulations"
    Then I take a visual snapshot "achievement-popup-<scheme>-<size>"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I take a visual snapshot "achievements-<scheme>-<size>"

    Examples:
      | scheme | size    | width | height |
      | light  | mobile  | 412   | 823    |
      | dark   | mobile  | 412   | 823    |
      | light  | desktop | 1280  | 900    |
      | dark   | desktop | 1280  | 900    |
