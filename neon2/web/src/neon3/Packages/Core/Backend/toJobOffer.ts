import {JobOffer} from "../../Feature/JobBoard/Domain/JobOffer";
import {JobOfferTag} from "../../Feature/JobBoard/Domain/Model";
import {parseWorkMode} from "../../Feature/JobBoard/Domain/workMode";
import {BackendJobOffer} from "./backendInput";

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
