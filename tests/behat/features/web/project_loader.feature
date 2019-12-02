@homepage
Feature: Project loader & show more/less programs button behaviour

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | User2    | 654321   | cccccccccc | dev2@pocketcode.org |  2 |
      | User3    | 654321   | cccccccccc | dev3@pocketcode.org |  3 |
      | User4    | 654321   | cccccccccc | dev4@pocketcode.org |  4 |
    And there are programs:
      | id | name       | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | oldestProg | p1          | Catrobat | 3         | 2             | 12    | 01.01.2009 12:00 | 0.8.5   |
      | 2  | project 02 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | project 03 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | project 04 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | project 05 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | project 06 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7  | project 07 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8  | project 08 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | project 09 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | project 10 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11 | project 11 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12 | project 12 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13 | project 13 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14 | project 14 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15 | project 15 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16 | project 16 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17 | project 17 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18 | project 18 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19 | project 19 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20 | project 20 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21 | project 21 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22 | project 22 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 23 | project 23 |             | User3    | 1         |  1            | 1     | 01.01.2011 13:00 | 0.8.5   |
    And I start a new session

  Scenario Outline: Should see all buttons at homepage
    Given I am on <page>
    Then the element <button> should be visible
    And the element <button2> should not be visible

    Examples:
      | button                              | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/app/search/p"  |

  @Mobile
  Scenario Outline: Per default a user should only see the show more buttons
    Given I am on <page>
    Then the element <button> should be visible
    And the element <button2> should not be visible

    Examples:
      | button                              | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/app/search/p"  |

  Scenario Outline: The show more Button should disappear when all project are loaded
    Given I am on <page>
    Then I wait 100 milliseconds
    When I click <button>
    Then the element <button> should be visible
    And the element <button2> should be visible

    Examples:
      | button                              | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/app/search/p"  |

  Scenario Outline: When clicking the show less button often enough the the show less button should disappear
    Given I am on <page>
    Then I wait 100 milliseconds
    When I click <button>
    And I click <button2>
    And I click <button2>
    And I click <button2>
    Then the element <button> should be visible
    And the element <button2> should not be visible

    Examples:
      | button                              | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/app/search/p"  |

  Scenario Outline: When there are more projects to load then both buttons should be visible after clicking show more
    Given I am on <page>
    Then I wait 100 milliseconds
    When I click <button>
    Then the element <button> should be visible
    And the element <button2> should be visible

    Examples:
      | button                              | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/app/search/p"  |

  Scenario Outline: Show more Buttons should load more projects, Show less button should hide some projects. There
                    should also be enough projects displayed to make the rows full if possible
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <projects>

    Examples:
      | button                              | projects                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/app/search/p"  |

  Scenario: All projects should be visible after all of them have been loaded
    Given I am on homepage
    And the random program section is empty
    Then I should not see "oldestProg"
    When I click "#newest .button-show-more"
    And I click "#newest .button-show-more"
    And I click "#newest .button-show-more"
    And I wait for a second
    Then I should see "oldestProg"

  Scenario Outline: Checking random show more / less clicks
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <projects>
    And the element <button2> should not be visible
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    And the element <button> should not be visible
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <projects>

    Examples:
      | button                              | projects                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/app/search/p"  |


  Scenario Outline: When a user has loaded more projects and left the page the number of loaded projects should
                    be the same when a user returns to the page.
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I am on "app/help"
    And I wait 100 milliseconds
    And I am on <page>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I am on "app/help"
    And I wait 100 milliseconds
    And I am on <page>
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I am on "app/help"
    And I wait 100 milliseconds
    And I am on <page>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <projects>

    Examples:
      | button                              | projects                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/app/search/p"  |


  Scenario Outline: When a user has loaded more projects and reloads the page the number of loaded projects should
                    be the same when a user returns to the page.
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    And I wait 100 milliseconds
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <projects>

    Examples:
      | button                              | projects                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/app/search/p"  |

  Scenario Outline: When a user has loaded more projects and goes back and forward the number of loaded projects should
                    be the same when a user returns to the page.
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I move backward one page
    And I move forward one page
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    And I wait 100 milliseconds
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I move backward one page
    And I move forward one page
    And I wait 100 milliseconds
    Then I should see 23 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I move backward one page
    And I move forward one page
    And I wait 100 milliseconds
    Then I should see 12 <projects>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <projects>

    Examples:
      | button                              | projects                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/app/search/p"  |


