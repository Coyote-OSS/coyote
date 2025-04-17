<template>
  <Design.Card title="Podstawowe informacje">
    <label>
      Tytuł ogłoszenia
      <Design.TextField placeholder="np. Senior Java Developer" v-model="jobOffer.title"/>
    </label>
  </Design.Card>
  <Design.Card title="Opis ogłoszenia">
    <label>
      Opis ogłoszenia
      <Design.TextField placeholder="Miejsce na szczegółowy opis oferty" v-model="jobOffer.description"/>
    </label>
  </Design.Card>
  <Design.Card title="Zatrudniająca firma">
    <label>
      Nazwa firmy
      <Design.TextField placeholder="Podaj nazwę firmy dla której chcesz dodać ogłoszenie" v-model="jobOffer.companyName"/>
    </label>
  </Design.Card>
  <Design.Tile>
    <Design.Row>
      <Design.RowEnd>
        <Design.Button primary @click="emit('submit', jobOffer)">
          {{buttonTitle}}
        </Design.Button>
      </Design.RowEnd>
    </Design.Row>
  </Design.Tile>
</template>

<script setup lang="ts">
import {computed, reactive} from 'vue';
import {SubmitJobOffer} from "../../../main";
import {Design} from "../design/design";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOffer: SubmitJobOffer;
  mode: 'create'|'update';
}

interface Emit {
  (event: 'submit', jobOffer: SubmitJobOffer): void;
}

const jobOffer: SubmitJobOffer = reactive<SubmitJobOffer>({...props.jobOffer});
const buttonTitle = computed<string>(() => {
  if (props.mode === 'create') {
    return 'Dodaj';
  }
  return 'Zapisz';
});
</script>
