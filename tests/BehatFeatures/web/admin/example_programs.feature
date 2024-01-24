@admin
Feature: Adminius example programs
  It should be possible to list all example programs, sort and filter etc.

  Background:
    Given there are admins:
      | name     |
      | Adminius |

    And there are users:
      | name      | password | token      | email               | id |
      | Superman  | 123456   | cccccccccc | dev1@pocketcode.org | 2  |
      | Gregor    | 123456   | dddddddddd | dev2@pocketcode.org | 3  |
      | Frank Jr. | 123456   | qwertyuiop | dev3@pocketcode.org | 4  |

    And there are projects:
      | id          | name      | description             | owned by  | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready |
      | 1337-c0ffee | program 1 | my superman description | Superman  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      |
      | c0ffee-b00b | program 2 | abcef                   | Gregor    | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | c01d-cafe   | program 3 | abcef                   | Gregor    | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | b100d-c01d  | program 4 | abc                     | Superman  | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | dead-beef   | to add    | add me if u can         | Frank Jr. | 123       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |

    And there are flavors:
      | id | name       |
      | 1  | arduino    |
      | 2  | embroidery |

    And following projects are examples:
      | name      | active | priority | flavor     | ios_only |
      | program 1 | 0      | 1        | arduino    | yes      |
      | program 2 | 1      | 2        | embroidery | no       |
      | program 3 | 1      | 3        | embroidery | no       |
      | program 4 | 1      | 3        | arduino    | no       |

  Scenario: List all example programs:
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/example_program/list"
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
    And I should not see "Adminius"

  Scenario: Delete first example Program
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/example_program/list"
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
    Then I am on "/admin/example_program/1/delete"
    And I wait for the page to be loaded
    Then I click on the first ".btn-danger" button
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
    And I should not see "Adminius"
    And I should not see "program 1"

  Scenario: Click on program link
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/example_program/list"
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
    And I click on the "program 1 (#1337-c0ffee)" link
    And I wait for the page to be loaded
    Then I should see "Show \"program 1 (#1337-c0ffee)\""

  Scenario: Adding an example Program (success)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/example_program/list"
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/example_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "dead-beef" in textarea with label "Program Id"
    Then I write "3" in textarea with label "Priority"
    Then I select flavor "arduino" for example project
    Then I click ".btn-success"
    Then I should see "has been successfully created"
    And I am on "/admin/example_program/list"
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
      | 5  | to add (#dead-beef)      | arduino    | 3        |

  Scenario: Adding a example Program (fail)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/example_program/list"
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/example_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "dead-b00f" in textarea with label "Program Id"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    And I am on "/admin/example_program/list"
    Then I should see the example table:
      | Id | Project                  | Flavor     | Priority |
      | 1  | program 1 (#1337-c0ffee) | arduino    | 1        |
      | 2  | program 2 (#c0ffee-b00b) | embroidery | 1        |
      | 3  | program 3 (#c01d-cafe)   | embroidery | 2        |
      | 4  | program 4 (#b100d-c01d)  | arduino    | 3        |
