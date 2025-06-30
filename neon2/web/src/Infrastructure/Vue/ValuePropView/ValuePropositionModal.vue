<template>
  <div class="backdrop-blur-xs absolute w-full h-full z-[1]"/>
  <div class="fixed z-[2] left-0 right-0 flex justify-center items-center h-full -mt-12">
    <div
      class="min-h-88  bg-tile rounded-lg flex max-md:flex-col">
      <img class="w-full rounded-l-lg max-md:h-48 md:w-64 lg:w-88 object-cover" :src="logo" alt="4programmers.net"/>
      <div class="flex flex-col justify-between p-4">
        <template v-if="!secondScreen">
          <div class="space-y-4">
            <h2 class="font-semibold text-lg">
              Chcesz zwiększyć swoje szanse?
            </h2>
            <h3 class="font-medium">Skorzystaj z usług agenta 4programmers, a zyskasz:</h3>
            <ul class="list-disc pl-4">
              <li>CV konkurencji (zanonimizowane) – zobacz, z kim rywalizujesz.</li>
              <li>Informacja, gdzie i dlaczego odpadli inni.</li>
              <li>Gwarancja odpowiedzi – jeśli firma milczy, my ich przypilnujemy.</li>
              <li>Notatki z rozmów z rekruterem.</li>
            </ul>
          </div>
          <div class="flex flex-col space-y-2 mt-8">
            <Design.Button primary @click="accept(true)">Tak</Design.Button>
            <Design.Button primary-outline @click="accept(false)">Nie jestem zainteresowany</Design.Button>
          </div>
        </template>
        <template v-else>
          <div class="max-w-94 space-y-4 mb-8">
            <h2 class="font-semibold text-lg">
              Skorzystaj z agenta 4programmers
            </h2>
            <p>
              Pomożemy Ci przejść rekrutację w <span class="font-medium" v-text="props.companyName"/>
              krok po kroku z <b>gwarancją feedbacku</b>.
            </p>
            <template v-if="!emailSubscribed">
              <p>Zostaw swój adres e-mail, a zaprosimy Cię na rozmowę.</p>
              <Design.TextField placeholder="Twój adres e-mail" v-model="email" class="mb-2"/>
              <Design.Button primary @click="subscribe" class="w-full">Zapisz</Design.Button>
            </template>
            <p v-else>
              Dziękujemy! Skontaktujemy się z Tobą!
            </p>
          </div>
          <div class="mt-auto space-x-2 flex items-center">
            <Design.Button primary-outline icon="vpBack" square @click="back"/>
            <Design.Button class="flex-grow-1" @click="apply" :primary-outline="!emailSubscribed"
                           :primary="emailSubscribed">
              Kontynuuj aplikację
            </Design.Button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref} from "vue";
import {Design} from "../DesignSystem/design";
import {ValuePropositionEvent} from "../../../Domain/ValueProp/Model";
import logo from "./survey.svg";

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

interface Props {
  companyName: string;
}

interface Emits {
  (event: 'accept', _event: ValuePropositionEvent, email?: string): void;
}

const secondScreen = ref<boolean>(false);
const email = ref<string>('');
const emailSubscribed = ref<boolean>(false);

function accept(accepted: boolean): void {
  if (accepted) {
    emit('accept', 'vpAccepted');
  } else {
    emit('accept', 'vpDeclined');
  }
  secondScreen.value = true;
}

function back(): void {
  secondScreen.value = false;
}

function subscribe(): void {
  emit('accept', 'vpSubscribed', email.value);
  emailSubscribed.value = true;
}

function apply(): void {
  emit('accept', 'vpApply');
}
</script>
