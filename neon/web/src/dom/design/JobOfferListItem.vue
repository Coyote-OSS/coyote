<template>
  <Design.Tile vertical>
    <Design.Row>
      <Design.Tile nested-pill icon="jobOfferLocation" v-for="location in jobOffer.locations">
        {{ location }}
      </Design.Tile>
      <Design.Tile nested-pill icon="jobOfferWorkModeRemote" v-if="jobOffer.workMode === 'remote'">
        Praca zdalna
      </Design.Tile>
      <Design.Tile nested-pill icon="jobOfferWorkModeStationary" v-if="jobOffer.workMode === 'stationary'">
        Praca stacjonarna
      </Design.Tile>
    </Design.Row>
    <Design.Tile nested>
      <div class="flex">
        <div class="size-12 rounded flex-shrink-0 flex items-center justify-center bg-accent-back text-accent-front">
          <Icon name="jobOfferLogoPlaceholder"/>
        </div>
        <div class="flex-grow-1">
          <p class="text-lg leading-6" data-testid="jobOfferTitle">
            <a :href="jobOffer.url" v-text="jobOffer.title"/>
          </p>
          {{ jobOffer.companyName }}
        </div>
        <div v-if="jobOffer.salary">
          <Design.Salary :salary="jobOffer.salary"/>
        </div>
      </div>
    </Design.Tile>
  </Design.Tile>
</template>

<script setup lang="ts">
import {VueJobOffer} from "../../Main.vue";
import Icon from "../component/Icon.vue";
import {Design} from "./design";

interface Props {
  jobOffer: VueJobOffer;
}

const props = defineProps<Props>();
</script>
