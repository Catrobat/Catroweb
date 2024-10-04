@web
Feature: Pocketcode homepage
  In order to access and browse the projects
  As a visitor
  I want to be able to see the homepage

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | User1     |
      | 3  | Catrobat2 |

    And there are extensions:
      | id | internal_title |
      | 1  | embroidery     |

    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 3  | embroidery |

    And there are projects:
      | id | name      | owned by  | extensions | flavor     |
      | 1  | project 1 | Catrobat  | embroidery | pocketcode |
      | 2  | project 2 | Catrobat  | embroidery | embroidery |
      | 3  | project 3 | User1     |            | pocketcode |
      | 4  | project 4 | User1     |            | embroidery |
      | 5  | project 5 | User1     |            | pocketcode |
      | 6  | project 6 | Catrobat2 |            | pocketcode |
      | 7  | project 7 | Catrobat2 |            | pocketcode |

    And following projects are featured:
      | name      | url                   | active | priority |
      | project 1 |                       | 0      | 1        |
      | project 2 |                       | 1      | 3        |
      | project 3 |                       | 1      | 2        |
      |           | http://www.google.at/ | 1      | 5        |
      |           | http://www.orf.at/    | 0      | 4        |

    And following projects are examples:
      | name      | active | priority |
      | project 4 | 0      | 1        |
      | project 5 | 1      | 3        |
      | project 6 | 1      | 2        |

    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 70058680          | 6                 |
      | 70058680          | 7                 |

  Scenario: Scratch remixes project should be visible:
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    Then the element "#home-projects__scratch" should exist
    And the "#home-projects__scratch" element should contain "project 6"
    And the "#home-projects__scratch" element should contain "project 7"
    And the "#home-projects__scratch" element should not contain "project 1"

  Scenario: Viewing the homepage at website root
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    Then one of the ".project-list__title" elements should contain "Examples"
    Then one of the ".project-list__title" elements should contain "Most downloaded"
    Then one of the ".project-list__title" elements should contain "Scratch remixes"
    Then one of the ".project-list__title" elements should contain "Random projects"
    Then one of the ".project-list__title" elements should contain "Popular projects"

  Scenario: Welcome Section
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element "#welcome-section" should be visible
    Then I should see the video available at "https://www.youtube.com/embed/BHe2r2WU-T8"
    And I should see "Google Play Store"
    And I should see "iOS App Store"
    And I should see "Huawei AppGallery"

  Scenario: Welcome Section luna
    Given I am on "/luna"
    And I wait for the page to be loaded
    Then the element "#welcome-section" should be visible
    Then I should see the video available at "https://www.youtube.com/embed/-6AEZrSbOMg"
    And I should see "Google Play Store"
    And I should see "Discord Chat"
    And I should not see "iOS App Store"
    And I should not see "Huawei AppGallery"

  Scenario: Welcome Section embroidery
    Given I am on "/embroidery"
    And I wait for the page to be loaded
    Then the element "#welcome-section" should be visible
    Then I should see the video available at "https://www.youtube.com/embed/IjHI0UZzuWM"
    And I should see "Google Play Store"
    And I should see "Instagram"
    And I should not see "iOS App Store"
    And I should not see "Huawei AppGallery"

  Scenario: Welcome Section mindstorms
    Given I am on "/mindstorms"
    And I wait for the page to be loaded
    Then the element "#welcome-section" should be visible
    Then I should see the video available at "https://www.youtube.com/embed/YnSl-fSV-nY"
    And I should see "Google Play Store"
    And I should not see "iOS App Store"
    And I should not see "Huawei AppGallery"

  Scenario: Cant see the Welcome Section when logged in
    Given I log in as "Catrobat"
    And I go to the homepage
    And I wait for the page to be loaded
    And the element "#welcome-section" should not exist

  Scenario: Featured Programs and Urls
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    And I should see the slider with the values "http://www.google.at/,project 2,project 3"

  @disabled
  Scenario: Example Programs
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element "#home-projects__example" should exist
    And the "#home-projects__example" element should contain "project 5"
    And the "#home-projects__example" element should contain "project 6"

  Scenario: User should be able to see legally required links
    Given I am on homepage
    And I wait for the page to be loaded
    And I should see "About Catrobat"
    And I should see "License to play"
    And I should see "Privacy policy"
    And I should see "Terms of Use"
