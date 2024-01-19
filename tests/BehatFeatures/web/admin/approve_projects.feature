@admin
Feature: Admin approve programs

  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |

    And there are users:
      | name     | password | token      | email               | id |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org | 2  |
      | Catroweb | 123456   | dddddddddd | dev2@pocketcode.org | 3  |
      | User1    | 123456   | qwertyuiop | dev3@pocketcode.org | 4  |

    And there are projects:
      | id | name      | description  | owned by | upload time      | version | language version | visible |
      | 1  | program 1 | description1 | Catrobat | 01.01.2014 12:00 | 0.8.5   | 0.94             | true    |
      | 2  | program 2 | description2 | Catroweb | 22.04.2014 14:00 | 0.8.5   | 0.93             | true    |
      | 3  | program 3 | description3 | Catroweb | 22.04.2014 14:30 | 0.8.5   | 0.93             | true    |
      | 4  | program 4 |              | Catrobat | 22.04.2014 14:15 | 0.8.5   | 0.93             | true    |
      | 5  | program 5 | description2 | User1    | 22.04.2014 14:00 | 0.8.5   | 0.93             | false   |


  Scenario: List all programs:
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I should see the following not approved projects:
      | Upload Time           | Id | User     | Name      | Visible | Approved | Action |
      | January 1, 2014 12:00 | 1  | Catrobat | program 1 | yes     | no       | Show   |
      | April 22, 2014 14:00  | 2  | Catroweb | program 2 | yes     | no       | Show   |
      | April 22, 2014 14:30  | 3  | Catroweb | program 3 | yes     | no       | Show   |
      | April 22, 2014 14:15  | 4  | Catrobat | program 4 | yes     | no       | Show   |
      | April 22, 2014 14:00  | 5  | User1    | program 5 | no      | no       | Show   |


  Scenario: List projects sorted by upload date ascending
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    And I click on the column with the name "Upload Time"
    And I wait for the page to be loaded
    Then I should see the following not approved projects:
      | Upload Time           | Id | User     | Name      | Visible | Approved | Action |
      | January 1, 2014 12:00 | 1  | Catrobat | program 1 | yes     | no       | Show   |
      | April 22, 2014 14:00  | 2  | Catroweb | program 2 | yes     | no       | Show   |
      | April 22, 2014 14:00  | 5  | User1    | program 5 | no      | no       | Show   |
      | April 22, 2014 14:15  | 4  | Catrobat | program 4 | yes     | no       | Show   |
      | April 22, 2014 14:30  | 3  | Catroweb | program 3 | yes     | no       | Show   |

  Scenario: Filter projects by upload date using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I am on "/admin/approve/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=Apr+22%2C+2014%2C+2%3A15%3A00+pm&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=Apr+22%2C+2014%2C+2%3A30%3A00+pm&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    Then I should see the following not approved projects:
      | Upload Time          | Id | User     | Name      | Visible | Approved | Action |
      | April 22, 2014 14:30 | 3  | Catroweb | program 3 | yes     | no       | Show   |
      | April 22, 2014 14:15 | 4  | Catrobat | program 4 | yes     | no       | Show   |
    And I should not see "program 1"
    And I should not see "program 2"
    And I should not see "program 5"


  Scenario: Filter projects by user using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I am on "/admin/approve/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=Catroweb&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    Then I should see the following not approved projects:
      | Upload Time          | Id | User     | Name      | Visible | Approved | Action |
      | April 22, 2014 14:00 | 2  | Catroweb | program 2 | yes     | no       | Show   |
      | April 22, 2014 14:30 | 3  | Catroweb | program 3 | yes     | no       | Show   |
    And I should not see "program 1"
    And I should not see "program 4"
    And I should not see "program 5"

  Scenario: Filter projects by name using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I am on "/admin/approve/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=program+1&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    Then I should see the following not approved projects:
      | Upload Time           | Id | User     | Name      | Visible | Approved | Action |
      | January 1, 2014 12:00 | 1  | Catrobat | program 1 | yes     | no       | Show   |
    And I should not see "program 2"
    And I should not see "program 3"
    And I should not see "program 4"
    And I should not see "program 5"

  Scenario: Filter projects by id using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I am on "/admin/approve/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=2&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    Then I should see the following not approved projects:
      | Upload Time          | Id | User     | Name      | Visible | Approved | Action |
      | April 22, 2014 14:00 | 2  | Catroweb | program 2 | yes     | no       | Show   |
    And I should not see "program 1"
    And I should not see "program 3"
    And I should not see "program 4"
    And I should not see "program 5"

  Scenario: Admin should be able to change the visibility of the program
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    And I change the visibility of the project number "5" in the approve list to "yes"
    Then I am on "/app"
    And I wait for the page to be loaded
    Then I should see "program 5"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I should see "program 5"

  Scenario: Admin should be able to change if project is approved
    Given I log in as "Admin" with the password "123456"
    Given I am on "/admin/approve/list"
    And I wait for the page to be loaded
    And I wait for the page to be loaded
    And I change the approval of the project number "2" in the approve list to "yes"
    And I am on "/app"
    And I wait for the page to be loaded
    Then I should see "program 2"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    Then I should not see "program 2"


  Scenario: Clicking on the user should take me to the page where I can edit user information
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/list"
    And I wait for the page to be loaded
    And I click on the username "Catroweb"
    Then I should be on "/admin/app/db/user-user/3/show"
    And I should see "Catroweb"
    And I should see "Email"


  Scenario: Clicking on the show button should take me to the page with program details
    Given I log in as "Admin" with the password "123456"
    Given I am on "/admin/approve/list"
    And I wait for the page to be loaded
    And I click on the show button of project with id "3" in the approve list
    Then I should be on "/admin/approve/3/show"
    And I should see "program 3"
    And I should see "description3"
    And I should see "Catroweb"
    And I should see "Images"
    And I should see "Sounds"
    And I should see "Objects"


  Scenario: Clicking on the project name should take me to edit page of the project
    Given I log in as "Admin" with the password "123456"
    Given I am on "/admin/approve/list"
    And I wait for the page to be loaded
    And I click on the project name "program 5"
    And I wait for the page to be loaded
    Then I should be on "/admin/approve/5/show"
    And I should see "Approve Projects"
    And I should see "program 5"
    And I should see "User"
    And I should see "User1"
    And I should see "Update"

  Scenario: Clicking on the code view button should take me to the code view page
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/approve/5/show"
    And I wait for the page to be loaded
    And I click on the code view button
    Then I should be on "/app/project/5/code_view"

