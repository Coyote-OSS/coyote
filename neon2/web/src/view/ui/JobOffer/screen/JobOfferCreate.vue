<template>
  <JobOfferForm
    mode="create"
    :job-offer="newJobOffer"
    :job-offer-expires-in-days="expiresInDays"
    :tag-autocomplete="screen.tagAutocomplete"
    :upload="screen.upload"
    :four-steps="fourSteps"
    @submit="create"
    @abort="service.navigate('pricing', null)"/>
</template>

<script setup lang="ts">
import {computed, inject} from 'vue';
import {UploadAssets} from "../../../../main";
import {JobBoardService} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {jobBoardServiceInjectKey} from "../../../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {SubmitJobOffer} from "../../../../neon3/Packages/Feature/JobBoard/Application/Model";
import {PricingPlan} from "../../../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {TagAutocomplete} from "../../ui";
import JobOfferForm from "../JobOfferForm.vue";

const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;
const screen = inject('screen') as Screen;

interface Screen {
  tagAutocomplete: TagAutocomplete;
  upload: UploadAssets;
  pricingPlan: PricingPlan;
  applicationEmail: string;
}

function create(jobOffer: SubmitJobOffer): void {
  service.createJob(screen.pricingPlan, jobOffer);
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
