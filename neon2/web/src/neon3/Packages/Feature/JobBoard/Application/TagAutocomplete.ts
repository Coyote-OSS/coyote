import {Tag} from "../Domain/Model";

export interface TagAutocomplete {
  prompt(tagPrompt: string, result: TagAutocompleteResult): void;
}

export type TagAutocompleteResult = (tags: Tag[]) => void;
