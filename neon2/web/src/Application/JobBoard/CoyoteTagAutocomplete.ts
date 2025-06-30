import {Tag} from "../../Domain/JobBoard/JobBoard";
import {TagAutocomplete} from "./Port/TagAutocomplete";

export class CoyoteTagAutocomplete implements TagAutocomplete {
  prompt(prompt: string): Promise<Tag[]> {
    return fetch('/completion/prompt/tags?q=' + encodeURIComponent(prompt))
      .then(response => response.json())
      .then(tags => tags.map((coyoteTag: any) => {
        return {
          tagName: coyoteTag.name,
          title: coyoteTag.real_name,
          timesUsed: coyoteTag.topics + coyoteTag.jobs + coyoteTag.microblogs,
        };
      }));
  }
}
