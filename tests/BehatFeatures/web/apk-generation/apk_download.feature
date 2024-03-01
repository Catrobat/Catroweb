Feature:

  Background:
    Given there are projects:
      | name              | apk_status | visible | id |
      | Galaxy War        | none       | true    | 1  |
      | My little program | pending    | true    | 2  |
      | Bunny             | ready      | true    | 3  |
      | Whack a Marko     | ready      | false   | 4  |

  Scenario:
    When I want to download the apk file of "Bunny"
    Then I should receive the apk file

  Scenario:
    When I want to download the apk file of "Galaxy War"
    Then the apk file will not be found

  Scenario:
    When I want to download the apk file of "My little program"
    Then the apk file will not be found

  Scenario: I should not be allowed to download the apk of an invisible program
    When I want to download the apk file of "Whack a Marko"
    Then the apk file will not be found
    