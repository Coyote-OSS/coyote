<template>
  <JobOfferForm
    mode="update"
    :view-listener="screen.viewListener"
    :job-offer="toSubmitJobOffer(route.routeJobOffer!)"
    :job-offer-expires-in-days="route.routeJobOffer!.expiresInDays"
    :tag-autocomplete="screen.tagAutocomplete"
    :upload="screen.upload"
    :four-steps="false"
    @submit="update"
    @abort="abort"/>
</template>

<script setup lang="ts">
import {inject} from "vue";
import {UploadAssets} from "../../../../main";
import {JobBoardService} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {jobBoardServiceInjectKey} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {SubmitJobOffer, toSubmitJobOffer} from "../../../../neon3/Packages/Feature/JobBoard/Application/Model";
import {RouteProperties} from "../../screen/Screens";
import {TagAutocomplete, UiController, ViewListener} from "../../ui";
import JobOfferForm from "../JobOfferForm.vue";

const screen = inject('screen') as Screen;
const route = defineProps<RouteProperties>();
const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

interface Screen {
  viewListener: ViewListener;
  tagAutocomplete: TagAutocomplete;
  upload: UploadAssets;
}

function update(jobOffer: SubmitJobOffer): void {
  service.updateJob(route.routeJobOfferId!, jobOffer);
}

function abort(): void {
  service.showJob(route.routeJobOfferId!, route.routeJobOffer!);
}
</script>
