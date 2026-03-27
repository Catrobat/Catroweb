@web-general @dataset-homepage
Feature: Homepage browsing
  Scenario: Scratch remixes show only the expected projects
    Given I have accepted cookies
    When I open the "homepage" page
    Then the "scratch remixes" section should contain:
      | project 6 |
      | project 7 |
    And the "scratch remixes" section should not contain:
      | project 1 |

  Scenario: Homepage shows the expected sections
    Given I have accepted cookies
    When I open the "homepage" page
    Then the homepage section titles should contain:
      | Examples         |
      | Most downloaded  |
      | Scratch remixes  |
      | Random projects  |
      | Popular projects |

  Scenario Outline: Welcome sections show the expected hero copy and video
    Given I have accepted cookies
    When I open the path "<path>"
    Then the welcome section should show the video "<video>"
    And the welcome section should show:
      | <visible 1> |
      | <visible 2> |

    Examples:
      | path         | video                                     | visible 1            | visible 2                                                        |
      | /            | https://www.youtube.com/embed/BHe2r2WU-T8 | Pocket Code         | Create stories, games and more...                               |
      | /luna        | https://www.youtube.com/embed/-6AEZrSbOMg | Luna & Cat          | Create stories, games and more...                               |
      | /embroidery  | https://www.youtube.com/embed/IjHI0UZzuWM | Embroidery Designer | Stitch your own patterns on your T-shirts, bags, pants, or smartphone case! |
      | /mindstorms  | https://www.youtube.com/embed/YnSl-fSV-nY | Mindstorms EV3 and NXT | Create your own projects with your Lego Mindstorms EV3 and NXT robots |

  Scenario: Logged-in users do not see the welcome section
    When I log in as "PlaywrightCatrobat" with password "123456"
    Then the current URL should end with "/app/"
    And the welcome section should not exist

  Scenario: Featured slider keeps the expected order
    Given I have accepted cookies
    When I open the "homepage" page
    Then the featured slider links should be:
      | http://www.google.at/ |
      | /project/9402         |
      | /project/9403         |

  Scenario: Homepage footer shows the legal links
    Given I have accepted cookies
    When I open the "homepage" page
    Then the footer should show:
      | About Catrobat |
      | License to play |
      | Privacy policy |
      | Terms of Use |
