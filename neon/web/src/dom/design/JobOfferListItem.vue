<template>
  <Design.Tile vertical>
    <Design.Row>
      <Design.Tile nested-pill v-for="badge in badges" :text="badge.title" :icon="badge.icon"/>
      <Design.RowEnd>
        <Design.JobOfferFavouriteButton
          :favourite="jobOffer.isFavourite"
          @favourite-change="toggleFavourite"/>
      </Design.RowEnd>
    </Design.Row>
    <Design.Tile nested space>
      <Design.Row vertical-center>
        <div class="size-14 rounded-lg overflow-hidden">
          <img v-if="props.jobOffer.companyLogoUrl" :src="props.jobOffer.companyLogoUrl"/>
          <div v-else class="size-14 rounded flex-shrink-0 flex items-center justify-center bg-accent-back text-accent-front">
            <Icon name="jobOfferLogoPlaceholder"/>
          </div>
        </div>
        <div class="flex-grow-1">
          <Design.Row vertical-center apart>
            <p class="text-lg leading-6" data-testid="jobOfferTitle">
              <a :href="jobOffer.url" v-text="jobOffer.title"/>
            </p>
            <div v-if="jobOffer.salary" class="max-md:hidden">
              <Design.Salary :salary="jobOffer.salary"/>
            </div>
          </Design.Row>
          <Design.Row apart class="max-md:hidden mt-2" vertical-center>
            <Design.Row vertical-center class="space-x-2">
              <span v-if="jobOffer.companyName" v-text="jobOffer.companyName"/>
              <div>
                <Design.TagList :tag-names="jobOffer.tagNames" :max="5"/>
              </div>
            </Design.Row>
            <Design.Row class="space-x-2 text-sm">
              <div v-for="badge in badges">
                <Icon :name="badge.icon" v-if="badge.icon"/>
                {{ badge.title }}
              </div>
            </Design.Row>
          </Design.Row>
        </div>
      </Design.Row>
      <div class="md:hidden">
        <Design.Divider/>
        <Design.Row vertical-center>
          <span>{{ jobOffer.companyName }}</span>
          <Design.RowEnd>
            <Design.Salary v-if="jobOffer.salary" :salary="jobOffer.salary"/>
          </Design.RowEnd>
        </Design.Row>
        <template v-if="jobOffer.tagNames.length">
          <Design.Divider/>
          <Design.TagList :tag-names="jobOffer.tagNames" :max="5"/>
        </template>
      </div>
    </Design.Tile>
  </Design.Tile>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {LegalForm, WorkMode} from "../../filters";
import Icon, {IconName} from "../component/Icon.vue";
import {VueJobOffer} from "../JobBoard.vue";
import {Design} from "./design";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffer: VueJobOffer;
}

interface Emit {
  (event: 'favouriteChange', favourite: boolean): void;
}

function toggleFavourite(newValue: boolean): void {
  emit('favouriteChange', newValue);
}

const badges = computed<Badge[]>((): Badge[] => {
  return [
    ...locationBadges.value,
    workModeBadge.value,
    {title: legalFormTitle.value},
  ];
});

const workModeBadge = computed<Badge>((): Badge => {
  const badges: Record<WorkMode, Badge> = {
    'stationary': {title: 'Praca stacjonarna'},
    'fullyRemote': {title: 'Praca zdalna'},
    'hybrid': {title: 'Praca hybrydowa'},
  };
  return badges[props.jobOffer.workMode];
});

const locationBadges = computed<Badge[]>((): Badge[] => {
  return props.jobOffer.locations.map(location => ({
    icon: 'jobOfferLocation',
    title: location,
  }));
});

const legalFormTitle = computed((): string => {
  const titles: Record<LegalForm, string> = {
    'fullTime': 'Pełny etat',
    'contract': 'Kontrakt',
    'partTime': 'Praca dorywcza',
  };
  return titles[props.jobOffer.legalForm];
});

interface Badge {
  title: string;
  icon?: IconName;
}
</script>
