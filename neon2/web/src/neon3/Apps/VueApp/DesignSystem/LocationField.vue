<template>
  <TextInputOutline>
    <input
      class="outline-none flex-grow-1"
      autocomplete="off"
      ref="locationField"
      :placeholder="loading ? 'Wczytywanie...' : props.placeholder"
      :disabled="loading"/>
  </TextInputOutline>
</template>

<script setup lang="ts">
import {inject, onMounted, ref} from "vue";
import {Location, LocationInput} from "../../../Packages/Core/Application/LocationInput";
import TextInputOutline from "./TextInputOutline.vue";

const modelLocation = defineModel<Location|null>({required: true});
const props = defineProps<Props>();

interface Props {
  placeholder: string;
}

const locationInput: LocationInput = inject('locationInput')!;
const locationField = ref<HTMLInputElement|null>(null);

let previouslySelectedFormattedAddress: string = formatAddress(modelLocation.value);

const loading = ref<boolean>(true);

onMounted(() => {
  locationField.value!.value = formatAddress(modelLocation.value);
  locationInput.mount(locationField.value!, {
    mounted(): void {
      loading.value = false;
    },
    select(location: Location): void {
      previouslySelectedFormattedAddress = locationField.value!.value;
      modelLocation.value = location;
    },
    abort(): void {
      if (locationField.value!.value === '') {
        modelLocation.value = null;
      } else {
        locationField.value!.value = previouslySelectedFormattedAddress;
      }
    },
  });
});

function formatAddress(location: Location|null): string {
  if (location === null) {
    return '';
  }
  return [location.streetName, location.streetNumber, location.city, location.countryCode]
    .filter(component => component)
    .join(', ');
}
</script>
