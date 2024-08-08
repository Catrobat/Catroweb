@web @project_page
Feature: Reactions to projects "likes"

  Background:
    Given there are users:
      | id | name       |
      | 1  | Catrobat   |
      | 2  | OtherUser  |
      | 3  | LovelyUser |

    And there are projects:
      | id | name     | description | owned by  | upload time      |
      | 1  | Minions  | p1          | Catrobat  | 01.01.2013 12:00 |
      | 2  | Minimies | p2          | Catrobat  | 01.01.2013 12:00 |
      | 3  | otherPro | p3          | OtherUser | 01.01.2013 12:00 |

    And there are project reactions:
      | project | user       | type      |
      | 2       | OtherUser  | smile     |
      | 2       | OtherUser  | thumbs_up |
      | 3       | Catrobat   | wow       |
      | 3       | LovelyUser | love      |
      | 3       | OtherUser  | love      |

  Scenario: Thumbs up button with on-click bubble should appear for projects without reactions
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#project-like-buttons-small" should be visible
    And I should see 1 "#project-like-buttons-small > *"
    And the element "#project-like-buttons-small .thumbs-up" should be visible
    And the element "#project-like-counter-small" should not be visible
    And the element "#project-like-detail-small" should not be visible
    When I click "#project-like-buttons-small .thumbs-up"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small" should be visible
    And I should see 4 "#project-like-detail-small > .btn"
    # bubble should disappear if I click anywhere
    And I click "body"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small" should not be visible

  Scenario: Thumbs up and smile button with on-click bubble and counter should appear for project 2
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#project-like-buttons-small" should be visible
    And I should see 2 "#project-like-buttons-small > *"
    And the element "#project-like-buttons-small .thumbs-up" should be visible
    And the element "#project-like-buttons-small .smile" should be visible
    And the element "#project-like-counter-small" should be visible
    And the "#project-like-counter-small" element should contain "2"
    And the element "#project-like-detail-small" should not be visible
    When I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small" should be visible
    And I should see 4 "#project-like-detail-small > .btn"

  Scenario: Detail dialog for project 2 should open and show reactions
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#project-like-modal" should not be visible
    And I click "#project-like-counter-small"
    And I wait for AJAX to finish
    Then the element "#project-like-modal" should be visible
    And I should see 3 "#project-like-modal .modal-body .nav-tabs .nav-item"
    And the "#all-tab > span" element should contain "1"
    And the "#thumbs-up-tab > span" element should contain "1"
    And the "#smile-tab > span" element should contain "1"
    And the element "#love-tab" should not be visible
    And the element "#wow-tab" should not be visible
    And the element "#all-tab-content" should be visible
    And the element "#thumbs-up-tab-content" should not be visible
    And the element "#smile-tab-content" should not be visible
    And the element "#love-tab-content" should not be visible
    And the element "#wow-tab-content" should not be visible
    And I should see 1 "#all-tab-content .reaction"
    And the "#all-tab-content .reaction:first-child a" element should contain "OtherUser"
    And I should see 2 "#all-tab-content .reaction:first-child .types > i"
    And the element "#all-tab-content .reaction:first-child .types .smile" should be visible
    And the element "#all-tab-content .reaction:first-child .types .thumbs-up" should be visible
    When I click "#smile-tab"
    And I wait for AJAX to finish
    Then the element "#smile-tab-content" should be visible
    And the element "#all-tab-content" should not be visible
    And I should see 1 "#smile-tab-content .reaction"
    And the "#smile-tab-content .reaction:first-child a" element should contain "OtherUser"
    And I should see 2 "#smile-tab-content .reaction:first-child .types > i"
    And the element "#smile-tab-content .reaction:first-child .types .smile" should be visible
    And the element "#smile-tab-content .reaction:first-child .types .thumbs-up" should be visible
    And I click "#project-like-modal button.btn-close"
    And I wait for AJAX to finish
    Then the element "#project-like-modal" should not be visible

  Scenario: Detail dialog for project 3 should open and show reactions
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then the element "#project-like-modal" should not be visible
    And the ".like-counter" element should contain "3"
    And I click ".like-counter"
    And I wait for AJAX to finish
    Then the element "#project-like-modal" should be visible
    And I should see 3 "#project-like-modal .modal-body .nav-tabs .nav-item"
    And the "#all-tab > span" element should contain "3"
    And the "#love-tab > span" element should contain "2"
    And the "#wow-tab > span" element should contain "1"
    And the element "#thumbs-up-tab" should not be visible
    And the element "#smile-tab" should not be visible
    And the element "#all-tab-content" should be visible
    And the element "#thumbs-up-tab-content" should not be visible
    And the element "#smile-tab-content" should not be visible
    And the element "#love-tab-content" should not be visible
    And the element "#wow-tab-content" should not be visible
    And I should see 3 "#all-tab-content .reaction"
    And the "#all-tab-content .reaction:first-child a" element should contain "Catrobat"
    And I should see 1 "#all-tab-content .reaction:first-child .types > img"
    And the element "#all-tab-content .reaction:first-child .types .wow" should be visible
    And the "#all-tab-content .reaction:nth-child(3) a" element should contain "LovelyUser"
    And I should see 1 "#all-tab-content .reaction:nth-child(3) .types > i"
    And the element "#all-tab-content .reaction:nth-child(3) .types .love" should be visible
    When I click "#love-tab"
    And I wait for AJAX to finish
    Then the element "#love-tab-content" should be visible
    And the element "#all-tab-content" should not be visible
    And I should see 2 "#love-tab-content .reaction"
    And the "#love-tab-content" element should contain "OtherUser"
    And the "#love-tab-content" element should contain "LovelyUser"
    And the element "#love-tab-content .reaction:nth-child(1) .types .love" should be visible
    And the element "#love-tab-content .reaction:nth-child(2) .types .love" should be visible

  Scenario: A users reactions to a project should be marked
    Given I log in as "OtherUser"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    And I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small" should be visible
    And I should see 4 "#project-like-detail-small > .btn"
    And I should see 2 "#project-like-detail-small > .btn.active"
    And I should see 2 "#project-like-detail-small > .btn:not(.active)"
    And the element "#project-like-detail-small > .btn.active[data-like-type=1]" should exist
    And the element "#project-like-detail-small > .btn.active[data-like-type=2]" should exist
    And the element "#project-like-detail-small > .btn.active[data-like-type=3]" should not exist
    And the element "#project-like-detail-small > .btn.active[data-like-type=4]" should not exist

  Scenario: A logged-in user should be able to give several reactions to a single project and counter + icons should be refreshed
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the "#project-like-counter-small" element should contain "0"
    And I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small" should be visible
    When I click "#project-like-detail-small .btn[data-like-type=4]"
    And I wait for AJAX to finish
    And the "#project-like-counter-small" element should contain "1"
    And I should see 1 "#project-like-buttons-small > *"
    And the element "#project-like-buttons-small .wow" should be visible
    When I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    And I click "#project-like-detail-small .btn[data-like-type=2]"
    And I wait for AJAX to finish
    Then the "#project-like-counter-small" element should contain "2"
    And I should see 2 "#project-like-buttons-small > *"
    And the element "#project-like-buttons-small .smile" should be visible
    And the element "#project-like-buttons-small .wow" should be visible
    When I reload the page
    Then the "#project-like-counter-small" element should contain "2"
    And I should see 2 "#project-like-buttons-small > *"
    And the element "#project-like-buttons-small .smile" should be visible
    And the element "#project-like-buttons-small .wow" should be visible
    When I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    And I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    Then the "#project-like-counter-small" element should contain "3"
    And I should see 3 "#project-like-buttons-small > *"
    And the element "#project-like-buttons-small .thumbs-up" should be visible
    And the element "#project-like-buttons-small .smile" should be visible
    And the element "#project-like-buttons-small .wow" should be visible

  Scenario: Guests should be redirected to login page if they react to a project and the reaction should count after login
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And the "#project-like-counter-small" element should contain "0"
    And I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    # click on Smile button
    And I click "#project-like-detail-small .btn:nth-child(2)"
    And I wait for AJAX to finish
    Then I should be on "/app/login"
    Then I fill in "OtherUser" for "_username"
    And I fill in "123456" for "_password"
    Then I press "Login"
    And I wait for AJAX to finish
    Then I should be logged in
    And the "#project-like-counter-small" element should contain "1"
    And the element "#project-like-buttons-small .smile" should be visible

  Scenario: Many user reactions with correct reaction count
    Given there are 100 additional users
    And there are project reactions:
      | project | user    | type      |
      | 1       | User100 | thumbs_up |
      | 1       | User101 | thumbs_up |
      | 1       | User102 | thumbs_up |
      | 1       | User103 | thumbs_up |
      | 1       | User104 | thumbs_up |
      | 1       | User105 | thumbs_up |
      | 1       | User106 | thumbs_up |
      | 1       | User107 | thumbs_up |
      | 1       | User108 | thumbs_up |
      | 1       | User109 | thumbs_up |
      | 1       | User110 | thumbs_up |
      | 1       | User111 | thumbs_up |
      | 1       | User112 | thumbs_up |
      | 1       | User113 | thumbs_up |
      | 1       | User114 | thumbs_up |
      | 1       | User115 | thumbs_up |
      | 1       | User116 | thumbs_up |
      | 1       | User117 | thumbs_up |
      | 1       | User118 | thumbs_up |
      | 1       | User119 | thumbs_up |
      | 1       | User120 | thumbs_up |
      | 1       | User121 | thumbs_up |
      | 1       | User122 | thumbs_up |
      | 1       | User123 | thumbs_up |
      | 1       | User124 | thumbs_up |
      | 1       | User125 | thumbs_up |
      | 1       | User126 | thumbs_up |
      | 1       | User127 | thumbs_up |
      | 1       | User128 | thumbs_up |
      | 1       | User129 | thumbs_up |
      | 1       | User130 | thumbs_up |
      | 1       | User131 | thumbs_up |
      | 1       | User132 | thumbs_up |
      | 1       | User133 | thumbs_up |
      | 1       | User134 | thumbs_up |
      | 1       | User135 | thumbs_up |
      | 1       | User136 | thumbs_up |
      | 1       | User137 | thumbs_up |
      | 1       | User138 | thumbs_up |
      | 1       | User139 | thumbs_up |
      | 1       | User140 | thumbs_up |
      | 1       | User141 | thumbs_up |
      | 1       | User142 | thumbs_up |
      | 1       | User143 | thumbs_up |
      | 1       | User144 | thumbs_up |
      | 1       | User145 | thumbs_up |
      | 1       | User146 | thumbs_up |
      | 1       | User147 | thumbs_up |
      | 1       | User148 | thumbs_up |
      | 1       | User149 | thumbs_up |
      | 1       | User150 | thumbs_up |
      | 1       | User151 | thumbs_up |
      | 1       | User152 | thumbs_up |
      | 1       | User153 | thumbs_up |
      | 1       | User154 | thumbs_up |
      | 1       | User155 | thumbs_up |
      | 1       | User156 | thumbs_up |
      | 1       | User157 | thumbs_up |
      | 1       | User158 | thumbs_up |
      | 1       | User159 | thumbs_up |
      | 1       | User160 | thumbs_up |
      | 1       | User161 | thumbs_up |
      | 1       | User162 | thumbs_up |
      | 1       | User163 | thumbs_up |
      | 1       | User164 | thumbs_up |
      | 1       | User165 | thumbs_up |
      | 1       | User166 | thumbs_up |
      | 1       | User167 | thumbs_up |
      | 1       | User168 | thumbs_up |
      | 1       | User169 | thumbs_up |
      | 1       | User170 | thumbs_up |
      | 1       | User171 | thumbs_up |
      | 1       | User172 | thumbs_up |
      | 1       | User173 | thumbs_up |
      | 1       | User174 | thumbs_up |
      | 1       | User175 | thumbs_up |
      | 1       | User176 | thumbs_up |
      | 1       | User177 | thumbs_up |
      | 1       | User178 | thumbs_up |
      | 1       | User179 | thumbs_up |
      | 1       | User180 | thumbs_up |
      | 1       | User181 | thumbs_up |
      | 1       | User182 | thumbs_up |
      | 1       | User183 | thumbs_up |
      | 1       | User184 | thumbs_up |
      | 1       | User185 | thumbs_up |
      | 1       | User186 | thumbs_up |
      | 1       | User187 | thumbs_up |
      | 1       | User188 | thumbs_up |
      | 1       | User189 | thumbs_up |
      | 1       | User190 | thumbs_up |
      | 1       | User191 | thumbs_up |
      | 1       | User192 | thumbs_up |
      | 1       | User193 | thumbs_up |
      | 1       | User194 | thumbs_up |
      | 1       | User195 | thumbs_up |
      | 1       | User196 | thumbs_up |
      | 1       | User197 | thumbs_up |
      | 1       | User198 | thumbs_up |
      | 1       | User199 | thumbs_up |
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the "#project-like-counter-small" element should contain "100"
    When I click "#project-like-counter-small"
    And I wait for AJAX to finish
    Then I should see 2 "#project-like-modal .modal-body .nav-tabs .nav-item"
    And the "#all-tab > span" element should contain "100"
    And the "#thumbs-up-tab > span" element should contain "100"
    And the "#smile-tab > span" element should contain "0"
    And the element "#love-tab" should not be visible
    And the element "#wow-tab" should not be visible
    And the element "#all-tab-content" should be visible
    And the element "#thumbs-up-tab-content" should not be visible
    And I should see 100 "#all-tab-content .reaction"
    And I should see 1 "#all-tab-content .reaction:first-child .types > i"
    And the element "#all-tab-content .reaction:first-child .types .thumbs-up" should be visible
    When I click "#thumbs-up-tab"
    And I wait for AJAX to finish
    Then I should see 100 "#thumbs-up-tab-content .reaction"

  Scenario: I should be able to give multiple reactions to a single project and the owner should be notified once
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    And I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    And I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    And I click "#project-like-detail-small .btn[data-like-type=2]"
    And I wait for AJAX to finish
    When I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "1"
    When I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And the element "#all-notif" should be visible
    And the element "#follow-notif" should be visible
    And the element "#reaction-notif" should be visible
    And the element "#comment-notif" should be visible
    And the element "#remix-notif" should be visible
    And the element "#catro-notification-1" should be visible
    And the element "#catro-notification-2" should not exist
    Then I should see text matching "OtherUser reacted to Minions."


  Scenario: I should be able to like multiple projects when I am logged in and it should notify the owner multiple times
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    And I click "#project-like-detail-small .btn[data-like-type=4]"
    And I wait for AJAX to finish
    When I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "2"
    When I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And the element "#all-notif" should be visible
    And the element "#follow-notif" should be visible
    And the element "#reaction-notif" should be visible
    And the element "#comment-notif" should be visible
    And the element "#remix-notif" should be visible
    And the element "#catro-notification-1" should be visible
    And the element "#catro-notification-2" should be visible
    And I should see "OtherUser reacted to Minions."


  Scenario: The notification should disappear if I remove my reaction again
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And the element "#catro-notification-1" should be visible
    And I should see "OtherUser reacted to Minions."
    When I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#project-like-buttons-small"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small > .btn.active[data-like-type=1]" should exist
    When I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    Then the element "#project-like-detail-small > .btn:not(.active)[data-like-type=1]" should exist
    And the element "#project-like-counter-small" should not be visible
    And the "#project-like-counter-small" element should contain "0"
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And the element "#catro-notification-1" should not exist
    Then I should see text matching "It looks like you don't have any notifications."

  Scenario: I can't notify myself
    Given I log in as "OtherUser"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    And I click "#project-like-detail-small .btn[data-like-type=1]"
    And I wait for AJAX to finish
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then the element "#catro-notification-1" should not exist
    And I should not see "OtherUser reacted to otherPro."