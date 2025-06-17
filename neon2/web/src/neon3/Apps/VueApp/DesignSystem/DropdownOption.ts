import {IconName} from "../Icon/icons";

export interface DropdownOption<T extends string> {
  value: T;
  title: string;
  icon?: IconName;
}
