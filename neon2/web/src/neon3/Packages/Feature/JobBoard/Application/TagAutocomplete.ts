import {Tag} from "../Domain/Model";

export interface TagAutocomplete {
  (tagPrompt: string, result: TagAutocompleteResult): void;
}

export type TagAutocompleteResult = (tags: Tag[]) => void;
