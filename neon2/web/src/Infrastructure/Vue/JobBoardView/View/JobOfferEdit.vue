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
import {toSubmitJobOffer} from "../../../../Application/JobBoard/Model";
import {SubmitJobOffer} from "../../../../Application/JobBoard/Port/SubmitJobOffer";
import {JobOffer} from "../../../../Domain/JobBoard/JobOffer";
import {useRouteId} from "../../External/VueRouter";
import JobOfferForm from "./component/JobOfferForm.vue";
import {useJobBoardService} from "./vue";

const jobOfferId = useRouteId();

const service = useJobBoardService();
const jobOffer: JobOffer = service.findJobOffer(jobOfferId)!;

function update(jobOffer: SubmitJobOffer): void {
  service.updateJob(jobOfferId, jobOffer);
}

function abort(): void {
  service.showJob(jobOfferId);
}
</script>
