@admin
Feature: Admin featured programs
  It should be possible to list all users, sort and filter etc. The created_at column does not work while creation of the users. They are updated afterwards.

  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Edmin | 123456   | eeeeeeeeee | admin@pocketcode.org | 0  |

    And there are users:
      | name      | password | token      | email               | id | enabled |
      | Superman  | 123456   | cccccccccc | dev1@pocketcode.org | 1  | true    |
      | Gregor    | 123456   | dddddddddd | dev2@pocketcode.org | 2  | false   |
      | Frank Jr. | 123456   | qwertyuiop | dev3@pocketcode.org | 3  | true    |

    And the users are created at:
      | name      | created_at          |
      | Edmin     | 2011-11-11 11:11:11 |
      | Superman  | 2012-11-11 11:11:11 |
      | Gregor    | 2013-11-11 11:11:11 |
      | Frank Jr. | 2014-11-11 11:11:11 |

  Scenario: List all users:
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/app/user/list"
    And I wait for the page to be loaded
    Then I should see the user table:
      | username  | email                | groups | enabled | createdAt               |
      | Edmin     | admin@pocketcode.org |        | 1       | November 11, 2011 11:11 |
      | Superman  | dev1@pocketcode.org  |        | 1       | November 11, 2012 11:11 |
      | Gregor    | dev2@pocketcode.org  |        | 0       | November 11, 2013 11:11 |
      | Frank Jr. | dev3@pocketcode.org  |        | 1       | November 11, 2014 11:11 |

  Scenario: List users created between 01.01.2012 and 01.01.2014
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/app/user/list?filter%5Busername%5D%5Btype%5D=&filter%5Busername%5D%5Bvalue%5D=&filter%5Bemail%5D%5Btype%5D=&filter%5Bemail%5D%5Bvalue%5D=&filter%5Bgroups%5D%5Btype%5D=&filter%5Bgroups%5D%5Bvalue%5D=&filter%5Benabled%5D%5Btype%5D=&filter%5Benabled%5D%5Bvalue%5D=&filter%5BcreatedAt%5D%5Btype%5D=&filter%5BcreatedAt%5D%5Bvalue%5D%5Bstart%5D=01.01.2012%2C+10%3A28%3A15&filter%5BcreatedAt%5D%5Bvalue%5D%5Bend%5D=01.01.2014%2C+10%3A28%3A04&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the user table:
      | username | email               | groups | enabled | createdAt               |
      | Superman | dev1@pocketcode.org |        | 1       | November 11, 2012 11:11 |
      | Gregor   | dev2@pocketcode.org |        | 0       | November 11, 2013 11:11 |

  Scenario: List enabled users
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/app/user/list?filter%5Busername%5D%5Btype%5D=&filter%5Busername%5D%5Bvalue%5D=&filter%5Bemail%5D%5Btype%5D=&filter%5Bemail%5D%5Bvalue%5D=&filter%5Bgroups%5D%5Btype%5D=&filter%5Bgroups%5D%5Bvalue%5D=&filter%5Benabled%5D%5Btype%5D=&filter%5Benabled%5D%5Bvalue%5D=1&filter%5BcreatedAt%5D%5Btype%5D=&filter%5BcreatedAt%5D%5Bvalue%5D%5Bstart%5D=&filter%5BcreatedAt%5D%5Bvalue%5D%5Bend%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
    And I wait for the page to be loaded
    Then I should see the user table:
      | username  | email                | groups | enabled | createdAt               |
      | Edmin     | admin@pocketcode.org |        | 1       | November 11, 2011 11:11 |
      | Superman  | dev1@pocketcode.org  |        | 1       | November 11, 2012 11:11 |
      | Frank Jr. | dev3@pocketcode.org  |        | 1       | November 11, 2014 11:11 |
    And I should not see "Gregor"

  Scenario: Delete User
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/app/user/list"
    And I wait for the page to be loaded
    And I click on the "Superman" link
    And I wait for the page to be loaded
    Then I click on the first ".btn-danger" button
    And I wait for the page to be loaded
    Then I click on the first ".btn-danger" button
    And I wait for the page to be loaded
    Then I should see the user table:
      | username  | email                | groups | enabled | createdAt               |
      | Edmin     | admin@pocketcode.org |        | 1       | November 11, 2011 11:11 |
      | Gregor    | dev2@pocketcode.org  |        | 0       | November 11, 2013 11:11 |
      | Frank Jr. | dev3@pocketcode.org  |        | 1       | November 11, 2014 11:11 |
    And I should see "Item \"Superman\" has been deleted successfully"


  Scenario: Editing a user
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/app/user/list"
    And I wait for the page to be loaded
    Then I should see the user table:
      | username  | email                | groups | enabled | createdAt               |
      | Edmin     | admin@pocketcode.org |        | 1       | November 11, 2011 11:11 |
      | Superman  | dev1@pocketcode.org  |        | 1       | November 11, 2012 11:11 |
      | Gregor    | dev2@pocketcode.org  |        | 0       | November 11, 2013 11:11 |
      | Frank Jr. | dev3@pocketcode.org  |        | 1       | November 11, 2014 11:11 |
    And I click on the "Superman" link
    And I wait for the page to be loaded
    Then I should see "Edit \"Superman\""
    Then I write "Supergirl" in textarea with label "Username"
    Then I click on the button named "btn_update_and_list"
    And I wait for the page to be loaded
    Then I should see the user table:
      | username  | email                | groups | enabled | createdAt               |
      | Edmin     | admin@pocketcode.org |        | 1       | November 11, 2011 11:11 |
      | Supergirl | dev1@pocketcode.org  |        | 1       | November 11, 2012 11:11 |
      | Gregor    | dev2@pocketcode.org  |        | 0       | November 11, 2013 11:11 |
      | Frank Jr. | dev3@pocketcode.org  |        | 1       | November 11, 2014 11:11 |
    And I should not see "Superman"

  Scenario: Disable user
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/app/user/1/edit?_tab=tab_s5ebe49e9bfdfd_282749955_2"
    And I wait for the page to be loaded
    Then I click ".iCheck-helper"
    And I wait for the page to be loaded
    And I click on the button named "btn_update_and_list"
    Then I should see the user table:
      | username  | email                | groups | enabled | createdAt               |
      | Edmin     | admin@pocketcode.org |        | 1       | November 11, 2011 11:11 |
      | Superman  | dev1@pocketcode.org  |        | 0       | November 11, 2012 11:11 |
      | Gregor    | dev2@pocketcode.org  |        | 0       | November 11, 2013 11:11 |
      | Frank Jr. | dev3@pocketcode.org  |        | 1       | November 11, 2014 11:11 |


