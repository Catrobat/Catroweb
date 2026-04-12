@web @studio
Feature: As studio admin I must be able to configure a studio

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
      | 3  | Catrobat1    |
      | 4  | Catrobat2    |
      | 5  | Catrobat3    |
      | 6  | StudioAdmin2    |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
      | 2  | CatrobatStudio03 | hasADescription | true           | false     |
      | 3  | CatrobatStudio04 | hasADescription | true           | false     |
      | 4  | CatrobatStudio05 | hasADescription | true           | false     |

    And there are studio join requests:
      | User      | Studio              | Status   |
      | Catrobat1 | CatrobatStudio03   | pending  |
      | Catrobat3 | CatrobatStudio03   | declined |
      | Catrobat3 | CatrobatStudio04   | pending  |
      | Catrobat3 | CatrobatStudio05   | declined |

    And there are studio users:
      | id                                   | user          | studio_id | role   |
      | 00000000-0000-0000-0000-000000000001 | StudioAdmin   | 1         | admin  |
      | 00000000-0000-0000-0000-000000000002 | Catrobat      | 1         | member |
      | 00000000-0000-0000-0000-000000000003 | Catrobat1     | 2         | member |
      | 00000000-0000-0000-0000-000000000004 | Catrobat2     | 2         | member |
      | 00000000-0000-0000-0000-000000000005 | Catrobat3     | 2         | member |
      | 00000000-0000-0000-0000-000000000006 | StudioAdmin2  | 2         | admin  |
      | 00000000-0000-0000-0000-000000000007 | StudioAdmin2  | 3         | admin  |
      | 00000000-0000-0000-0000-000000000008 | StudioAdmin2  | 4         | admin  |
      | 00000000-0000-0000-0000-000000000009 | Catrobat3     | 3         | member |
      | 00000000-0000-0000-0000-000000000010 | Catrobat3     | 4         | member |


  Scenario: If I am not logged in I must not see the button to open the settings modal
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should not be visible

  Scenario: If I am not the admin of the studio, I must not see the button to open the settings modal
    Given I log in as "Catrobat"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should not be visible

  Scenario: If I am the admin of the studio, I have access to an settings modal
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should be visible
    And the element "#studio-admin-settings-modal" should not be visible
    When I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    Then the element "#studio-settings__submit-button" should be visible

  Scenario: As Studio admin I can change the studio name
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I should see "CatrobatStudio01"
    And the element "#studio-settings__submit-button" should not be visible
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And the element "#studio-settings__submit-button" should be visible
    When I fill in "studio_name" with "CatrobatStudio02"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And the element "#studio-settings__submit-button" should not be visible
    And I should not see "CatrobatStudio01"
    And I should see "CatrobatStudio02"

  Scenario: As Studio admin I can change the studio description
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I should see "hasADescription"
    And the element "#studio-settings__submit-button" should not be visible
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And the element "#studio-settings__submit-button" should be visible
    When I fill in "studio_description" with "hasANewDescription"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And the element "#studio-settings__submit-button" should not be visible
    And I should not see "hasADescription"
    And I should see "hasANewDescription"

  Scenario: The settings modal can be closed without saving
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I should see "CatrobatStudio01"
    And the element "#studio-settings__close-button" should not be visible
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    Then the element "#studio-settings__close-button" should be visible
    When I fill in "studio_name" with "CatrobatStudio02"
    And I click "#studio-settings__close-button"
    And I wait 500 milliseconds
    Then the element "#studio-settings__close-button" should not be visible
    And I should see "CatrobatStudio01"

  Scenario: As Studio admin I can enable and disable comments
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I click "#comments-tab"
    And I wait for the page to be loaded
    Then I should see "This studio has no comments yet"
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And I click "#studio-setting__switch-enable-comments"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I click "#comments-tab"
    And I wait for the page to be loaded
    Then I should not see "This studio has no comments yet"
    Then I should see "Comments have been disabled for this studio"
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And I click "#studio-setting__switch-enable-comments"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I click "#comments-tab"
    And I wait for the page to be loaded
    Then I should see "This studio has no comments yet"
    Then I should not see "Comments have been disabled for this studio"

  Scenario: As Studio admin I can toggle the privacy of a studio
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "public"
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And I click "#studio-setting__switch-studio-privacy"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#header-visibility" to contain "Invite-only"
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    And I click "#studio-setting__switch-studio-privacy"
    And I click "#studio-settings__submit-button"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#header-visibility" to contain "public"

  Scenario:As a public Studio admin, I can not see pending join requests, approved join requests, and declined join requests.
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#header-visibility" to contain "public"
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    Then I should not see "Pending Join Request"
    And  I should not see "Approved Join Requests"
    And  I should not see "Declined Join Requests"

  Scenario: As a private Studio admin, join request management is not shown (feature deferred)
    Given I log in as "StudioAdmin2"
    And I am on "/app/studio/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#header-visibility" to contain "Invite-only"
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-edit-studio"
    And I wait for the element "#studio-admin-settings-modal" to be visible
    Then I should not see "Pending Join Request"
    And I should not see "Approved Join Requests"
    And I should not see "Declined Join Requests"
