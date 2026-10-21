@web @project_page
Feature: The web server cannot serve project files through legacy static URLs

  Background:
    Given there are users:
      | id | name  |
      | 1  | Owner |
    And there are downloadable projects:
      | id | name    | owned by |
      | 1  | Project | Owner    |

  Scenario: Extracted images are authorized before delivery and legacy image URLs fail
    Then the raw-origin file "/resources_test/extract/1/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png" is denied while "/project-files/1/assets/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png" is served as "image/png"

  Scenario: Existing archive files cannot bypass the application
    Then the raw-origin file "/resources_test/projects/1.catrobat" is denied while "/project-files/1/archive" is served as "application/zip"
