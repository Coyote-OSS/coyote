<template>
  <JobOfferStepper four-steps step="publish"/>
  <Design.Card title="Wykorzystaj swój pakiet">
    Pozostało {{props.planBundle.remainingJobOffers}} ogłoszeń dostępnych w pakiecie
    {{capitalize(props.planBundle.bundleName)}}.
  </Design.Card>
  <Design.Tile>
    <Design.Row>
      <Design.RowEnd>
        <Design.Button primary @click="redeemBundle">
          Publikuj korzystając z pakietu
        </Design.Button>
      </Design.RowEnd>
    </Design.Row>
  </Design.Tile>
</template>

<script setup lang="ts">
import {Design} from "../../../neon3/Core/View/DesignSystem/design";
import {PlanBundle, ViewListener} from "../ui";
import JobOfferStepper from './JobOfferStepper.vue';

const props = defineProps<Props>();

interface Props {
  viewListener: ViewListener;
  jobOfferId: number;
  planBundle: PlanBundle;
}

function redeemBundle(): void {
  props.viewListener.redeemBundle(props.jobOfferId);
}

function capitalize(string: string): string {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>
