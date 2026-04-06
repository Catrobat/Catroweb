@admin
Feature: Admin featured banners
  It should be possible to list all featured banners, sort and filter etc.

  Background:
    Given there are admins:
      | name     | password | email                | id |
      | Adminius | 123456   | admin@pocketcode.org | 1  |

    And there are users:
      | name      | password | email               | id |
      | Superman  | 123456   | dev1@pocketcode.org | 2  |
      | Gregor    | 123456   | dev2@pocketcode.org | 3  |
      | Frank Jr. | 123456   | dev3@pocketcode.org | 4  |

    And there are projects:
      | id          | name      | description             | owned by  | downloads | views | upload time      | version | language version | visible |
      | 1337-c0ffee | program 1 | my superman description | Superman  | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    |
      | c0ffee-b00b | program 2 | abcef                   | Gregor    | 333       | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    |
      | c01d-cafe   | program 3 | abcef                   | Gregor    | 333       | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    |
      | b100d-c01d  | program 4 | abc                     | Superman  | 333       | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    |
      | dead-beef   | to add    | add me if u can         | Frank Jr. | 123       | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    |

  Scenario: Access featured banner admin list
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured/banner/list"
    And I wait for the page to be loaded
    Then I should see "Featured Banner"
    And I should not see "Adminius"

  Scenario: Create a new featured banner for a project
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured/banner/create"
    And I wait for the page to be loaded
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    Then I should see "has been successfully created"
