<template>
  <JobOfferForm
    mode="create"
    :job-offer="newJobOffer"
    :job-offer-expires-in-days="expiresInDays"
    :four-steps="fourSteps"
    @submit="create"
    @abort="service.navigate('pricing', null)"/>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {SubmitJobOffer} from "../../../../Application/JobBoard/Port/SubmitJobOffer";
import {useBoardStore} from "../boardStore";
import JobOfferForm from "./component/JobOfferForm.vue";
import {useJobBoardService} from "./vue";

const service = useJobBoardService();
const store = useBoardStore();

function create(jobOffer: SubmitJobOffer): void {
  service.createJob(store.pricingPlan!, jobOffer);
}

const expiresInDays = computed(() => store.pricingPlan === 'free' ? 14 : 30);
const fourSteps = computed(() => store.pricingPlan !== 'free');

const newJobOffer: SubmitJobOffer = {
  title: '',
  description: null,
  companyName: '',
  salaryRangeFrom: null,
  salaryRangeTo: null,
  salaryIsNet: true,
  salaryCurrency: 'PLN',
  salaryRate: 'monthly',
  locations: [],
  companyLogoUrl: null,
  tags: [],
  workModeRemoteRange: 0,
  legalForm: 'employment',
  experience: 'not-provided',
  applicationMode: '4programmers',
  applicationExternalAts: null,
  applicationEmail: store.applicationEmail,
  companyHiringType: 'direct',
  companyFundingYear: null,
  companySizeLevel: null,
  companyAddress: null,
  companyVideoUrl: null,
  companyDescription: null,
  companyWebsiteUrl: null,
  companyPhotoUrls: [],
};
</script>
