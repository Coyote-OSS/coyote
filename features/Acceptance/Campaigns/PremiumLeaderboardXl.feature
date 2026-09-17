Feature: Leaderboard-xl header for a premium campaign
  In order to give a premium campaign more prominence in the header,
  As a campaign space provider,
  I need to resolve its leaderboard-xl variant into the header slot on desktop.

  Scenario: Leaderboard-xl variant is resolved into the header on desktop
    Given there is a premium campaign "sale"
    Given the campaign "sale" has a "leaderboard-xl" variant "lbxl.png"
    When variants are resolved for a user on "desktop"
    Then the "header" slot contains "lbxl.png"

  Scenario: Leaderboard-xl variant is NOT resolved into the header on mobile
    Given there is a premium campaign "sale"
    Given the campaign "sale" has a "leaderboard-xl" variant "lbxl.png"
    When variants are resolved for a user on "mobile"
    Then the "header" slot does not contain "lbxl.png"

  Scenario: Leaderboard-xl variant is NOT resolved into the feed
    Given there is a premium campaign "sale"
    Given the campaign "sale" has a "leaderboard-xl" variant "lbxl.png"
    When variants are resolved for a user on "desktop"
    Then the "feed" slot does not contain "lbxl.png"

  Scenario: Leaderboard-xl variant is NOT resolved into the header for a non-premium campaign
    Given there is a standard campaign "regular"
    And the campaign "regular" has a "leaderboard-xl" variant "lbxl.png"
    When variants are resolved for a user on "desktop"
    Then the "header" slot does not contain "lbxl.png"

  Scenario: Without leaderboard-xl variant, header is empty for a premium campaign
    Given there is a premium campaign "sale"
    When variants are resolved for a user on "desktop"
    Then the "header" slot is empty

  Scenario: Without leaderboard-xl variant, header falls back to the banner
    Given there is a premium campaign "sale"
    And the campaign "sale" has a "banner" variant "banner.png"
    When variants are resolved for a user on "desktop"
    Then the "header" slot contains "banner.png"
