@admin
Feature: Admin reported programs
  It should be possible to list all reported programs, unreport them and edit their visible status

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org |  0 |
    And there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | Gregor   | 123456   | dddddddddd | dev2@pocketcode.org |  2 |
    And there are programs:
      | id | name      | description              | visible |
      | 1  | program 1 | my superman description  | true    |
      | 2  | program 2 | abcef                    | true    |
      | 3  | program 3 | hello                    | true    |

  Scenario: List reported programs sorted by date descending
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "spam" and note "Even Worse Program" in Browser
    And I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | New      | Spam       | Adminius       | program 2             | no              |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |

  Scenario: Report another program and list should still be sorted by date descending
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "spam" and note "Even Worse Program" in Browser
    And I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | New      | Spam       | Adminius       | program 2             | no              |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |
    Then I report program 3 with category "dislike" and note "Pure Filth" in Browser
    And I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Pure Filth         | New      | Dislike    | Adminius       | program 3             | no              |
      | Even Worse Program | New      | Spam       | Adminius       | program 2             | no              |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |

  Scenario: Unreport project
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "spam" and note "Even Worse Program" in Browser
    And I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    And I am on "/admin/app/programinappropriatereport/unreportProgram?id=2"
    And I wait for the page to be loaded
    Then I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |

  Scenario: Accept report of project with state filter
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "spam" and note "Even Worse Program" in Browser
    And I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    Then I am on "/admin/app/programinappropriatereport/acceptProgramReport?id=2"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | Accepted | Spam       | Adminius       | program 2             | no              |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |
    Then I am on "/admin/app/programinappropriatereport/list?filter%5Btime%5D%5Btype%5D=&filter%5Btime%5D%5Bvalue%5D%5Bstart%5D=&filter%5Btime%5D%5Bvalue%5D%5Bend%5D=&filter%5Bstate%5D%5Btype%5D=&filter%5Bstate%5D%5Bvalue%5D=2&filter%5Bcategory%5D%5Btype%5D=&filter%5Bcategory%5D%5Bvalue%5D=&filter%5BreportingUser__username%5D%5Btype%5D=&filter%5BreportingUser__username%5D%5Bvalue%5D=&filter%5Bprogram__visible%5D%5Btype%5D=&filter%5Bprogram__visible%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | Accepted | Spam       | Adminius       | program 2             | no              |
    And I should not see "Bad Program"
    Then I am on "/app"
    And I wait for the page to be loaded
    And I should not see "program 1"
    And I should not see "program 2"

  Scenario: Decline report of project with state filter
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "spam" and note "Even Worse Program" in Browser
    And I am on "/admin/app/programinappropriatereport/list"
    And I wait for the page to be loaded
    Then I am on "/admin/app/programinappropriatereport/unreportProgram?id=2"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | Rejected | Spam       | Adminius       | program 2             | yes             |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |
    Then I am on "/admin/app/programinappropriatereport/list?filter%5Btime%5D%5Btype%5D=&filter%5Btime%5D%5Bvalue%5D%5Bstart%5D=&filter%5Btime%5D%5Bvalue%5D%5Bend%5D=&filter%5Bstate%5D%5Btype%5D=&filter%5Bstate%5D%5Bvalue%5D=3&filter%5Bcategory%5D%5Btype%5D=&filter%5Bcategory%5D%5Bvalue%5D=&filter%5BreportingUser__username%5D%5Btype%5D=&filter%5BreportingUser__username%5D%5Bvalue%5D=&filter%5Bprogram__visible%5D%5Btype%5D=&filter%5Bprogram__visible%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | Rejected | Spam       | Adminius       | program 2             | yes             |
    And I should not see "Bad Program"
    Then I am on "/app"
    And I wait for the page to be loaded
    And I should not see "program 1"
    And I should see "program 2"

  Scenario: Category filter
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "dislike" and note "Even Worse Program" in Browser
    Then I am on "/admin/app/programinappropriatereport/list?filter%5Btime%5D%5Btype%5D=&filter%5Btime%5D%5Bvalue%5D%5Bstart%5D=&filter%5Btime%5D%5Bvalue%5D%5Bend%5D=&filter%5Bstate%5D%5Btype%5D=&filter%5Bstate%5D%5Bvalue%5D=&filter%5Bcategory%5D%5Btype%5D=&filter%5Bcategory%5D%5Bvalue%5D=Spam&filter%5BreportingUser__username%5D%5Btype%5D=&filter%5BreportingUser__username%5D%5Bvalue%5D=&filter%5Bprogram__visible%5D%5Btype%5D=&filter%5Bprogram__visible%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Bad Program        | New      | Spam       | Adminius       | program 1             | no              |
    And I should not see "Dislike"

  Scenario: Visible filter
    Given I log in as "Adminius" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    And I report program 2 with category "dislike" and note "Even Worse Program" in Browser
    Then I am on "/admin/app/programinappropriatereport/unreportProgram?id=2"
    And I wait for the page to be loaded
    Then I am on "/admin/app/programinappropriatereport/list?filter%5Btime%5D%5Btype%5D=&filter%5Btime%5D%5Bvalue%5D%5Bstart%5D=&filter%5Btime%5D%5Bvalue%5D%5Bend%5D=&filter%5Bstate%5D%5Btype%5D=&filter%5Bstate%5D%5Bvalue%5D=&filter%5Bcategory%5D%5Btype%5D=&filter%5Bcategory%5D%5Bvalue%5D=&filter%5BreportingUser__username%5D%5Btype%5D=&filter%5BreportingUser__username%5D%5Bvalue%5D=&filter%5Bprogram__visible%5D%5Btype%5D=&filter%5Bprogram__visible%5D%5Bvalue%5D=1&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | Rejected | Dislike    | Adminius       | program 2             | yes             |
    And I should not see "Bad Program"

  Scenario: Reporting User Username filter
    Given I log in as "Superman" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    Then I logout
    And I log in as "Adminius" with the password "123456"
    And I report program 2 with category "spam" and note "Even Worse Program" in Browser
    Then I am on "/admin/app/programinappropriatereport/list?filter%5Btime%5D%5Btype%5D=&filter%5Btime%5D%5Bvalue%5D%5Bstart%5D=&filter%5Btime%5D%5Bvalue%5D%5Bend%5D=&filter%5Bstate%5D%5Btype%5D=&filter%5Bstate%5D%5Bvalue%5D=&filter%5Bcategory%5D%5Btype%5D=&filter%5Bcategory%5D%5Bvalue%5D=&filter%5BreportingUser__username%5D%5Btype%5D=&filter%5BreportingUser__username%5D%5Bvalue%5D=Adminius&filter%5Bprogram__visible%5D%5Btype%5D=&filter%5Bprogram__visible%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the reported programs table:
      | Note               | State    | Category   | Reporting User | Program               | Program Visible |
      | Even Worse Program | New      | Spam       | Adminius       | program 2             | no              |
    And I should not see "Superman"