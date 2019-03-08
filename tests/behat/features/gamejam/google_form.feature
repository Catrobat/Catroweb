Feature: On upload a google form link should be created with some parameters set

  Scenario: Parameters need to be set in the form url

    Given The form url of the current jam is
          """
          https://someurl.google.com/myform?name=%CAT_NAME%&id=%CAT_ID%&mail=%CAT_MAIL%
          """
    And I am "Catrobat" with email "catrobat@catrob.at"
    When I submit a game which gets the id "1"
    Then The returned url should be
          """
          https://someurl.google.com/myform?name=Catrobat&id=1&mail=catrobat@catrob.at
          """

  @disabled
  Scenario: Parameters need to be set in the form url

    Given I am "Catrobat" with email "catrobat@catrob.at"
    When I submit a game which gets the id "1"
    Then The following patameters should be set in the form url:
      | Parameter  | Value              |
      | %CAT_NAME% | Catrobat           |
      | %CAT_ID%   | 1                  |
      | %CAT_MAIL% | catrobat@catrob.at |

  @external
  Scenario: On google form submission, the server must be informed.

    Given The jam is on "https://share.catrob.at/pocketalice/"
    And I filled the google form for my game with id "33"
    When I submit it to google
    Then The url "https://share.catrob.at/pocketalice/api/gamejam/finalize/33" should be called