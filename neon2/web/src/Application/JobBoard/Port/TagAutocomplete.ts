import {Tag} from "../../../Domain/JobBoard/JobBoard";

export interface TagAutocomplete {
  prompt(tagPrompt: string): Promise<Tag[]>;
}
