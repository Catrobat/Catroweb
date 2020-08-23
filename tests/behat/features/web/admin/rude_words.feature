@admin
Feature: Admin rude words

  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org |  0 |
    And there are rude words:
      | word  |
      | test  |
      | word  |
      | test2 |

  Scenario: List all rude words:
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/rudeword/list"
    And I wait for the page to be loaded
    Then I should see the following rude words:
      | Id | Word  | Action     |
      | 1  | test  | Edit       |
      | 2  | word  | Edit       |
      | 3  | test2 | Edit       |

  Scenario: List words sorted by Word
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/rudeword/list"
    And I wait for the page to be loaded
    And I click on the column with the name "Word"
    And I wait for the page to be loaded
    Then I should see the following rude words:
      | Id | Word  | Action     |
      | 1  | test  | Edit       |
      | 3  | test2 | Edit       |
      | 2  | word  | Edit       |

  Scenario: Filter words by word using filter options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/rudeword/list"
    And I wait for the page to be loaded
    Then I am on "/admin/rudeword/list?filter%5Bword%5D%5Btype%5D=&filter%5Bword%5D%5Bvalue%5D=test&filter%5B_page%5D=1&filter%5B_sort_by%5D=word&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
    Then I should see the following rude words:
      | Id | Word  | Action     |
      | 3  | test2 | Edit       |
      | 1  | test  | Edit       |

  Scenario: Clicking on the show button should take me to the page with program details
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/rudeword/list"
    And I wait for the page to be loaded
    And I click on the edit button of word with id "3"
    Then I should be on "/admin/rudeword/3/edit"
    And I should see "Update"
    And I should see "Update and close"
    And I should see "Delete"

  Scenario: Add a rude word
    Given I log in as "Admin" with the password "123456"
    And I try to add the following word "word2"
    Then I am on "/admin/rudeword/list"

  Scenario: Adding an existing rude word is not allowed
    Given I log in as "Admin" with the password "123456"
    And I try to add the following word "test"
    Then I should be on "/admin/rudeword/create"
