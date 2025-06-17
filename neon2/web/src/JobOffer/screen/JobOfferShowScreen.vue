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
import {useRoute} from "vue-router";
import {Design} from "../../neon3/Apps/VueApp/DesignSystem/design";
import {JobBoardService} from "../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {jobBoardServiceInjectKey} from "../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {JobOffer} from "../../neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import JobOfferButtonPill from "../JobOfferButtonPill.vue";
import {toJobOfferShow} from "../JobOfferShow";
import JobOfferShow from "../JobOfferShow.vue";

const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

const route = useRoute();
const jobOfferId = Number(route.params.id)!;

function navigateHome(): void {
  service.navigate('home', null);
}

function editJob(): void {
  service.navigate('edit', jobOfferId);
}

function applyForJob(): void {
  service.applyForJob(jobOfferId);
}

function resumePayment(): void {
  service.navigate('payment', jobOfferId);
}

const routeJobOffer = computed((): JobOffer => {
  return service.findJobOffer(jobOfferId)!;
});

function markAsFavourite(favourite: boolean): void {
  service.markAsFavourite(jobOfferId, favourite);
}
</script>
