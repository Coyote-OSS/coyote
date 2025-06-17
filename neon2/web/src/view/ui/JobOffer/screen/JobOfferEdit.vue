<template>
  <JobOfferForm
    mode="update"
    :job-offer="toSubmitJobOffer(route.routeJobOffer!)"
    :job-offer-expires-in-days="route.routeJobOffer!.expiresInDays"
    :four-steps="false"
    @submit="update"
    @abort="abort"/>
</template>

<script setup lang="ts">
import {inject} from "vue";
import {JobBoardService} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {jobBoardServiceInjectKey} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {SubmitJobOffer, toSubmitJobOffer} from "../../../../neon3/Packages/Feature/JobBoard/Application/Model";
import {RouteProperties} from "../../screen/Screens";
import JobOfferForm from "../JobOfferForm.vue";

const route = defineProps<RouteProperties>();
const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

function update(jobOffer: SubmitJobOffer): void {
  service.updateJob(route.routeJobOfferId!, jobOffer);
}

function abort(): void {
  service.showJob(route.routeJobOfferId!, route.routeJobOffer!);
}
</script>
