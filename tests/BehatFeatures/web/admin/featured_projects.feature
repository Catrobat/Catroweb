@admin
Feature: Admin featured projects
  It should be possible to list all featured projects, sort and filter etc.

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |

    And there are users:
      | name      | password | token      | email               | id |
      | Superman  | 123456   | cccccccccc | dev1@pocketcode.org | 2  |
      | Gregor    | 123456   | dddddddddd | dev2@pocketcode.org | 3  |
      | Frank Jr. | 123456   | qwertyuiop | dev3@pocketcode.org | 4  |

    And there are projects:
      | id          | name      | description             | owned by  | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready |
      | 1337-c0ffee | project 1 | my superman description | Superman  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      |
      | c0ffee-b00b | project 2 | abcef                   | Gregor    | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | c01d-cafe   | project 3 | abcef                   | Gregor    | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | b100d-c01d  | project 4 | abc                     | Superman  | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | dead-beef   | to add    | add me if u can         | Frank Jr. | 123       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |

    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
      | 3  | arduino    |
      | 4  | embroidery |

    And there are featured projects:
      | id | project_id  | active | flavor     | priority | ios_only |
      | 1  | 1337-c0ffee | 1      | pocketcode | 1        | yes      |
      | 2  | c0ffee-b00b | false  | arduino    | 1        | no       |
      | 3  | c01d-cafe   | 1      | luna       | 2        | no       |
      | 4  | b100d-c01d  | false  | embroidery | 3        | no       |

  Scenario: List all featured projects:
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I should not see "Adminius"

  Scenario: List featured projects just for arduino
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list?filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bfor_ios%5D%5Btype%5D=&filter%5Bfor_ios%5D%5Bvalue%5D=&filter%5Bactive%5D%5Btype%5D=&filter%5Bactive%5D%5Bvalue%5D=&filter%5Bpriority%5D%5Btype%5D=&filter%5Bpriority%5D%5Bvalue%5D=&filter%5Bflavor%5D%5Btype%5D=&filter%5Bflavor%5D%5Bvalue%5D=3&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor  | Priority |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino | 1        |
    And I should not see "Adminius"
    And I should not see "project 1"
    And I should not see "project 3"

  Scenario: List featured projects just for IOS
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list?filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bfor_ios%5D%5Btype%5D=&filter%5Bfor_ios%5D%5Bvalue%5D=1&filter%5Bactive%5D%5Btype%5D=&filter%5Bactive%5D%5Bvalue%5D=&filter%5Bpriority%5D%5Btype%5D=&filter%5Bpriority%5D%5Bvalue%5D=&filter%5Bflavor%5D%5Btype%5D=&filter%5Bflavor%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
    And I should not see "Adminius"
    And I should not see "project 2"
    And I should not see "project 3"

  Scenario: List featured projects with priority above 1
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list?filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bfor_ios%5D%5Btype%5D=&filter%5Bfor_ios%5D%5Bvalue%5D=&filter%5Bactive%5D%5Btype%5D=&filter%5Bactive%5D%5Bvalue%5D=&filter%5Bpriority%5D%5Btype%5D=2&filter%5Bpriority%5D%5Bvalue%5D=1&filter%5Bflavor%5D%5Btype%5D=&filter%5Bflavor%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                 | Url | Flavor     | Priority |
      | 3  | project 3 (#c01d-cafe)  |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d) |     | embroidery | 3        |
    And I should not see "Adminius"
    And I should not see "project 1"
    And I should not see "project 2"

  Scenario: List only active featured projects
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list?filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bfor_ios%5D%5Btype%5D=&filter%5Bfor_ios%5D%5Bvalue%5D=&filter%5Bactive%5D%5Btype%5D=&filter%5Bactive%5D%5Bvalue%5D=1&filter%5Bpriority%5D%5Btype%5D=&filter%5Bpriority%5D%5Bvalue%5D=&filter%5Bflavor%5D%5Btype%5D=&filter%5Bflavor%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
    And I should not see "Adminius"
    And I should not see "project 2"
    And I should not see "project 4"

  Scenario: Delete first Featured Project
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    Then I am on "/admin/featured_program/1/delete"
    And I wait for the page to be loaded
    Then I click on the first ".btn-danger" button
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I should not see "Adminius"
    And I should not see "project 1"
    Then I am on "/app"
    And I wait for the page to be loaded
    Then I should not see "featured project"

  Scenario: Click on project link
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "project 1 (#1337-c0ffee)" link
    And I wait for the page to be loaded
    Then I should see "Show \"project 1 (#1337-c0ffee)\""

  Scenario: Adding a featured Project (success)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/featured_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "dead-beef" in textarea with label "Project Id Or Url"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    Then I should see "has been successfully created"
    And I am on "/admin/featured_program/list"
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
      | 5  | to add (#dead-beef)      |     | pocketcode | 3        |

  Scenario: Adding a featured Project (fail)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/featured_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "dead-b00f" in textarea with label "Project Id Or Url"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    And I am on "/admin/featured_program/list"
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |

  Scenario: Adding a featured Project (wrong picture)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/featured_program/create"
    When I attach the avatar "fail.tif" to "File"
    Then I write "dead-b00f" in textarea with label "Project Id Or Url"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    Then I should see "Error"

  Scenario: Adding a featured Project with URL - Project (success)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/featured_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "1" in textarea with label "Use Url"
    Then I write "catrobat.at/app/project/dead-beef" in textarea with label "Project Id Or Url"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    Then I should see "has been successfully created"
    And I am on "/admin/featured_program/list"
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
      | 5  | to add (#dead-beef)      |     | pocketcode | 3        |

  Scenario: Adding a featured Project with URL - Extern (success)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/featured_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    When I click ".iCheck-helper"
    Then the "Use Url" checkbox should be checked
    Then I write "http://www.google.com" in textarea with label "Project Id Or Url"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    And I am on "/admin/featured_program/list"
    Then I should see the featured table:
      | Id | Project                  | Url                   | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |                       | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |                       | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |                       | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |                       | embroidery | 3        |
      | 5  |                          | http://www.google.com | pocketcode | 3        |

  Scenario: Adding a featured Project with URL - Project (fail)
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/featured_program/list"
    And I wait for the page to be loaded
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/featured_program/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "catrobat.at/app/project/dead-b00f" in textarea with label "Project Id Or Url"
    Then I write "3" in textarea with label "Priority"
    Then I click ".btn-success"
    Then I should see "Error"
    And I am on "/admin/featured_program/list"
    Then I should see the featured table:
      | Id | Project                  | Url | Flavor     | Priority |
      | 1  | project 1 (#1337-c0ffee) |     | pocketcode | 1        |
      | 2  | project 2 (#c0ffee-b00b) |     | arduino    | 1        |
      | 3  | project 3 (#c01d-cafe)   |     | luna       | 2        |
      | 4  | project 4 (#b100d-c01d)  |     | embroidery | 3        |