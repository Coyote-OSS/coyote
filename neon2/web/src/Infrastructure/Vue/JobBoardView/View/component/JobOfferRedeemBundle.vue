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
import {PlanBundle} from "../../../../../Application/JobBoard/Model";
import {Design} from "../../../DesignSystem/design";
import {useJobBoardService} from "../vue";
import JobOfferStepper from './JobOfferStepper.vue';

const props = defineProps<Props>();

interface Props {
  planBundle: PlanBundle;
  jobOfferId: number;
}

const service = useJobBoardService();

function redeemBundle(): void {
  service.redeemBundle(props.jobOfferId);
}

function capitalize(string: string): string {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>
