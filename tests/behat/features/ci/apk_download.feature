Feature:

  Background:
    Given there are programs:
      | name              | apk status | visible |
      | Galaxy War        | none       | true    |
      | My little program | pending    | true    |
      | Bunny             | ready      | true    |
      | Whack a Marko     | ready      | false   |

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
    