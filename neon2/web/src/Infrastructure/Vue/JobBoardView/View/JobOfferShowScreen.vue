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
import {computed} from "vue";
import {JobOffer} from "../../../../Domain/JobBoard/JobOffer";
import {Design} from "../../DesignSystem/design";
import {useRouteId} from "../../External/VueRouter";
import JobOfferButtonPill from "./component/JobOfferButtonPill.vue";
import {toJobOfferShow} from "./component/JobOfferShow";
import JobOfferShow from "./component/JobOfferShow.vue";
import {useJobBoardService} from "./vue";

const service = useJobBoardService();
const jobOfferId = useRouteId();

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
