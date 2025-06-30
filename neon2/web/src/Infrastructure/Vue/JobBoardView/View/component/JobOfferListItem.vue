<template>
  <Design.Tile vertical :class="[
      'border-[1.5px] border-transparent',
      'hover:border-neutral2-200',
      'transition-[border-color,box-shadow] duration-150',
      'hover:shadow-lg shadow-neutral2-300/10'
    ]">
    <Design.Row wrap vertical-center>
      <Design.Tile nested-pill v-for="badge in badges" :text="badge.title" :icon="badge.icon"/>
      <Design.RowEnd inline>
        <JobOfferBadge text-small color="gray" v-text="'Oczekuje na płatność'"
                       v-if="jobOffer.status === 'awaitingPayment'"/>
        <JobOfferBadge text-small color="gray" v-text="'Wygasła'"
                       v-else-if="jobOffer.status === 'expired'"/>
        <JobOfferBadge text-small color="pink" v-text="'Nowe'"
                       v-else-if="jobOffer.isNew"/>
        <JobOfferFavouriteButton
          :model-value="jobOffer.isFavourite"
          @update:model-value="markAsFavourite"/>
      </Design.RowEnd>
    </Design.Row>
    <Design.Tile nested desktop-space :href="props.jobOfferUrl" @click="click">
      <Design.Row vertical-center>
        <Design.Image
          class="flex-shrink-0"
          :src="props.jobOffer.companyLogoUrl"
          placeholder-icon="jobOfferLogoPlaceholder"/>
        <div class="flex-grow-1">
          <Design.Row vertical-center apart>
            <p class="text-lg leading-6" v-text="jobOffer.title" data-testid="jobOfferTitle"/>
            <div class="max-md:hidden">
              <JobOfferSalary :salary="jobOfferSalary" v-if="jobOfferSalary"/>
              <JobOfferSalaryNotProvided v-else/>
            </div>
          </Design.Row>
          <Design.Row apart class="max-md:hidden mt-2" vertical-center>
            <Design.Row vertical-center class="space-x-2">
              <span v-if="jobOffer.companyName" v-text="jobOffer.companyName" class="text-neutral2-900"/>
              <JobOfferTagList :tag-names="jobOfferTagNames(jobOffer)" :max="5"/>
            </Design.Row>
            <Design.Row class="space-x-2 text-sm">
              <div v-for="badge in badges">
                <Icon :name="badge.icon" v-if="badge.icon"/>
                {{badge.title}}
              </div>
            </Design.Row>
          </Design.Row>
        </div>
      </Design.Row>
      <div class="md:hidden">
        <Design.Divider/>
        <Design.Row wrap vertical-center>
          <span>{{jobOffer.companyName}}</span>
          <Design.RowEnd>
            <JobOfferSalary :salary="jobOfferSalary" v-if="jobOfferSalary"/>
            <JobOfferSalaryNotProvided v-else/>
          </Design.RowEnd>
        </Design.Row>
        <template v-if="jobOffer.tags.length">
          <Design.Divider/>
          <Design.Row>
            <JobOfferTagList :tag-names="jobOfferTagNames(jobOffer)" :max="5"/>
          </Design.Row>
        </template>
      </div>
    </Design.Tile>
  </Design.Tile>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {JobOffer} from "../../../../../Domain/JobBoard/JobOffer";
import {Design} from "../../../DesignSystem/design";
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";
import {formatLegalForm, formatWorkMode} from "./format";
import JobOfferBadge from "./JobOfferBadge.vue";
import JobOfferFavouriteButton from "./JobOfferFavouriteButton.vue";
import {fewLocations} from "./JobOfferListItem";
import {SalaryJobOffer} from "./JobOfferSalary";
import JobOfferSalary from "./JobOfferSalary.vue";
import JobOfferSalaryNotProvided from "./JobOfferSalaryNotProvided.vue";
import JobOfferTagList from "./JobOfferTagList.vue";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffer: JobOffer;
  jobOfferUrl: string;
}

interface Emit {
  (event: 'favourite', favourite: boolean): void;
  (event: 'select'): void;
}

function markAsFavourite(newValue: boolean): void {
  emit('favourite', newValue);
}

const badges = computed<Badge[]>((): Badge[] => {
  return [
    ...locationBadges.value,
    workModeBadge.value,
    {title: legalFormTitle.value},
  ];
});

const workModeBadge = computed<Badge>((): Badge => ({title: formatWorkMode(props.jobOffer.workMode)}));

const locationBadges = computed<Badge[]>((): Badge[] => {
  const cities = props.jobOffer.locations.map(location => location.city ?? 'Nie podano');
  const fewCities = fewLocations(cities);
  return fewCities.map(title => ({title, icon: 'jobOfferLocation'}));
});

const legalFormTitle = computed((): string => formatLegalForm(props.jobOffer.legalForm));

interface Badge {
  title: string;
  icon?: IconName;
}

const jobOfferSalary = computed<SalaryJobOffer|null>((): SalaryJobOffer|null => {
  if (props.jobOffer.salaryRangeFrom && props.jobOffer.salaryRangeTo) {
    return {
      ...props.jobOffer,
      salaryRangeFrom: props.jobOffer.salaryRangeFrom,
      salaryRangeTo: props.jobOffer.salaryRangeTo,
    };
  }
  return null;
});

function click(event: MouseEvent): void {
  if (!opensInNewTab(event)) {
    event.preventDefault();
    emit('select');
  }
}

function opensInNewTab(event: MouseEvent): boolean {
  return event.ctrlKey || event.metaKey;
}

function jobOfferTagNames(jobOffer: JobOffer): string[] {
  return jobOffer.tags.map(tag => tag.tagName);
}
</script>
