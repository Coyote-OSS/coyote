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
import {inject} from "vue";
import {Design} from "../../../../DesignSystem/design";
import {JobBoardService} from "../../JobBoardService";
import {jobBoardServiceInjectKey} from "../../vue";
import {PlanBundle} from "../../../../../../Packages/Feature/JobBoard/Application/Model";
import JobOfferStepper from './JobOfferStepper.vue';

const props = defineProps<Props>();

interface Props {
  planBundle: PlanBundle;
  jobOfferId: number;
}

const service = inject<JobBoardService>(jobBoardServiceInjectKey)!;

function redeemBundle(): void {
  service.redeemBundle(props.jobOfferId);
}

function capitalize(string: string): string {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>
