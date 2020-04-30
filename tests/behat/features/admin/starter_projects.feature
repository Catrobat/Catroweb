@admin
Feature: Admin starter projects
  It should be possible to list all starter projects sorted by their order, change all values and delete an entry

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org |  1 |
    And there are users:
      | name     | password | token      | email               | id |
      | Karim    | 123456   | cccccccccc | dev1@pocketcode.org |  2 |
      | Pauli    | 123456   | dddddddddd | dev2@pocketcode.org |  3 |
    And there are starter programs with name "Serious uff", alias "Serious" and order 2:
      | id | name      | description | owned by |
      | 2  | program 2 | abcef       | Pauli    |
      | 3  | program 3 | abcef       | Karim    |
    And there are starter programs with name "Funny lol", alias "Funny" and order 1:
      | id | name      | description | owned by |
      | 1  | program 1 | p1          | Pauli    |

  Scenario: Show starter programs list and it should be sorted by order ASC
    Given I log in as "Adminius" with the password "123456"
    Given I am on "/admin/categories/list"
    And I wait for the page to be loaded
    Then I should see the starter programs table:
      | Starter Category | Category Alias | Programs                       | Order  |
      | Funny lol        | Funny          | program 1 (#1)                 | 1      |
      | Serious uff      | Serious        | program 2 (#2), program 3 (#3) | 2      |

  Scenario: Remove element from list via remove button
    Given I log in as "Adminius" with the password "123456"
    Given I am on "/admin/categories/list"
    And I wait for the page to be loaded
    And I am on "/admin/categories/removeFromStarterTable?id=2"
    And I wait for the page to be loaded
    Then I should see the starter programs table:
      | Starter Category | Category Alias | Programs                       | Order  |
      | Serious uff      | Serious        | program 2 (#2), program 3 (#3) | 2      |

  Scenario: Check edit form from edit button
    Given I log in as "Adminius" with the password "123456"
    Given I am on "/admin/categories/list"
    And I wait for the page to be loaded
    And I am on "/admin/categories/2/edit"
    And I wait for the page to be loaded
    And I should see "Name"
    And I should see "Alias"
    And I should see "Programs"
    And I should see "Order"

  Scenario: Check add new form from add new button
    Given I log in as "Adminius" with the password "123456"
    Given I am on "/admin/categories/list"
    And I wait for the page to be loaded
    And I am on "/admin/categories/create"
    And I wait for the page to be loaded
    And I should see "Name"
    And I should see "Alias"
    And I should see "Programs"
    And I should see "Order"
