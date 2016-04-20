@homepage
Feature: Show more programs button behaviour

  Background:
    Given there are users:
      | name     | password  | token      | email               |
      | Catrobat | 123456    | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321    | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id  | name       | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1   | program 01 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2   | program 02 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3   | program 03 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4   | program 04 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5   | program 05 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6   | program 06 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7   | program 07 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8   | program 08 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9   | program 09 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10  | program 10 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11  | program 11 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12  | program 12 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13  | program 13 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14  | program 14 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15  | program 15 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16  | program 16 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17  | program 17 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18  | program 18 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19  | program 19 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20  | program 20 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21  | program 21 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22  | program 22 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |

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

  Scenario Outline: Buttons should disappear after clicking them in desktop format
    Given I am on homepage
    When I click <button>
    Then the element <button> should not be visible

    Examples:
      | button                              |
      | "#newest .button-show-more"         |
      | "#mostDownloaded .button-show-more" |
      | "#mostViewed .button-show-more"     |
      | "#random .button-show-more"         |

  Scenario Outline: Buttons should load more programs
    Given I am on homepage
    When I click <button>
    Then I should see funny things

    Examples:
      | button                              |
      | "#newest .button-show-more"         |
      | "#mostDownloaded .button-show-more" |
      | "#mostViewed .button-show-more"     |
      | "#random .button-show-more"         |

