Feature: Campaign variant resolution
  In order to fulfill paying customers' campaigns,
  As a campaign space provider,
  I need to resolve the right graphic variant for a user's device.

  Background:
    Given there is a campaign "sale"
    And it is not a sole campaign

  Scenario: Variant "banner" is resolved into wide slots on desktop
    And the campaign "sale" has a "banner" variant "banner.png"
    When variants are resolved for a user on "desktop"
    Then the "header" slot contains "banner.png"
    And the "feed" slot contains "banner.png"

  Scenario: Variant "banner-xl" is resolved into wide slots on mobile
    Given the campaign "sale" has a "banner-xl" variant "banner-xl.png"
    When variants are resolved for a user on "mobile"
    Then the "header" slot contains "banner-xl.png"
    And the "feed" slot contains "banner-xl.png"

  Scenario: Variant "rectangle" is resolved into the square slot on desktop
    Given the campaign "sale" has a "rectangle" variant "rectangle.png"
    When variants are resolved for a user on "desktop"
    Then the "square" slot contains "rectangle.png"

  Scenario Outline: Without a variant, slot <slot> is empty on <device>
    When variants are resolved for a user on "<device>"
    Then the "<slot>" slot is empty

    Examples:
      | slot   | device  |
      | header | desktop |
      | feed   | desktop |
      | square | desktop |
      | header | mobile  |
      | feed   | mobile  |
      | square | mobile  |
