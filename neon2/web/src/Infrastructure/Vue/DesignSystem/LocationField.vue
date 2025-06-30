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
import {onMounted, ref} from "vue";
import {Location} from "../../../Application/JobBoard/Port/LocationInput";
import {useJobBoardService} from "../JobBoardView/View/vue";
import TextInputOutline from "./TextInputOutline.vue";

const modelLocation = defineModel<Location|null>({required: true});
const props = defineProps<Props>();
const service = useJobBoardService();
const locationField = ref<HTMLInputElement|null>(null);

interface Props {
  placeholder: string;
}

let previouslySelectedFormattedAddress: string = formatAddress(modelLocation.value);

const loading = ref<boolean>(true);

onMounted(() => {
  locationField.value!.value = formatAddress(modelLocation.value);
  service.mountLocationInput(locationField.value!, {
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
