@homepage
Feature: Show more programs button behaviour

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name       | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | oldestProg | p1          | Catrobat | 3         | 2             | 12    | 01.01.2009 12:00 | 0.8.5   |
      | 2  | program 02 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 03 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | program 04 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | program 05 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | program 06 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7  | program 07 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8  | program 08 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | program 09 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | program 10 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11 | program 11 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12 | program 12 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13 | program 13 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14 | program 14 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15 | program 15 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16 | program 16 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17 | program 17 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18 | program 18 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19 | program 19 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20 | program 20 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21 | program 21 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22 | program 22 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 23 | program 23 |             | User1    | 1         |  1            | 1     | 01.01.2011 13:00 | 0.8.5   |

  Scenario Outline: Should see all buttons at homepage
    Given I am on homepage
    Then the element <button> should be visible

    Examples:
      | button                              |
      | "#newest .button-show-more"         |
      | "#mostDownloaded .button-show-more" |
      | "#mostViewed .button-show-more"     |
      | "#random .button-show-more"         |

  @Mobile
  Scenario Outline: Should see all buttons at homepage in mobile format
    Given I am on homepage
    Then the element <button> should be visible

    Examples:
      | button                              |
      | "#newest .button-show-more"         |
      | "#mostDownloaded .button-show-more" |
      | "#mostViewed .button-show-more"     |
      | "#random .button-show-more"         |

  Scenario Outline: Buttons should disappear after clicking them three times (on small phones)
    Given I am on homepage
    Then I wait 300 milliseconds
    When I click <button>
    And I click <button>
    And I click <button>
    Then the element <button> should not be visible

    Examples:
      | button                              |
      | "#newest .button-show-more"         |
      | "#mostDownloaded .button-show-more" |
      | "#mostViewed .button-show-more"     |
      | "#random .button-show-more"         |

  Scenario Outline: Buttons should load more programs
  The number, of how many programs the user should see, should equal the number of programs in the database
  and should be < 37. 36 is the current max number of programs visible after the user clicks the button once.
  Because we test mobile, we press the button three times.
    Given I am on homepage
    And I wait 100 milliseconds
    Then I should see 6 <programs>
    When I click <button>
    And I click <button>
    And I click <button>
    And I wait for a second
    Then I should see 23 <programs>

    Examples:
      | button                              | programs                   |
      | "#newest .button-show-more"         | "#newest .program"         |
      | "#mostDownloaded .button-show-more" | "#mostDownloaded .program" |
      | "#mostViewed .button-show-more"     | "#mostViewed .program"     |
      | "#random .button-show-more"         | "#random .program"         |

  Scenario: All programs should be visible after all of them have been loaded
    Given I am on homepage
    And the random program section is empty
    Then I should not see "oldestProg"
    When I click "#newest .button-show-more"
    When I click "#newest .button-show-more"
    When I click "#newest .button-show-more"
    And I wait for a second
    Then I should see "oldestProg"
