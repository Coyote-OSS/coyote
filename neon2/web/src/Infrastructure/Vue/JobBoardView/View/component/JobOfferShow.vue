<template>
  <Design.Toast v-if="props.jobOffer.expired" title="Oferta wygasła."/>
  <Design.Tile class="p-4 md:p-8">
    <div :class="[
      'h-16 rounded-lg -m-2 md:-m-6 mb-0',
      'bg-linear-to-r from-green-050 dark:from-green-800 to-tile'
    ]"/>
    <Design.Row vertical-center class="-mt-7 mb-8">
      <Design.Image
        border
        :src="props.jobOffer.companyLogoUrl"
        placeholder-icon="jobOfferLogoPlaceholder"
        class="mr-auto"/>
      <JobOfferFavouriteButton
        :model-value="props.jobOffer.favourite"
        @update:model-value="markAsFavourite"/>
      <Design.Button @click="editJob" v-if="props.canEdit">Edytuj</Design.Button>
      <Design.Button
        :primary="!props.preview"
        :primary-outline="props.preview"
        :disabled="props.jobOffer.expired"
        @click="apply">
        Aplikuj
        <Icon class="ml-2" name="jobOfferApplyExternally" v-if="props.jobOffer.applyExternally"/>
      </Design.Button>
    </Design.Row>
    <div class="mb-8 md:flex md:justify-between md:items-start max-md:space-y-6">
      <div>
        <h1
          class="text-2xl md:text-3xl md:mb-2 font-medium text-neutral2-900"
          data-testid="jobOfferTitle"
          v-text="props.jobOffer.title"/>
        <h2
          class="text-lg text-neutral2-700"
          v-text="props.jobOffer.companyName"
          data-testid="jobOfferCompanyName"/>
      </div>
      <div>
        <div class="md:flex md:justify-end">
          <JobOfferSalary :salary="salary" nowrap v-if="salary"/>
          <JobOfferSalaryNotProvided v-else/>
        </div>
        <div class="mt-6 md:mt-3 text-neutral2-500 flex flex-wrap md:justify-end gap-x-4 gap-y-2">
          <span v-for="field in catchEyeFields" class="whitespace-nowrap">
            <Icon :name="field.icon" class="mr-1"/>
            {{field.title}}
          </span>
        </div>
      </div>
    </div>
    <div class="md:flex max-md:space-y-12">
      <div :class="[
          'md:w-2/3 pr-8',
          'text-neutral2-700',
          'job-offer-description list-style paragraph-style'
        ]">
        <div
          v-if="props.jobOffer.description"
          v-html="props.jobOffer.description"
          data-testid="jobOfferDescription"/>
        <div class="my-4 space-y-3" v-if="
            props.jobOffer.companyDescription || 
            props.jobOffer.companyVideoUrl ||
            props.jobOffer.companyPhotoUrls.length > 0 ||
            props.jobOffer.companyAddress">
          <p><b>O firmie</b></p>
          <div
            v-html="props.jobOffer.companyDescription"
            v-if="props.jobOffer.companyDescription"/>
          <VideoPlayer
            v-if="props.jobOffer.companyVideoUrl"
            :youtube-url="props.jobOffer.companyVideoUrl"/>
          <div
            class="grid grid-cols-2 md:grid-cols-3 gap-4"
            v-if="props.jobOffer.companyPhotoUrls.length">
            <img
              v-for="photoUrl in props.jobOffer.companyPhotoUrls"
              class="w-full rounded-lg"
              :src="photoUrl"
              alt="company photo">
          </div>
          <div
            v-if="props.jobOffer.companyAddress"
            ref="address" class="w-full h-54 lg:h-76"/>
        </div>
      </div>
      <div class="md:w-1/3 md:relative">
        <div class="space-y-3 md:sticky md:top-14">
          <Design.Card space border>
            <h2 :class="['mb-5', titleClass]" v-text="props.jobOffer.title"/>
            <JobOfferField
              title="Tryb pracy"
              :value="formatWorkMode(props.jobOffer.workMode)"
              icon="jobOfferWorkModeRemote"/>
            <JobOfferField
              title="Lokalizacja"
              :value="props.jobOffer.locationCities.join(', ')"
              v-if="props.jobOffer.locationCities.length"
              icon="jobOfferLocation"/>
            <JobOfferField
              title="Rodzaj umowy"
              :value="formatLegalForm(props.jobOffer.legalForm)"
              icon="jobOfferLegalForm"/>
            <JobOfferField
              title="Poziom Doświadczenia"
              :value="formatWorkExperience(props.jobOffer.experience)"
              v-if="props.jobOffer.experience !== 'not-provided'"
              icon="jobOfferWorkExperience"/>
            <JobOfferField title="Wynagrodzenie" v-if="salary">
              <span class="text-green-500">{{formatSalary(salary)}}</span>
            </JobOfferField>
            <JobOfferField
              title="Wygasa"
              :value="formatExpiresInDays(props.jobOffer.expiresInDays)"
              v-if="props.jobOffer.expiresInDays"
              test-id="jobOfferExpiresInDays"/>
            <JobOfferField
              title="Wygasła"
              :value="props.jobOffer.expiryDate"
              v-if="props.jobOffer.expiryDate"
              icon="jobOfferExpiredOn"/>
            <Design.Button
              :primary="!props.preview"
              :primary-outline="props.preview"
              :disabled="props.jobOffer.expired"
              full-width
              @click="apply">
              Aplikuj
              <Icon class="ml-2" name="jobOfferApplyExternally" v-if="props.jobOffer.applyExternally"/>
            </Design.Button>
          </Design.Card>
          <Design.Card space border v-if="props.jobOffer.tags.length">
            <h1 class="text-neutral2-500 mb-2">Technologie</h1>
            <JobOfferTagPriorities :tags="props.jobOffer.tags"/>
          </Design.Card>
          <Design.Card space border v-if="companyCard">
            <div class="flex items-center">
              <Design.Image
                class="mr-2 flex-shrink-0"
                size="small"
                :src="props.jobOffer.companyLogoUrl"
                placeholder-icon="jobOfferLogoPlaceholder"/>
              <span :class="titleClass" v-text="props.jobOffer.companyName"/>
            </div>
            <JobOfferField
              link
              icon="jobOfferCompanyWebsite"
              title="Witryna firmy"
              :value="props.jobOffer.companyWebsiteUrl"
              v-if="props.jobOffer.companyWebsiteUrl"/>
            <JobOfferField
              icon="jobOfferCompanyFundingYear"
              title="Rok założenia firmy"
              :value="props.jobOffer.companyFundingYear.toString()"
              v-if="props.jobOffer.companyFundingYear"/>
            <JobOfferField
              icon="jobOfferCompanySize"
              title="Rozmiar firmy"
              :value="formatCompanySizeLevel(props.jobOffer.companySizeLevel)"
              v-if="props.jobOffer.companySizeLevel"/>
          </Design.Card>
        </div>
      </div>
    </div>
  </Design.Tile>
