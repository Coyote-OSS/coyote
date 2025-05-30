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
import {SubmitJobOffer, toSubmitJobOffer, UploadAssets} from "../../../main";
import {RouteProperties} from "../screen/Screens";
import {TagAutocomplete, UiController, ViewListener} from "../ui";
import JobOfferForm from "./JobOfferForm.vue";

const screen = inject('screen') as Screen;
const route = defineProps<RouteProperties>();

interface Screen {
  uiController: UiController;
  viewListener: ViewListener;
  tagAutocomplete: TagAutocomplete;
  upload: UploadAssets;
}

function update(jobOffer: SubmitJobOffer): void {
  screen.viewListener.updateJob(route.routeJobOffer!.id, jobOffer);
}

function abort(): void {
  screen.uiController.showJobOffer(route.routeJobOffer!);
}
</script>
