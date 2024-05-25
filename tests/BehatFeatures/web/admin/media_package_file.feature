@admin
Feature: Media Package Files
  It should be possible to list all media package files

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |
    And there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org | 2  |
      | Gregor   | 123456   | dddddddddd | dev2@pocketcode.org | 3  |
    And there are media packages:
      | id | name   | name_url |
      | 1  | Looks  | looks    |
      | 2  | Sounds | sounds   |
    And there are media package categories:
      | id | name       | package |
      | 1  | category 1 | Looks   |
      | 2  | category 2 | Looks   |
      | 3  | category 3 | Sounds  |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
      | 3  | arduino    |
    And there are media package files:
      | id | category   | name   | extension | file   | active | downloads | flavors          | author   |
      | 1  | category 1 | File 1 | png       | 1.png  | 1      | 1         | pocketcode, luna | Adminius |
      | 2  | category 1 | File 2 | mpga      | 2.mpga | 0      | 2         | luna             | Superman |
      | 3  | category 2 | File 3 | png       | 3.png  | 1      | 3         | pocketcode       | Superman |
      | 4  | category 2 | File 4 | png       | 4.png  | 1      | 4         | pocketcode       | Gregor   |
      | 5  | category 3 | File 5 | png       | 5.png  | 1      | 5         | luna             | Gregor   |

  Scenario: List media package files sorted by ID
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/media_package_file/list"
    And I wait for the page to be loaded
    Then I should see the media package files table:
      | Id | Name   | Category   | Author   | Flavors          | Downloads | Active |
      | 1  | File 1 | category 1 | Adminius | pocketcode, luna | 1         | 1      |
      | 2  | File 2 | category 1 | Superman | luna             | 2         | 0      |
      | 3  | File 3 | category 2 | Superman | pocketcode       | 3         | 1      |
      | 4  | File 4 | category 2 | Gregor   | pocketcode       | 4         | 1      |
      | 5  | File 5 | category 3 | Gregor   | luna             | 5         | 1      |

  Scenario: List media package files sorted by downloads
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/media_package_file/list?filter%5B_sort_order%5D=ASC&filter%5B_sort_by%5D=downloads&filter%5B_page%5D=1&filter%5B_per_page%5D=32&_list_mode=list"
    And I wait for the page to be loaded
    Then I should see the media package files table:
      | Id | Name   | Category   | Author   | Flavors          | Downloads | Active |
      | 5  | File 5 | category 3 | Gregor   | luna             | 5         | 1      |
      | 4  | File 4 | category 2 | Gregor   | pocketcode       | 4         | 1      |
      | 3  | File 3 | category 2 | Superman | pocketcode       | 3         | 1      |
      | 2  | File 2 | category 1 | Superman | luna             | 2         | 0      |
      | 1  | File 1 | category 1 | Adminius | pocketcode, luna | 1         | 1      |

  Scenario: Delete first media package file
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/media_package_file/list"
    And I wait for the page to be loaded
    Then I should see the media package files table:
      | Id | Name   | Category   | Author   | Flavors          | Downloads | Active |
      | 1  | File 1 | category 1 | Adminius | pocketcode, luna | 1         | 1      |
      | 2  | File 2 | category 1 | Superman | luna             | 2         | 0      |
      | 3  | File 3 | category 2 | Superman | pocketcode       | 3         | 1      |
      | 4  | File 4 | category 2 | Gregor   | pocketcode       | 4         | 1      |
      | 5  | File 5 | category 3 | Gregor   | luna             | 5         | 1      |
    Then I am on "/admin/media_package_file/1/delete"
    And I wait for the page to be loaded
    Then I click on the first ".btn-danger" button
    And I wait for the page to be loaded
    Then I should see the media package files table:
      | Id | Name   | Category   | Author   | Flavors    | Downloads | Active |
      | 2  | File 2 | category 1 | Superman | luna       | 2         | 0      |
      | 3  | File 3 | category 2 | Superman | pocketcode | 3         | 1      |
      | 4  | File 4 | category 2 | Gregor   | pocketcode | 4         | 1      |
      | 5  | File 5 | category 3 | Gregor   | luna       | 5         | 1      |
    And I should not see "Adminius"

  Scenario: Adding a media package file
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/media_package_file/list"
    And I wait for the page to be loaded
    Then I should see the media package files table:
      | Id | Name   | Category   | Author   | Flavors          | Downloads | Active |
      | 1  | File 1 | category 1 | Adminius | pocketcode, luna | 1         | 1      |
      | 2  | File 2 | category 1 | Superman | luna             | 2         | 0      |
      | 3  | File 3 | category 2 | Superman | pocketcode       | 3         | 1      |
      | 4  | File 4 | category 2 | Gregor   | pocketcode       | 4         | 1      |
      | 5  | File 5 | category 3 | Gregor   | luna             | 5         | 1      |
    And I click on the "new" link
    And I wait for the page to be loaded
    Then I should be on "/admin/media_package_file/create"
    When I attach the avatar "galaxy.jpg" to "File"
    Then I write "New file" in textarea with label "Name"
    Then I write "Adminius" in textarea with label "Author"
    Then I select flavor "pocketcode" for media package file
    Then I select flavor "luna" for media package file
    Then I click ".btn-success"
    And I go to "/admin/media_package_file/list"
    And I wait for the page to be loaded
    Then I should see the media package files table:
      | Id | Name     | Category   | Author   | Flavors          | Downloads | Active |
      | 1  | File 1   | category 1 | Adminius | pocketcode, luna | 1         | 1      |
      | 2  | File 2   | category 1 | Superman | luna             | 2         | 0      |
      | 3  | File 3   | category 2 | Superman | pocketcode       | 3         | 1      |
      | 4  | File 4   | category 2 | Gregor   | pocketcode       | 4         | 1      |
      | 5  | File 5   | category 3 | Gregor   | luna             | 5         | 1      |
      | 6  | New file | category 1 | Adminius | pocketcode, luna | 0         | 1      |