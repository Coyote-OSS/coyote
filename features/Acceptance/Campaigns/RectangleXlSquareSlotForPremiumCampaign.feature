Feature: Rectangle-xl square slot for a premium campaign
  In order to give a premium campaign more prominence in the square slot,
  As a campaign space provider,
  I need to resolve its rectangle-xl variant into the square slot on desktop.

  Scenario: Rectangle-xl variant is resolved into the square slot on desktop
    Given there is a premium campaign "sale"
    And the campaign "sale" has a "rectangle-xl" variant "rectxl.png"
    When variants are resolved for a user on "desktop"
    Then the "square" slot contains "rectxl.png"

  Scenario: Rectangle-xl variant is NOT resolved into the square slot on mobile
    Given there is a premium campaign "sale"
    And the campaign "sale" has a "rectangle-xl" variant "rectxl.png"
    When variants are resolved for a user on "mobile"
    Then the "square" slot does not contain "rectxl.png"

  Scenario: Without a rectangle-xl variant, square slot resolves to the rectangle
    Given there is a premium campaign "sale"
    And the campaign "sale" has a "rectangle" variant "rectangle.png"
    When variants are resolved for a user on "desktop"
    Then the "square" slot contains "rectangle.png"

  Scenario: Rectangle-xl variant is NOT resolved into the square slot for a non-premium campaign
    Given there is a standard campaign "regular"
    And the campaign "regular" has a "rectangle-xl" variant "rectxl.png"
    When variants are resolved for a user on "desktop"
    Then the "square" slot does not contain "rectxl.png"
