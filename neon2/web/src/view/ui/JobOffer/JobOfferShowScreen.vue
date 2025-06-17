<template>
  <JobOfferButtonPill @click="navigateHome">Wróć do ogłoszeń</JobOfferButtonPill>
  <Design.Card
    title="Ogłoszenie oczekuje na płatność"
    v-if="routeJobOffer.status === 'awaitingPayment'">
    <Design.Button primary @click="resumePayment">
      Przejdź do płatności
    </Design.Button>
  </Design.Card>
  <JobOfferShow
    :view-listener="screen.viewListener"
    :job-offer="toJobOfferShow(routeJobOffer)"
    :can-edit="routeJobOffer.canEdit"
    @edit="editJob"
    @apply="applyForJob"
    @favourite="markAsFavourite"/>
</template>

<script setup lang="ts">
import {computed, inject} from "vue";
import {Design} from "../../../neon3/Apps/VueApp/DesignSystem/design";
import {JobOffer} from "../../../neon3/Packages/Feature/JobBoard/Application/JobOffer";
import {RouteProperties} from "../screen/Screens";
import {UiController, ViewListener} from "../ui";
import JobOfferButtonPill from "./JobOfferButtonPill.vue";
import {toJobOfferShow} from "./JobOfferShow";
import JobOfferShow from "./JobOfferShow.vue";

const screen = inject('screen') as Screen;
const route = defineProps<RouteProperties>();

interface Screen {
  uiController: UiController;
  viewListener: ViewListener;
}

function navigateHome(): void {
  screen.uiController.navigate('home', null);
}

function editJob(): void {
  screen.uiController.navigate('edit', route.routeJobOfferId!);
}

function applyForJob(): void {
  screen.uiController.applyForJob(route.routeJobOfferId!);
}

function resumePayment(): void {
  screen.uiController.navigate('payment', route.routeJobOfferId!);
}

const routeJobOffer = computed((): JobOffer => {
  return screen.uiController.findJobOffer(route.routeJobOfferId!)!;
});

function markAsFavourite(favourite: boolean): void {
  screen.uiController.markAsFavourite(route.routeJobOfferId!, favourite);
}
</script>
