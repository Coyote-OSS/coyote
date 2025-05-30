<template>
  <div class="neon-tile neon-rounded px-4 py-2">
    <div class="border-bottom" v-if="hasBundle">
      <label class="col-form-label">
        Opłacony pakiet
      </label>
      <div class="mb-2">
        <p class="mb-0">
          Pakiet: Plan {{ userPlanBundle.planName }}
        </p>
        Wykorzystujesz: <strong>{{ userPlanBundle.remaining }}</strong> z <strong>{{ userPlanBundle.bundleSize }}</strong> ofert pracy.
      </div>
    </div>
    <div class="border-bottom" v-else>
      <div class="mb-2">
        {{ plan.name }} ({{ plan.price }}zł)
        <a href="/Praca/Oferta" class="neon-color-link-light">
          <u>Zmień</u>
        </a>
      </div>
    </div>
    <div class="form-row border-bottom">
      <vue-form-group :errors="errors['title']" class="col-sm-9">
        <template v-slot:label>
          <label class="col-form-label">Tytuł oferty <em>*</em></label>
        </template>
        <vue-text name="title" v-model="job.title" placeholder="Np. Senior Java Developer" :maxlength="titleMaxLength" :is-invalid="'title' in errors"></vue-text>
        <span class="form-text text-muted">
          Pozostało <strong>{{ jobTitleCharactersRemaining }}</strong> znaków
        </span>
      </vue-form-group>
      <vue-form-group class="col-sm-3" label="Staż pracy">
        <vue-select name="seniority" v-model="job.seniority" :options="seniorities" placeholder="--"></vue-select>
      </vue-form-group>
    </div>
    <div class="form-group border-bottom">
      <label class="col-form-label">
        Lokalizacja
      </label>
      <div v-for="(location, index) in job.locations" class="row mb-2">
        <div class="col-sm-12">
          <div class="input-group">
            <span title="Usuń lokalizację" class="input-group-text text-decoration-none text-danger cursor-pointer" @click="REMOVE_LOCATION(location)" v-if="job.locations.length > 1">
              <vue-icon name="jobOfferLocationRemove"/>
            </span>
            <vue-google-place @change="location => setLocation(index, location)" :label="location.label"></vue-google-place>
          </div>
        </div>
      </div>
      <span title="Dodaj więcej lokalizacji" class="btn btn-secondary" @click="addLocation" v-if="canAddLocation">
        <vue-icon name="jobOfferLocationAdd"/>
        Dodaj lokalizację
      </span>
      <div class="row">
        <div class="col-12">
          <div class="mt-2 mb-3 d-flex align-items-center">
            <vue-checkbox id="is_remote" name="is_remote" v-model="job.is_remote" class="me-2"/>
            <label for="is_remote" class="me-2">Możliwa praca zdalna w zakresie</label>
            <vue-select
              name="remote_range"
              :disabled="!job.is_remote"
              :options="remoteRange"
              v-model="job.remote_range"
              class="form-control-sm d-inline-block"
              style="width: 100px;"/>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group border-bottom">
      <label class="col-form-label">
        Ofertowane wynagrodzenie netto i forma zatrudnienia
      </label>
      <div>
        od
        <vue-text type="number" name="salary_from" class="d-inline-block" v-model="job.salary_from" :is-invalid="'salary_from' in errors"/>
        do
        <vue-text type="number" name="salary_to" class="d-inline-block" v-model="job.salary_to" :is-invalid="'salary_to' in errors"/>
        {{ ' ' }}
        <vue-select name="currency_id" class="d-inline-block" :options="currenciesValues" v-model="job.currency_id"/>
        {{ ' ' }}
        <vue-select name="is_gross" class="d-inline-block" :options="['Netto', 'Brutto']" v-model="isGross"/>
        {{ ' ' }}
        <vue-select name="rate" class="d-inline-block" :options="rates" v-model="job.rate"/>
        {{ ' ' }}
        <vue-select name="employment" class="d-inline-block" :options="employments" v-model="job.employment"/>
        <span class="form-text text-muted">
          Podanie tych informacji nie jest obowiązkowe, ale dzięki temu Twoja oferta zainteresuje więcej osób. Obiecujemy!
        </span>
        <vue-error :message="errors.salary_from"/>
        <vue-error :message="errors.salary_to"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-form-label">Kluczowe technologie (konieczne lub mile widziane)</label>
      <vue-tags-inline @change="addTag" placeholder="Np. java, python, kotlin, aws, docker, typescript, sql, nginx" class="form-control"></vue-tags-inline>
      <span class="form-text text-muted" v-if="errors.tags != null">{{ errors.tags[0] }}</span>
      <span class="form-text text-muted" v-else-if="suggestions.length === 0">Wybierz z listy lub wpisz nazwę języka/technologii i naciśnij Enter, aby dodać wymaganie.</span>
      <span class="form-text text-muted" v-else-if="suggestions.length > 0">
        Podpowiedź:
        <template v-for="(suggestion, index) in suggestions">
          <a href="javascript:" class="tag-suggestion" @click="addTag({ name: suggestion })">{{ suggestion }}</a>{{ index < suggestions.length - 1 ? ', ' : '' }}
        </template>
      </span>
      <vue-tags :tags="job.tags" :editable="true" @delete="REMOVE_TAG" :tooltips="['mile widziane', 'średnio zaawansowany', 'zaawansowany']" class="tag-clouds-md mt-3"/>
    </div>

    <div class="border-bottom form-group">
      <label class="col-form-label">Opis oferty</label>
      <vue-rich-editor v-model="job.description"/>
      <span class="form-text text-muted">Miejsce na szczegółowy opis oferty. Pole nie jest wymagane.</span>
      <input type="hidden" name="description" v-model="job.description">
    </div>

    <div class="form-group">
      <div class="custom-control custom-radio">
        <input
          type="radio"
          id="applyTypeService"
          v-model="job.apply_type"
          value="service"
          class="custom-control-input"
          @change="changeApplicationCriteria">
        <label for="applyTypeService" class="custom-control-label ms-1">
          Zezwól na wysyłanie CV przez serwis 4programmers.net
        </label>
      </div>
      <div class="row mt-2" v-if="job.apply_type === 'service'">
        <div class="col-sm-6">
          <vue-text name="email" v-model="job.email" :is-invalid="'email' in errors"/>
          <span class="form-text text-muted">Adres e-mail nie będzie widoczny dla osób postronnych.</span>
          <vue-error :message="errors.email"/>
        </div>
      </div>
    </div>
    <div class="form-group">
      <div class="custom-control custom-radio">
        <input type="radio" id="applyTypeExternal" v-model="job.apply_type" value="external" class="custom-control-input" @change="changeApplicationCriteria">
        <label for="applyTypeExternal" class="custom-control-label ms-1">
          Podaj odnośnik do zewnętrznego systemu aplikacji
        </label>
      </div>
      <div class="mt-2" v-if="job.apply_type === 'external'">
        <vue-text placeholder="https://your-application-system.com/" v-model="job.application_url"/>
        <span class="form-text text-muted">Podaj adres URL do Twojego systemu zarządzania aplikacjami.</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import axios, {AxiosResponse} from 'axios';
