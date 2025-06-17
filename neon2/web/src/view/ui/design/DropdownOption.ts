import {IconName} from "../../../neon3/Core/View/Icon/icons";

export interface DropdownOption<T extends string> {
  value: T;
  title: string;
  icon?: IconName;
}
