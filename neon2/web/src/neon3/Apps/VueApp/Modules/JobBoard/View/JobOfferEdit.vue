<template>
  <JobOfferForm
    mode="update"
    :job-offer="toSubmitJobOffer(jobOffer)"
    :job-offer-expires-in-days="jobOffer.expiresInDays"
    :four-steps="false"
    @submit="update"
    @abort="abort"/>
</template>

<script setup lang="ts">
import {inject} from "vue";
import {useRouteId} from "../../../../../../Router";
import {SubmitJobOffer, toSubmitJobOffer} from "../../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {JobBoardService} from "../JobBoardService";
import {jobBoardServiceInjectKey} from "./vue";
import JobOfferForm from "./component/JobOfferForm.vue";

const jobOfferId = useRouteId();

const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;
const jobOffer: JobOffer = service.findJobOffer(jobOfferId)!;

function update(jobOffer: SubmitJobOffer): void {
  service.updateJob(jobOfferId, jobOffer);
}

function abort(): void {
  service.showJob(jobOfferId);
}
</script>
