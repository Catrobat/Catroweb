@admin
  Feature: Admin click statistics feature

    Background:
      Given there are admins:
        | name     | password | token      | email                | id |
        | Admin    | 123456   | eeeeeeeeee | admin@pocketcode.org |  1 |
      Given there are users:
        | id | name      |
        | 2  | Catrobat  |
        | 3  | OtherUser |
      And there are extensions:
        | id | name         | prefix  |
        | 1  | Arduino      | ARDUINO |
        | 2  | Drone        | DRONE   |
        | 3  | Lego         | LEGO    |
        | 4  | Phiro        | PHIRO   |
        | 5  | Raspberry Pi | RASPI   |

      And there are tags:
        | id | en           | de           |
        | 1  | Game         | Spiel        |
        | 2  | Animation    | Animation    |
        | 3  | Story        | Geschichte   |
        | 4  | Music        | Musik        |
        | 5  | Art          | Kunst        |
        | 6  | Experimental | Experimental |

      And there are projects:
        | id | name    | description | owned by  | downloads | apk_downloads | views | upload time      | version | extensions | tags_id | remix_root |
        | 1  | Minions | p1          | Catrobat  | 3         | 2             | 12    | 01.01.2013 12:01 | 0.8.5   | Lego,Phiro | 1,2,3,4 | true       |
        | 2  | Galaxy  | p2          | OtherUser | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | Lego,Drone | 1,2,3   | false      |
        | 3  | Alone   | p3          | Catrobat  | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |            | 1,2     | true       |
        | 4  | Trolol  | p5          | Catrobat  | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   | Lego       | 5       | true       |
        | 5  | Nothing | p6          | Catrobat  | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |            | 6       | true       |


      And there are click statistics:
        | type                  | user_agent                      | user     | referrer                                       | locale | rec_program_id | rec_from_id  | tag_id | extension_name | clicked_at            |
        | project               | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/2  | de     | 1              | 2            |        |                | August 15, 2020 20:20 |
        | project               | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/3  | de     | 2              | 3            |        |                | August 15, 2020 20:21 |
        | tags                  | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/1  | en_us  |                |              |  2     |                | August 15, 2020 20:22 |
        | tags                  | Chrome/5.0 (X11; Linux x86_64)  | OtherUser| http://localhost/index_test.php/app/project/3  | en_us  |                |              |  1     |                | August 15, 2020 20:23 |
        | tags                  | Chrome/5.0 (X11; Linux x86_64)  | OtherUser| http://localhost/index_test.php/app/project/5  | en_us  |                |              |  6     |                | August 15, 2020 20:24 |
        | rec_specific_programs | Mozilla/5.0 (X11; Linux x86_64) | OtherUser| http://localhost/index_test.php/app/project/4  | en_us  | 5              | 4            |        |                | August 15, 2020 20:25 |
        | rec_specific_programs | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/1  | en_us  | 3              | 1            |        |                | August 15, 2020 20:26 |
        | rec_specific_programs | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/2  | en_us  | 5              | 2            |        |                | August 15, 2020 20:27 |
        | project               | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/1  | en_us  | 3              | 1            |        |                | August 15, 2020 20:28 |
        | rec_homepage          | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/2  | en_us  |                |              |        |                | August 15, 2020 20:29 |
        | extensions            | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/4  | en_us  |                |              |        |  Lego          | August 15, 2020 20:30 |
        | extensions            | Mozilla/5.0 (X11; Linux x86_64) | Catrobat | http://localhost/index_test.php/app/project/2  | en_us  |                |              |        |  Arduino       | August 15, 2020 20:31 |



    Scenario: As admin I should be able to see list of all click statistics
        Given I log in as "Admin" with the password "123456"
        And I am on "/admin/click_stats/list"
        And I wait for the page to be loaded
        Then I should see the table with all click statistics in the following order:
          | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
          | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
          | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
          | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
          | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
          | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
          | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
          | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
          | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
          | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
          | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
          | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
          | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |


    Scenario: List all click statistics sorted by id ascending
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "Id"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |

    Scenario: List all click statistics sorted by clicked at descending
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "Clicked At"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |

    Scenario: List all click statistics sorted by locale
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "Locale"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |


    Scenario: List all click statistics sorted by type
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "Type"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:

        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |


    Scenario: List all click statistics sorted by tag
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "Tag"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
    Scenario: List all click statistics sorted by referrer
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "Referrer"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |


    Scenario: List all click statistics sorted by user agent
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/click_stats/list"
      And I wait for the page to be loaded
      And I click on the column with the name "User Agent"
      And I wait for the page to be loaded
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 10  | rec_homepage           | Catrobat  |             |                    |                            |               |           | August 15, 2020 20:29 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 11  | extensions             | Catrobat  |             |                    |                            |               | Lego      | August 15, 2020 20:30 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 12  | extensions             | Catrobat  |             |                    |                            |               | Arduino   | August 15, 2020 20:31 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |



    Scenario: Filter click statistics by id using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=1&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id | Recommended From Program  | Tag           | Extension |Clicked At              | Locale  | User Agent                        | Referrer                                      |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de     | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
      And I should not see "rec_specific_programs"
      And I should not see "rec_homepage"
      And I should not see "tags"
    Scenario: Filter click statistics by type using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=tags&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 3   | tags                   | Catrobat  |             |                    |                            | Animation     |           | August 15, 2020 20:22 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
      And I should not see "rec_specific_programs"
      And I should not see "rec_homepage"
      And I should not see "Minions (#1)"
      And I should not see "Galaxy (#2)"
    Scenario: Filter click statistics by program name using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=alone&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 9   | project                | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:28 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
        | 7   | rec_specific_programs  | Catrobat  | Alone (#3)  |                    | Minions (#1)               |               |           | August 15, 2020 20:26 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/1 |
      And I should not see "rec_homepage"
      And I should not see "Galaxy (#2)"
      And I should not see "tags"
      And I should not see "Nothing (#5)"
      And I should not see "OtherUser"
    Scenario: Filter click statistics by recommended from program name using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=galaxy&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 8   | rec_specific_programs  | Catrobat  | Nothing (#5)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:27 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
      And I should not see "rec_homepage"
      And I should not see "tags"
      And I should not see "OtherUser"
      And I should not see "Alone (#3)"
      And I should not see "Alone (#5)"

    Scenario: Filter click statistics by username using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=otheruser&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 6   | rec_specific_programs  | OtherUser | Nothing (#5)|                    | Trolol (#4)                |               |           | August 15, 2020 20:25 | en_us   | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/4 |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
      And I should not see "rec_homepage"
      And I should not see "Catrobat"
      And I should not see "Alone (#3)"
      And I should not see "Alone (#5)"
    Scenario: Filter click statistics by locale using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=de&filter%5B_page%5D=1&filter%5B_sort_by%5D=locale&filter%5B_sort_order%5D=ASC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 1   | project                | Catrobat  | Minions (#1)|                    | Galaxy (#2)                |               |           | August 15, 2020 20:20 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/2 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
      And I should not see "rec_homepage"
      And I should not see "rec_specific_programs"
      And I should not see "tags"
      And I should not see "OtherUser"
      And I should not see "en_us"
      And I should not see "Alone (#5)"

    Scenario: Filter click statistics by user agent using filter options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=chrome&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 5   | tags                   | OtherUser |             |                    |                            | Experimental  |           | August 15, 2020 20:24 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/5 |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
      And I should not see "rec_homepage"
      And I should not see "rec_specific_programs"
      And I should not see "Catrobat"
      And I should not see "Alone (#5)"
      And I should not see "Mozilla"

    Scenario: Filter click statistics by user agent using referrer options
      Given I log in as "Admin" with the password "123456"
      And I am on "/admin/all_programs/list"
      And I wait for the page to be loaded
      Then I am on "/admin/click_stats/list?filter%5Bid%5D%5Btype%5D=&filter%5Bid%5D%5Bvalue%5D=&filter%5Btype%5D%5Btype%5D=&filter%5Btype%5D%5Bvalue%5D=&filter%5Bprogram__name%5D%5Btype%5D=&filter%5Bprogram__name%5D%5Bvalue%5D=&filter%5Bscratch_program_id%5D%5Btype%5D=&filter%5Bscratch_program_id%5D%5Bvalue%5D=&filter%5Brecommended_from_program__name%5D%5Btype%5D=&filter%5Brecommended_from_program__name%5D%5Bvalue%5D=&filter%5Buser__username%5D%5Btype%5D=&filter%5Buser__username%5D%5Bvalue%5D=&filter%5Buser_agent%5D%5Btype%5D=&filter%5Buser_agent%5D%5Bvalue%5D=&filter%5Breferrer%5D%5Btype%5D=&filter%5Breferrer%5D%5Bvalue%5D=3&filter%5Blocale%5D%5Btype%5D=&filter%5Blocale%5D%5Bvalue%5D=&filter%5B_page%5D=1&filter%5B_sort_by%5D=id&filter%5B_sort_order%5D=DESC&filter%5B_per_page%5D=32"
      Then I should see the table with all click statistics in the following order:
        | Id  | Type                   | User      |  Program    | Scratch Program Id |  Recommended From Program  | Tag           | Extension |Clicked At             | Locale  | User Agent                        | Referrer                                      |
        | 4   | tags                   | OtherUser |             |                    |                            | Game          |           | August 15, 2020 20:23 | en_us   | Chrome/5.0 (X11; Linux x86_64)    | http://localhost/index_test.php/app/project/3 |
        | 2   | project                | Catrobat  | Galaxy (#2) |                    | Alone (#3)                 |               |           | August 15, 2020 20:21 | de      | Mozilla/5.0 (X11; Linux x86_64)   | http://localhost/index_test.php/app/project/3 |
      And I should not see "rec_homepage"
      And I should not see "rec_specific_programs"
      And I should not see "Alone (#5)"
      And I should not see "Nothing (#5)"
      And I should not see "Minions (#1)"