</template>

<script setup lang="ts">
import {computed, onMounted, ref} from "vue";
import {Design} from "../../../DesignSystem/design";
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";
import {useJobBoardService} from "../vue";
import {
  formatCompanySizeLevel,
  formatExpiresInDays,
  formatLegalForm,
  formatWorkExperience,
  formatWorkMode,
} from "./format";
import JobOfferFavouriteButton from "./JobOfferFavouriteButton.vue";
import JobOfferField from "./JobOfferField.vue";
import {fewLocations} from "./JobOfferListItem";
import {formatSalary, SalaryJobOffer} from "./JobOfferSalary";
import JobOfferSalary from "./JobOfferSalary.vue";
import JobOfferSalaryNotProvided from "./JobOfferSalaryNotProvided.vue";
import {JobOfferShow} from "./JobOfferShow";
import JobOfferTagPriorities from "./JobOfferTagPriorities.vue";
import VideoPlayer from "./VideoPlayer.vue";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();
const service = useJobBoardService();

interface Props {
  jobOffer: JobOfferShow;
  preview?: boolean;
  canEdit: boolean;
}

interface Emit {
  (event: 'edit'): void;
  (event: 'apply'): void;
  (event: 'favourite', favourite: boolean): void;
}

function editJob(): void {
  emit('edit');
}

function apply(): void {
  emit('apply');
}

function markAsFavourite(favourite: boolean): void {
  emit('favourite', favourite);
}

const titleClass = 'text-lg text-neutral2-900';

const address = ref<HTMLElement|null>(null);
onMounted(() => {
  if (props.jobOffer.companyAddress === null) {
    return;
  }
  service.mountLocationDisplay(
    address.value!,
    props.jobOffer.companyAddress.latitude,
    props.jobOffer.companyAddress.longitude);
});

interface CatchEyeField {
  title: string;
  icon: IconName;
}

const catchEyeFields = computed((): CatchEyeField[] => {
  const fields: CatchEyeField[] = [
    {icon: 'jobOfferWorkModeRemote', title: formatWorkMode(props.jobOffer.workMode)},
  ];
  if (props.jobOffer.locationCities.length) {
    fields.push({icon: 'jobOfferLocation', title: fewLocations(props.jobOffer.locationCities).join(', ')});
  }
  fields.push({icon: 'jobOfferLegalForm', title: formatLegalForm(props.jobOffer.legalForm)});
  return fields;
});

const salary = computed<SalaryJobOffer|null>(() => {
  if (props.jobOffer.salaryRangeFrom && props.jobOffer.salaryRangeTo) {
    return {...props.jobOffer} as SalaryJobOffer;
  }
  return null;
});

const companyCard = computed<boolean>((): boolean => {
  return !!props.jobOffer.companyWebsiteUrl ||
    !!props.jobOffer.companyFundingYear ||
    !!props.jobOffer.companySizeLevel;
});
</script>

<style>
@import "../../../DesignSystem/tailwind.css";

.job-offer-description b {
  @apply text-neutral2-800;
}
</style>
