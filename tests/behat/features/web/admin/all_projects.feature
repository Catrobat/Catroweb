@admin
Feature: Admin all projects


  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Admin    | 123456   | eeeeeeeeee | admin@pocketcode.org |  1 |
    And there are users:
      | name     | password | token      | email               | id |
      | Karim    | 123456   | cccccccccc | dev1@pocketcode.org |  2 |
      | Pauli    | 123456   | dddddddddd | dev2@pocketcode.org |  3 |
    And there are downloadable programs:
      | id | name      | description             | owned by | downloads | flavor        | views   | upload time      | visible |
      | 1  | program 1 | a classy program        | Karim    | 3         | pocketcode    | 120     | 01.01.2019 12:00 | true    |
      | 2  | program 2 | abcef                   | Karim    | 123       | luna          | 921     | 02.04.2019 13:00 | true    |
      | 3  | program 3 | description             | Pauli    | 234       | arduino       | 122     | 06.04.2019 13:02 | false   |
      | 4  | program 4 | abcdef                  | Pauli    | 222       | pocketcode    | 12      | 02.04.2019 13:10 | false   |
      | 5  | program 5 | abcd                    | Karim    | 111       | pocketcode    | 15      | 22.04.2019 13:00 | false   |


  Scenario: Al projects should be sorted by upload date descending
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action  |
      | April 22, 2019 13:00   | Karim              | program 5 |  pocketcode | 15     | 111       | no       | no      | Show    |
      | April 6, 2019 13:02    | Pauli              | program 3 |  arduino    | 122    | 234       | no       | no      | Show    |
      | April 2, 2019 13:10    | Pauli              | program 4 |  pocketcode | 12     | 222       | no       | no      | Show    |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show    |
      | January 1, 2019 12:00  | Karim              | program 1 |  pocketcode | 120    | 3         | no       | yes     | Show    |


  Scenario: List all projects sorted by views ascending
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    And I click on the column with the name "Views"
    And I wait for the page to be loaded
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | April 2, 2019 13:10    | Pauli              | program 4 |  pocketcode | 12     | 222       | no       | no      | Show   |
      | April 22, 2019 13:00   | Karim              | program 5 |  pocketcode | 15     | 111       | no       | no      | Show   |
      | January 1, 2019 12:00  | Karim              | program 1 |  pocketcode | 120    | 3         | no       | yes     | Show   |
      | April 6, 2019 13:02    | Pauli              | program 3 |  arduino    | 122    | 234       | no       | no      | Show   |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show   |

  Scenario: List all projects sorted by downloads ascending
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    And I click on the column with the name "Downloads"
    And I wait for the page to be loaded
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | January 1, 2019 12:00  | Karim              | program 1 |  pocketcode | 120    | 3         | no       | yes     | Show   |
      | April 22, 2019 13:00   | Karim              | program 5 |  pocketcode | 15     | 111       | no       | no      | Show   |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show   |
      | April 2, 2019 13:10    | Pauli              | program 4 |  pocketcode | 12     | 222       | no       | no      | Show   |
      | April 6, 2019 13:02    | Pauli              | program 3 |  arduino    | 122    | 234       | no       | no      | Show   |

  Scenario: List all projects sorted by upload date ascending
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    And I click on the column with the name "Upload Time"
    And I wait for the page to be loaded
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | January 1, 2019 12:00  | Karim              | program 1 |  pocketcode | 120    | 3         | no       | yes     | Show   |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show   |
      | April 2, 2019 13:10    | Pauli              | program 4 |  pocketcode | 12     | 222       | no       | no      | Show   |
      | April 6, 2019 13:02    | Pauli              | program 3 |  arduino    | 122    | 234       | no       | no      | Show   |
      | April 22, 2019 13:00   | Karim              | program 5 |  pocketcode | 15     | 111       | no       | no      | Show   |



  Scenario: Filter projects by upload date using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    Then I am on "/admin/all_programs/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=Apr+3%2C+2019%2C+4%3A38%3A58+pm&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=Apr+24%2C+2019%2C+4%3A31%3A40+pm&filter%5B_page%5D=1&filter%5B_sort_by%5D=uploaded_at&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | April 22, 2019 13:00   | Karim              | program 5 |  pocketcode | 15     | 111       | no       | no      | Show   |
      | April 6, 2019 13:02    | Pauli              | program 3 |  arduino    | 122    | 234       | no       | no      | Show   |
    And I should not see "program 1"
    And I should not see "program 2"
    And I should not see "program 4"


  Scenario: Filter projects by name using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    Then I am on "/admin/all_programs/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=program+2&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=uploaded_at&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show   |
    And I should not see "program 1"
    And I should not see "program 5"
    And I should not see "program 4"
    And I should not see "program 3"



  Scenario: Filter projects by username using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    Then I am on "/admin/all_programs/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=Karim&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=uploaded_at&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | April 22, 2019 13:00   | Karim              | program 5 |  pocketcode | 15     | 111       | no       | no      | Show   |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show   |
      | January 1, 2019 12:00  | Karim              | program 1 |  pocketcode | 120    | 3         | no       | yes     | Show   |
    And I should not see "program 3"
    And I should not see "program 4"


  Scenario: Filter projects by id using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    Then I am on "/admin/all_programs/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=4&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=uploaded_at&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | April 2, 2019 13:10    | Pauli              | program 4 |  pocketcode | 12     | 222       | no       | no      | Show   |
    And I should not see "program 1"
    And I should not see "program 2"
    And I should not see "program 3"
    And I should not see "program 5"

  Scenario: Filter projects by flavor using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    Then I am on "/admin/all_programs/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Bname%5D%5Btype%5D=&filter%5Bname%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buploaded_at%5D%5Btype%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bstart%5D=&filter%5Buploaded_at%5D%5Bvalue%5D%5Bend%5D=&filter%5Bflavor%5D%5Btype%5D=&filter%5Bflavor%5D%5Bvalue%5D=luna&filter%5B_page%5D=1&filter%5B_sort_by%5D=uploaded_at&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the table with all projects in the following order:
      | Uploaded At            | User               | Name      |  Flavor     | Views  | Downloads | Approved | Visible | Action |
      | April 2, 2019 13:00    | Karim              | program 2 |  luna       | 921    | 123       | no       | yes     | Show   |
    And I should not see "program 1"
    And I should not see "program 3"
    And I should not see "program 4"
    And I should not see "program 5"



   Scenario: Admin should be able to change the flavor of the project
     Given I log in as "Admin" with the password "123456"
     Given I am on "/admin/all_programs/list"
     And I wait for the page to be loaded
     And I change the flavor of the project number "4" in the list to "arduino"
     And I reload the page
     Then I should see the table with all projects in the following order:
       | Uploaded At            | User               | Name      |  Flavor    | Views  | Downloads | Approved | Visible | Action |
       | April 22, 2019 13:00   | Karim              | program 5 | pocketcode | 15     | 111       | no       | no      | Show   |
       | April 6, 2019 13:02    | Pauli              | program 3 | arduino    | 122    | 234       | no       | no      | Show   |
       | April 2, 2019 13:10    | Pauli              | program 4 | pocketcode | 12     | 222       | no       | no      | Show   |
       | April 2, 2019 13:00    | Karim              | program 2 | arduino    | 921    | 123       | no       | yes     | Show   |
       | January 1, 2019 12:00  | Karim              | program 1 | pocketcode | 120    | 3         | no       | yes     | Show   |


  Scenario: Admin should be able to change if project is approved
    Given I log in as "Admin" with the password "123456"
    Given I am on "/admin/all_programs/list"
    And I wait for the page to be loaded
    And I change the approval of the project number "4" in the list to "yes"
    And I report program 2 with category "spam" and note "Bad Program" in Browser
    And I am on "/app"
    And I wait for the page to be loaded
    Then I should see "program 2"

    Scenario: Change the visibility of the program
      Given I log in as "Admin" with the password "123456"
      And I am on "/app"
      And I wait for the page to be loaded
      Then I should not see "program 3"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      And I change the visibility of the project number "2" in the list to "yes"
      Then I am on "/app"
      And I wait for the page to be loaded
      Then I should see "program 3"

    Scenario: Click on the username should take me to the page where I can edit user info
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      And I click on the username "Pauli"
      Then I should be on "/admin/app/user/3/edit"
      And I should see "Pauli"
      And I should see "E-Mail-Address"

     Scenario: Clicking on the show button should take me to the page with program details
       Given I log in as "Admin" with the password "123456"
       Given I am on "/admin/all_programs/list"
       And I wait for the page to be loaded
       And I click on the show button of the program number "3" in the list
       Then I should be on "/admin/approve/4/show"
       And I should see "program 4"
       And I should see "abcdef"
       And I should see "Pauli"
       And I should see "Images"
       And I should see "Sounds"
       And I should see "Objects"



      Scenario: Clicking on the project name should take me to edit page of the project
        Given I log in as "Admin" with the password "123456"
        Given I am on "/admin/all_programs/list"
        And I wait for the page to be loaded
        And I click on the program name "program 5"
        And I wait for the page to be loaded
        Then I should be on "/admin/all_programs/5/edit"
        And I should see "Description"
        And I should see "program 5"
        And I should see "User"
        And I should see "Downloads"
        And I should see "Views"
        And I should see "Karim"
        And I should see "Update"
