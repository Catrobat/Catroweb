@homepage
Feature: Project loader & show more/less programs button behaviour

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | User2    | 654321   | cccccccccc | dev2@pocketcode.org |
      | User3    | 654321   | cccccccccc | dev3@pocketcode.org |
      | User4    | 654321   | cccccccccc | dev4@pocketcode.org |
    And there are programs:
      | id | name       | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | oldestProg | p1          | Catrobat | 3         | 2             | 12    | 01.01.2009 12:00 | 0.8.5   |
      | 2  | program 02 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 03 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | program 04 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | program 05 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | program 06 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7  | program 07 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8  | program 08 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | program 09 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | program 10 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11 | program 11 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12 | program 12 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13 | program 13 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14 | program 14 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15 | program 15 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16 | program 16 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17 | program 17 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18 | program 18 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19 | program 19 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20 | program 20 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21 | program 21 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22 | program 22 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 23 | program 23 |             | User3    | 1         |  1            | 1     | 01.01.2011 13:00 | 0.8.5   |
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
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/pocketcode/search/a"  |

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
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/pocketcode/search/a"  |

  Scenario Outline: The show more Button should disappear when all program are loaded
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
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/pocketcode/search/a"  |

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
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/pocketcode/search/a"  |

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
      | "#search-results .button-show-more" | "#search-results .button-show-less" | "/pocketcode/search/a"  |

  Scenario Outline: Show more Buttons should load more programs, Show less button should hide some programs. There
                    should also be enough projects displayed to make the rows full if possible
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <programs>

    Examples:
      | button                              | programs                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/pocketcode/search/a"  |

  Scenario: All programs should be visible after all of them have been loaded
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
    Then I should see 6 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <programs>
    And the element <button2> should not be visible
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    And the element <button> should not be visible
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <programs>

    Examples:
      | button                              | programs                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/pocketcode/search/a"  |


  Scenario Outline: When a user has loaded more projects and left the page the number of loaded projects should
                    be the same when a user returns to the page.
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I am on "pocketcode/help"
    And I wait 100 milliseconds
    And I am on <page>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I am on "pocketcode/help"
    And I wait 100 milliseconds
    And I am on <page>
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I am on "pocketcode/help"
    And I wait 100 milliseconds
    And I am on <page>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <programs>

    Examples:
      | button                              | programs                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/pocketcode/search/a"  |


  Scenario Outline: When a user has loaded more projects and reloads the page the number of loaded projects should
                    be the same when a user returns to the page.
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    And I wait 100 milliseconds
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I reload the page
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <programs>

    Examples:
      | button                              | programs                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/pocketcode/search/a"  |

  Scenario Outline: When a user has loaded more projects and goes back and forward the number of loaded projects should
                    be the same when a user returns to the page.
    Given I am on <page>
    And I wait 100 milliseconds
    Then I should see 6 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I move backward one page
    And I move forward one page
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    And I wait 100 milliseconds
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button>
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I move backward one page
    And I move forward one page
    And I wait 100 milliseconds
    Then I should see 23 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 18 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I move backward one page
    And I move forward one page
    And I wait 100 milliseconds
    Then I should see 12 <programs>
    When I click <button2>
    And I wait 100 milliseconds
    Then I should see 6 <programs>

    Examples:
      | button                              | programs                   | button2                             | page                    |
      | "#newest .button-show-more"         | "#newest .program"         | "#newest .button-show-less"         | "/"                     |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" | "#mostDownloaded .button-show-less" | "/"                     |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     | "#mostViewed .button-show-less"     | "/"                     |
      | "#random .button-show-more"         | "#random .program"         | "#random .button-show-less"         | "/"                     |
      | "#search-results .button-show-more" | ".program"                 | "#search-results .button-show-less" | "/pocketcode/search/a"  |


