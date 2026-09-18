Feature: Regular banners share a slot
  In order to give multiple campaigns simultaneous exposure,
  As a campaign space provider,
  I need regular banners from different campaigns to appear together in the same slot.

  Scenario: Two campaigns' banners are both shown in the header and feed slots
    Given there is a campaign "first", which has a "banner" variant "first.png"
    And there is a campaign "second", which has a "banner" variant "second.png"
    When variants are resolved for a user on "desktop"
    Then the following slots contain:
      | header | first.png | second.png |
      | feed   | first.png | second.png |
