@homepage
Feature: User gets generic notifications additionally to the remix notifications

  Background:
    Given there are users:
      | name      | password | token      | email               |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org |

    And there are catro notifications:
      | user     | title                 | message                                         | type    |
      | Catrobat | Achievement - Uploads | Congratulations, you uploaded your first app    | default |
      | Catrobat | Achievement - View    | Congratulations, you reached a total of 2 views | default |

  Scenario: User views his notifications and sees all of them
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    Then I should see "Achievement - Uploads"
    Then I should see "Achievement - View"

  Scenario: User views his notifications marks one as seen and does not see it anymore
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"

    Then I should see "Achievement - Uploads"
    Then I should see "Achievement - View"

    Then I click on the first ".catro-notification-read" button

    Then I should not see "Achievement - Uploads"
    Then I should see "Achievement - View"

    Then I click on the first ".catro-notification-read" button

    Then I should not see "Achievement - Uploads"
    Then I should not see "Achievement - View"
    Then I should see a ".swal2-modal" element