import {mapMutations} from 'vuex';

import store from '../../store/index';
import {Employment, Rate, Seniority, Tag} from '../../types/models';
import VueButton from '../forms/button.vue';
import VueCheckbox from '../forms/checkbox.vue';
import VueError from '../forms/error.vue';
import VueFormGroup from '../forms/form-group.vue';
import VueRadio from '../forms/radio.vue';
import VueSelect from '../forms/select.vue';
import VueTagsInline from '../forms/tags-inline.vue';
import VueText from '../forms/text.vue';
import VueGooglePlace from '../google-maps/place.vue';
import VueIcon from "../icon";
import VueTags from '../tags.vue';
import VueRichEditor from "./rich-editor.vue";

export default {
  name: 'VueForm',
  components: {
    VueIcon,
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-checkbox': VueCheckbox,
    'vue-radio': VueRadio,
    'vue-button': VueButton,
    'vue-error': VueError,
    'vue-tags-inline': VueTagsInline,
    'vue-tags': VueTags,
    'vue-google-place': VueGooglePlace,
    'vue-rich-editor': VueRichEditor,
  },
  props: {
    job: {type: Object, required: true},
    currencies: {type: Array, required: true},
    errors: {type: Object, required: false},
    plan: {type: Object},
    userPlanBundle: {type: Object, required: false},
  },
  data() {
    return {
      suggestions: {},
      titleMaxLength: 60,
    };
  },
  created(): void {
    if (!this.$props.job.apply_type) {
      this.$props.job.apply_type = 'service';
    }
  },
  methods: {
    ...mapMutations('jobs', ['REMOVE_LOCATION', 'SET_LABEL', 'ADD_TAG', 'REMOVE_TAG', 'TOGGLE_FEATURE']),
    addLocation(): void {
      if (this.canAddLocation) {
        this.$store.commit('jobs/ADD_LOCATION');
      }
    },
    setLocation(index, location) {
      store.commit('jobs/SET_LOCATION', {index, location});
    },
    addTag(tag: Tag) {
      store.commit('jobs/ADD_TAG', tag.name);
      // fetch only tag name
      let pluck = this.job.tags.map(item => item.name);
      // request suggestions
      axios.get<any>('/Praca/Tag/Suggestions', {params: {t: pluck}})
        .then((response: AxiosResponse<any>) => this.suggestions = response.data);
    },
    changeApplicationCriteria(): void {
      this.$props.job.enable_apply = this.$props.job.apply_type === 'service';
    },
  },
  computed: {
    hasBundle(): boolean {
      return this.$props.userPlanBundle !== null;
    },
    canAddLocation(): boolean {
      const locations = this.$store.getters['jobs/locationsCount'];
      return locations < this.$props.plan.max_locations;
    },
    jobTitleCharactersRemaining(): number {
      return this.titleMaxLength - String(this.job.title ?? '').length;
    },
    remoteRange() {
      let result = {};

      for (let i = 100; i > 0; i -= 10) {
        result[i] = `${i}%`;
      }

      return result;
    },
    rates() {
      return Rate;
    },
    seniorities() {
      return Seniority;
    },
    employments() {
      return Employment;
    },
    currenciesValues() {
      return this.currencies.reduce((acc, value) => {
        acc[value.id as unknown as string] = `${value.name} (${value.symbol})`;
        return acc;
      }, {});
    },
    isGross: {
      get() {
        return +this.job.is_gross;
      },
      set(flag) {
        this.job.is_gross = !!flag;
      },
    },
  },
  watch: {
    job: {
      handler(job) {
        store.commit('jobs/INIT_FORM', job);
      },
      deep: true,
    },
  },
};
</script>
