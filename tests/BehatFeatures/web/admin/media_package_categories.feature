@admin
Feature: Media Package Categories
  It should be possible to list all media package categories

  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |
    And there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org | 2  |
      | Gregor   | 123456   | dddddddddd | dev2@pocketcode.org | 3  |
    And there are media packages:
      | id | name    | name_url |
      | 1  | Looks   | looks    |
      | 2  | Sounds  | sounds   |
      | 3  | Objects | objects  |
    And there are media package categories:
      | id | name       | package | priority |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
    And there are media package files:
      | id | category   | name   | extension | file  | active | downloads | flavors    | author |
      | 1  | category 1 | File 1 | png       | 1.png | 1      | 1         | pocketcode | Admin  |


  Scenario: List media package categories sorted by ID
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-package/category/list"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |

  Scenario: List media package categories sorted by priority
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-package/category/list?filter%5B_sort_order%5D=ASC&filter%5B_sort_by%5D=priority&filter%5B_page%5D=1&filter%5B_per_page%5D=32&_list_mode=list"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 4  | Category 4 | Sounds  | 1        |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |

  Scenario: Delete media package category (fail)
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-package/category/list"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |
    Then I am on "/admin/media-package/category/1/delete"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |
    And I should see "This category is used by media package files!"

  Scenario: Delete media package category (success)
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-package/category/list"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |
    Then I am on "/admin/media-package/category/2/delete"
    And I wait for the page to be loaded
    Then I click on the first ".btn-danger" button
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 1  | Category 1 | Looks   | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |

  Scenario: Adding a media package category
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-package/category/list"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name       | Package | Priority |
      | 1  | Category 1 | Looks   | 0        |
      | 2  | Category 2 | Objects | 0        |
      | 3  | Category 3 | Sounds  | 0        |
      | 4  | Category 4 | Sounds  | 1        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/media-package/category/create"
    Then I write "New category" in textarea with label "Name"
    Then I select package "Looks" for media package category
    Then I select package "Objects" for media package category
    Then I write "2" in textarea with label "Priority"
    Then I click ".btn-success"
    And I go to "/admin/media-package/category/list"
    And I wait for the page to be loaded
    Then I should see the media package categories table:
      | Id | Name         | Package        | Priority |
      | 1  | Category 1   | Looks          | 0        |
      | 2  | Category 2   | Objects        | 0        |
      | 3  | Category 3   | Sounds         | 0        |
      | 4  | Category 4   | Sounds         | 1        |
      | 5  | New Category | Looks, Objects | 2        |