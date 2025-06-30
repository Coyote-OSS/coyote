<template>
  <div class="flex flex-wrap gap-2" v-if="locationFields.length">
    <Design.Tile
      nested-pill
      v-for="field in locationFields"
      :text="field.location?.city || 'Podaj lokalizację...'"
      icon="jobOfferLocation"/>
  </div>
  <Design.FieldGroup label="Lokalizacja" v-for="(locationField, index) in locationFields">
    <div class="flex gap-2 items-center">
      <LocationField
        class="flex-grow-1"
        :key="locationField.id"
        :placeholder="placeholder(index)"
        v-model="locationField.location"
        @update:model-value="update"/>
      <Design.Button square outline icon="remove" @click="removeLocation(locationField.id)"/>
    </div>
  </Design.FieldGroup>
  <Design.Button primary-outline icon="add" @click="addNewLocation(null)">
    Dodaj lokalizację
  </Design.Button>
</template>

<script setup lang="ts">
import {ref} from "vue";
import {Design} from "../../../DesignSystem/design";
import LocationField from "../../../DesignSystem/LocationField.vue";
import {Location} from "../../../../../Application/JobBoard/Port/LocationInput";

const model = defineModel<Location[]>({required: true});
const locationFields = ref<LocationField[]>([]);
let lastId = 0;

for (const location of model.value) {
  addNewLocation(location);
}

interface LocationField {
  id: number;
  location: Location|null;
}

function removeLocation(fieldId: number): void {
  locationFields.value.splice(
    locationFields.value.findIndex(l => l.id === fieldId),
    1);
  update();
}

function addNewLocation(location: Location|null): void {
  locationFields.value.push({
    id: lastId++,
    location,
  });
}

function update(): void {
  model.value = locationFields.value
    .map(field => field.location)
    .filter(location => location !== null)
    .filter(location => location.city !== null);
}

function placeholder(index: number): string {
  const placeholders = [
    'np. Warszawa, al. Jerozolimskie 3',
    'np. Kraków, al. Juliusza Słowackiego 12',
    'np. Wrocław, ul. Józefa Piłsudskiego 24',
  ];
  return placeholders[index % placeholders.length];
}
</script>
