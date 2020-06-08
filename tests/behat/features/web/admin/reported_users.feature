@admin
Feature: Admin reported users
  It should be possible to list all users sorted by the number of their reported comments or of their reported projects

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org |  0 |
    And there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | Gregor   | 123456   | dddddddddd | dev2@pocketcode.org |  2 |
    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready |
      | 1  | program 1 | my superman description | Superman | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      |
      | 2  | program 2 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | 3  | program 3 | abcef                   | Gregor   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
    And there are comments:
      | program_id | user_id | upload_date      | text | user_name | reported |
      | 2          | 1       | 01.01.2020 12:01 | c1   | Superman  | 1        |
      | 1          | 2       | 01.01.2020 12:01 | c2   | Gregor    | 1        |
      | 1          | 2       | 01.01.2020 12:01 | c3   | Gregor    | 1        |
      | 1          | 2       | 01.01.2020 12:01 | c4   | Gregor    | 1        |
      | 3          | 1       | 01.01.2020 12:01 | c5   | Superman  | 1        |
      | 3          | 1       | 01.01.2020 12:01 | c6   | Superman  | 1        |
      | 3          | 1       | 01.01.2020 12:01 | c7   | Superman  | 1        |
      | 3          | 1       | 01.01.2020 12:01 | c8   | Superman  | 1        |
    And there are inappropriate reports:
     | category      | program_id | user_id | time             | note |
     | inappropriate | 2          | 1       | 01.01.2020 12:01 | c1   |
     | inappropriate | 2          | 1       | 01.01.2020 12:01 | c2   |

  Scenario: List reported users sorted by reported comments
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/reported_users/list?filter%5B_sort_order%5D=DESC&filter%5B_sort_by%5D=getReportedCommentsCount&filter%5B_page%5D=1&filter%5B_per_page%5D=32&_list_mode=list"
    And I wait for the page to be loaded
    Then I should see the reported table:
      | #Reported Comments | #Reported Programs | Username | Email               |
      | 5                  | 0                  | Superman | dev1@pocketcode.org |
      | 3                  | 2                  | Gregor   | dev2@pocketcode.org |
    And I should not see "Adminius"

  Scenario: List reported users sorted by reported programs
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/reported_users/list?filter%5B_sort_order%5D=DESC&filter%5B_sort_by%5D=getProgramInappropriateReportsCount&filter%5B_page%5D=1&filter%5B_per_page%5D=32&_list_mode=list"
    And I wait for the page to be loaded
    Then I should see the reported table:
      | #Reported Comments | #Reported Programs | Username | Email               |
      | 3                  | 2                  | Gregor   | dev2@pocketcode.org |
    And I should not see "Adminius"
    And I should not see "Superman"