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
    :job-offer="toJobOfferShow(routeJobOffer)"
    :can-edit="routeJobOffer.canEdit"
    @edit="editJob"
    @apply="applyForJob"
    @favourite="markAsFavourite"/>
</template>

<script setup lang="ts">
import {computed, inject} from "vue";
import {Design} from "../../../../neon3/Apps/VueApp/DesignSystem/design";
import {JobBoardService} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {jobBoardServiceInjectKey} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {JobOffer} from "../../../../neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {RouteProperties} from "../../screen/Screens";
import JobOfferButtonPill from "../JobOfferButtonPill.vue";
import {toJobOfferShow} from "../JobOfferShow";
import JobOfferShow from "../JobOfferShow.vue";

const route = defineProps<RouteProperties>();
const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

function navigateHome(): void {
  service.navigate('home', null);
}

function editJob(): void {
  service.navigate('edit', route.routeJobOfferId!);
}

function applyForJob(): void {
  service.applyForJob(route.routeJobOfferId!);
}

function resumePayment(): void {
  service.navigate('payment', route.routeJobOfferId!);
}

const routeJobOffer = computed((): JobOffer => {
  return service.findJobOffer(route.routeJobOfferId!)!;
});

function markAsFavourite(favourite: boolean): void {
  service.markAsFavourite(route.routeJobOfferId!, favourite);
}
</script>
