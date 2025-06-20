import {JobBoardBackend} from "../../../Core/Backend/JobBoardBackend";
import {TagAutocomplete, TagAutocompleteResult} from "../Application/TagAutocomplete";

export class TagAutocompleteAdapter implements TagAutocomplete {
  constructor(private backend: JobBoardBackend) {}

  prompt(tagPrompt: string, result: TagAutocompleteResult): void {
    this.backend.tagsAutocomplete(tagPrompt).then(tags => result(tags));
  }
}
