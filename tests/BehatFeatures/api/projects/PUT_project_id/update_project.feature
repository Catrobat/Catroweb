@api @projects @put
Feature: Update project

  Background:
    Given there are users:
      | name     |
      | Catrobat |
    Given there are projects:
      | id | name        | description   | credits | private | owned by |
      | 1  | Project One | First project |         | true    | Catrobat |

  Scenario: Update all possible fields of a project
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "Awesome game",
        "description": "The best game you will ever play! Check it out!",
        "credits": "Big thanks to the Catrobat project.",
        "private": false,
        "screenshot": "data:image/gif;base64,R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
      }
    """
    And I request "PUT" "/api/project/1"
    Then the response code should be "204"
    And the following projects exist in the database:
      | id | name         | description                                     | credits                             | private |
      | 1  | Awesome game | The best game you will ever play! Check it out! | Big thanks to the Catrobat project. | false   |

  Scenario: Update project with empty name, too long credits, invalid screenshot and empty (= valid) description
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "",
        "description": "",
        "credits": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. In scelerisque a mi sed cursus. Maecenas aliquam et mi et convallis. Mauris sed sem leo. Donec sit amet condimentum orci, porta malesuada justo. Suspendisse quis enim vel ligula pellentesque vulputate. Sed a viverra nisl. Pellentesque fermentum nibh id lorem congue, ut sollicitudin urna pulvinar. Donec blandit ante ac urna lobortis tristique. Proin tempor pretium lectus, in gravida mauris vestibulum eget. Nulla facilisi. Fusce volutpat suscipit vestibulum. Phasellus sagittis tempor enim, at tincidunt risus consequat vel. Suspendisse erat nisi, lacinia pretium luctus vehicula, tempus vel sem. Ut placerat vel augue vel congue. Vestibulum elementum finibus ligula ac posuere. Aenean quis porta quam. Mauris viverra felis pretium tortor posuere pulvinar. Sed ut dui eleifend, convallis neque ac, auctor ipsum. Sed tempor enim interdum neque elementum, a pulvinar nibh feugiat. Mauris sed magna arcu. Cras dictum porta mi, at venenatis leo fringilla sit amet. Ut sollicitudin lacinia enim sed auctor. In non augue sed purus luctus porta id sed magna. Nunc non gravida nisl. Nam at molestie justo, et vestibulum eros. Nullam bibendum posuere nisi in ultrices. Mauris quis magna eu dui tempus vehicula. Praesent imperdiet tincidunt dictum. Aenean lobortis nibh eget nunc efficitur, a maximus leo laoreet. Curabitur at eros metus. Phasellus molestie urna vel molestie luctus. Vivamus ut odio ornare, hendrerit purus eu, rhoncus turpis. Cras auctor vestibulum dui a consectetur. Vivamus placerat lorem vel nisi commodo, vel venenatis leo suscipit. Mauris eget elementum nisl. Aliquam hendrerit mollis varius. Nunc ornare lacus in ligula venenatis, a imperdiet nisi fermentum. Sed vel lacus eu ligula vehicula ultricies eget sit amet magna. Fusce molestie scelerisque ante et posuere. Nam magna felis, luctus vitae vestibulum nec, faucibus pellentesque enim. Sed ut justo laoreet, iaculis erat at, luctus eros. Phasellus eros massa, dapibus sed varius sit amet, interdum at dui. Sed sodales sagittis vestibulum. Etiam vitae viverra nunc, at semper purus. Nam ipsum augue, ornare in tristique a, blandit tempus sapien. Cras aliquet in dolor id finibus. Pellentesque porta odio nec efficitur efficitur. Maecenas ipsum leo, condimentum quis quam in, commodo tempus leo. Nulla posuere imperdiet mattis. Vestibulum venenatis lorem eget turpis aliquam consectetur. Aliquam molestie dui id nisl commodo, iaculis efficitur quam varius. Etiam ac facilisis nisi. Aliquam euismod quis dolor non fringilla. Donec sit amet ante at nulla dictum malesuada. Phasellus volutpat nisl at urna malesuada rhoncus. Quisque aliquam justo quam, et tincidunt velit rhoncus id. Pellentesque dapibus ante vitae ex tempor congue. Donec maximus finibus vehicula. Duis eleifend congue malesuada. Praesent accumsan, urna a porta blandit, est est congue dolor, a rhoncus sapien libero non neque. Vivamus in dui ut urna tristique eleifend. Sed in ex in turpis dictum molestie mauris.",
        "screenshot": "data:image/png;base64,Catro/web"
      }
    """
    And I request "PUT" "/api/project/1"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "name": "Name cannot be empty",
        "credits": "Notes and credits too long",
        "screenshot": "Project screenshot invalid or not supported"
      }
    """
    And the following projects exist in the database:
      | id | name        | description   | credits | private |
      | 1  | Project One | First project |         | true    |
