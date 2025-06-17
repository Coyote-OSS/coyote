import {clamp} from "../../../../src/neon3/Core/View/DesignSystem/DropdownMultiple";
import {assertEquals, describe, test} from "../../../assertion";

describe('Clamp number.', () => {
  test('Returns the value.', () => {
    assertEquals(7, clamp(7, 0, 10));
  });
  test('Clamps to minimum.', () => {
    assertEquals(0, clamp(-5, 0, 10));
  });
  test('Clamps to maximum.', () => {
    assertEquals(10, clamp(15, 0, 10));
  });
});
