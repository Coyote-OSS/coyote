import {Tag} from "../../Domain/JobBoard/JobBoard";
import {TagAutocomplete} from "./Port/TagAutocomplete";

export class TestTagAutocomplete implements TagAutocomplete {
  constructor(private acceptanceTagNames: string[]) {}

  async prompt(tagPrompt: string): Promise<Tag[]> {
    return this.acceptanceTagNames.map(tagName => ({tagName, title: null, timesUsed: 0}));
  }
}
