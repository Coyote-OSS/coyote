import {Event} from "../../../Domain/ValueProp/Model";

export interface NavigationApi {
  event(event: Event): Promise<void>;
}
