<template>
  <JobOfferForm
    mode="create"
    :view-listener="screen.viewListener"
    :job-offer="newJobOffer"
    :job-offer-expires-in-days="expiresInDays"
    :upload="screen.upload"
    :four-steps="fourSteps"
    @submit="create"
    @abort="screen.uiController.navigate('pricing', null)"/>
</template>

<script setup lang="ts">
import {computed, inject} from 'vue';
import {PricingPlan, SubmitJobOffer, UploadAssets} from "../../../main";
import {UiController, ViewListener} from "../ui";
import JobOfferForm from "./JobOfferForm.vue";

const screen = inject('screen') as Screen;

interface Screen {
  uiController: UiController;
  viewListener: ViewListener;
  upload: UploadAssets;
  pricingPlan: PricingPlan;
  applicationEmail: string;
}

function create(jobOffer: SubmitJobOffer): void {
  screen.viewListener.createJob(screen.pricingPlan, jobOffer);
}

const expiresInDays = computed(() => screen.pricingPlan === 'free' ? 14 : 30);
const fourSteps = computed(() => screen.pricingPlan !== 'free');

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
  applicationEmail: screen.applicationEmail,
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
