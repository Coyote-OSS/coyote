import {BackendJobOffer} from "./BackendJobOffer";
import {JobOfferTag} from "../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../Domain/JobBoard/JobOffer";
import {parseWorkMode} from "../../Domain/JobBoard/workMode";

export function toJobOffer(jobOffer: BackendJobOffer): JobOffer {
  const {fields, ...operationalFields} = jobOffer;
  return {
    ...operationalFields,
    ...fields,
    workMode: parseWorkMode(jobOffer.fields.workModeRemoteRange),
    tags: jobOfferTags(jobOffer),
  };
}

function jobOfferTags(jobOffer: BackendJobOffer): JobOfferTag[] {
  return jobOffer.fields.tagNames.map((tagName: string, index: number): JobOfferTag => {
    return {
      tagName,
      priority: jobOffer.fields.tagPriorities[index],
    };
  });
}
