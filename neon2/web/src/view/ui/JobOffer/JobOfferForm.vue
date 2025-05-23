<template>
  <JobOfferStepper :four-steps="props.fourSteps" :step="step"/>
  <div class="max-w-170 space-y-4 mx-auto" v-if="step === 'company'">
    <Design.Card space title="O firmie">
      <Design.FieldLabel title="Logo firmy"/>
      <Design.Row>
        <div class="mr-8">
          <Design.ImageUpload v-model="jobOffer.companyLogoUrl" :upload="props.upload.uploadLogo"/>
          <Design.FieldHelp>Format: JPEG, PNG. Maksymalny rozmiar 5MB</Design.FieldHelp>
        </div>
        <div class="flex-grow-1 space-y-6">
          <div class="mb-8">
            <Design.RadioGroup vertical :options="hiringTypeOptions" v-model="jobOffer.companyHiringType"/>
          </div>
          <Design.FieldGroup required label="Nazwa firmy" :error="errors.companyName">
            <Design.TextField placeholder="Wpisz nazwę firmy" v-model="jobOffer.companyName"/>
            <Design.FieldHelp>
              Podając nazwę firmy, oferta staje się bardziej wiarygodna i wartościowa.
            </Design.FieldHelp>
          </Design.FieldGroup>
          <Design.FieldGroup label="Strona WWW" :error="errors.companyWebsiteUrl">
            <Design.TextField placeholder="Podaj stronę www firmy" v-model="jobOffer.companyWebsiteUrl"/>
            <Design.FieldHelp>
              Firmowa strona WWW. Będzie wyświetlana przy ofercie.
            </Design.FieldHelp>
          </Design.FieldGroup>
        </div>
      </Design.Row>
    </Design.Card>
    <Design.Card space title="Lokalizacja firmy">
      <div class="flex">
        <Design.Tile
          nested-pill
          :text="jobOffer.companyAddress?.city || 'Podaj lokalizację...'"
          icon="jobOfferLocation"/>
      </div>
      <Design.FieldGroup label="Adres siedziby firmy" :error="errors.companyAddress">
        <LocationField
          placeholder="np. Warszawa, al. Jerozolimskie 3"
          v-model="jobOffer.companyAddress"/>
      </Design.FieldGroup>
    </Design.Card>
    <Design.Card space title="Dodatkowe informacje">
      <Design.FieldGroup label="Rok powstania firmy" :error="errors.companyFundingYear">
        <Design.TextField placeholder="Podaj rok powstania firmy" v-model="jobOffer.companyFundingYear"/>
      </Design.FieldGroup>
      <Design.FieldGroup label="Liczba pracowników">
        <Design.Dropdown
          default="Podaj liczbę pracowników firmy"
          :options="companySizeOptions"
          v-model="jobOffer.companySizeLevel"/>
      </Design.FieldGroup>
      <Design.FieldGroup label="Opis firmy">
        <Design.TextField
          format-html
          placeholder="Miejsce na szczegółowy opis firmy. Pole nie jest wymagane."
          v-model="jobOffer.companyDescription"/>
      </Design.FieldGroup>
      <Design.FieldGroup label="Nagranie wideo" :error="errors.companyVideoUrl">
        <Design.TextField
          placeholder="Podaj link do filmu o firmie w serwisie YouTube"
          v-model="jobOffer.companyVideoUrl"/>
        <Design.FieldHelp>
          Film promujący firmę będzie wyświetlany pod ogłoszeniem o pracę.
        </Design.FieldHelp>
      </Design.FieldGroup>
      <Design.FieldGroup label="Dodaj zdjęcia firmowe">
        <div class="flex space-x-2">
          <JobOfferPhotoSet v-model="jobOffer.companyPhotoUrls" :upload="props.upload.uploadAsset"/>
        </div>
        <Design.FieldHelp>Format: JPEG, PNG. Maksymalny rozmiar 5MB</Design.FieldHelp>
      </Design.FieldGroup>
    </Design.Card>
  </div>
  <div class="max-w-170 space-y-4 mx-auto" v-if="step === 'jobOffer'">
    <Design.Card space title="Podstawowe informacje">
      <Design.FieldGroup required label="Tytuł ogłoszenia" :error="errors.title">
        <Design.TextField placeholder="np. Senior Java Developer" v-model="jobOffer.title"/>
      </Design.FieldGroup>
      <Design.FieldGroup label="Staż pracy">
        <Design.Dropdown :options="workExperienceOptions" v-model="jobOffer.experience"/>
      </Design.FieldGroup>
    </Design.Card>
    <Design.Card space title="Technologie">
      <Design.FieldGroup label="Wymagane technologie">
        <Design.TextField
          placeholder="Np. java, python, kotlin, c#, etc."
          v-model="jobOffer.tagNames"
          @update:model-value="updateTagNames"/>
        <Design.FieldHelp class="mb-6">
          Podaj tylko technologie programistyczne, np. Python, Kotlin, JavaScript, Laravel, React.
        </Design.FieldHelp>
        <JobOfferTagPrioritiesEditor
          v-model="jobOffer.tags"
          @update:model-value="updateTags"/>
      </Design.FieldGroup>
    </Design.Card>
    <Design.Card space title="Forma zatrudnienia i wynagrodzenie">
      <Design.Row space>
        <Design.FieldGroup label="Rodzaj umowy">
          <Design.Dropdown :options="legalFormOptions" v-model="jobOffer.legalForm"/>
        </Design.FieldGroup>
        <Design.FieldGroup label="Waluta wynagrodzenia">
          <Design.Dropdown :options="salaryCurrencyOptions" v-model="jobOffer.salaryCurrency"/>
        </Design.FieldGroup>
      </Design.Row>
      <Design.Row space>
        <Design.FieldGroup label="Wynagrodzenie od" :error="errors.salaryRangeFrom">
          <Design.TextField placeholder="Dolne widełki" v-model="jobOffer.salaryRangeFrom"/>
        </Design.FieldGroup>
        <Design.FieldGroup label="Wynagrodzenie do" :error="errors.salaryRangeTo">
          <Design.TextField placeholder="Górne widełki" v-model="jobOffer.salaryRangeTo"/>
        </Design.FieldGroup>
      </Design.Row>
      <Design.Row space>
        <Design.FieldGroup label="Częstotliwość wynagrodzenia">
          <Design.Dropdown :options="salaryRateOptions" v-model="jobOffer.salaryRate"/>
        </Design.FieldGroup>
        <Design.FieldGroup label="Netto / Brutto">
          <Design.Dropdown :options="salaryIsNetOptions" v-model="jobOffer.salaryIsNet"/>
        </Design.FieldGroup>
      </Design.Row>
    </Design.Card>
    <Design.Card space title="Tryb pracy">
      <Design.RadioGroup
        :options="workModeOptions"
        :model-value="jobOfferWorkMode"
        @update:model-value="updateWorkMode"/>
      <Design.FieldGroup label="Zakres pracy zdalnej">
        <Design.Dropdown
          :disabled="jobOfferWorkMode !== 'hybrid'"
          :options="workModeRemoteRangeOptions"
          v-model="jobOffer.workModeRemoteRange"/>
      </Design.FieldGroup>
    </Design.Card>
    <Design.Card space title="Lokalizacja">
      <JobOfferLocationSet v-model="jobOffer.locations"/>
    </Design.Card>
    <Design.Card space title="Opis ogłoszenia">
      <Design.FieldGroup label="Szczegółowe informacje">
        <Design.TextField
          format-html
          placeholder="Miejsce na szczegółowy opis oferty"
          test-id="jobOfferDescription"
          v-model="jobOffer.description"/>
      </Design.FieldGroup>
    </Design.Card>
    <Design.Card space title="Sposób aplikacji">
      <Design.FieldLabel title="Jak kandydat powinien aplikować na tę ofertę?"/>
      <div class="mt-6 space-y-8">
        <div>
          <Design.RadioButton
            title="Poprzez 4programmers"
            :selected="jobOffer.applicationMode==='4programmers'"
            @select="jobOffer.applicationMode='4programmers'"/>
          <Design.FieldHelp class="mb-4">
            Zezwól na wysyłanie CV poprzez 4programmers na Twój adres email. Adres e-mail nie
            będzie widoczny dla osób postronnych.
          </Design.FieldHelp>
          <Design.FieldGroup label="E-mail"
                             :error="errors.applicationEmail"
                             :disabled="jobOffer.applicationMode==='external-ats'">
            <Design.TextField placeholder="kontakt@twoja.firma.com" v-model="jobOffer.applicationEmail"/>
          </Design.FieldGroup>
        </div>
        <div>
          <Design.RadioButton
            title="Poprzez Twój system ATS"
            :selected="jobOffer.applicationMode==='external-ats'"
            @select="jobOffer.applicationMode='external-ats'"/>
          <Design.FieldHelp class="mb-4">
            Kandydaci zostaną przekierowani na wskazaną przez Ciebie stronę.
          </Design.FieldHelp>
          <Design.FieldGroup label="Twój adres"
                             :error="errors.applicationExternalAts"
                             :disabled="jobOffer.applicationMode==='4programmers'">
            <Design.TextField
              placeholder="Podaj adres formularza"
              v-model="jobOffer.applicationExternalAts"/>
          </Design.FieldGroup>
        </div>
      </div>
    </Design.Card>
  </div>
  <JobOfferShow
    v-if="step === 'preview'"
    :view-listener="props.viewListener"
    preview
    :can-edit="false"
    @edit="previousStep"
    :job-offer="fromSubmitToJobOfferShow(fromFormModel(jobOffer), props.jobOfferExpiresInDays)"/>
  <div class="sticky bottom-2 mt-5">
    <Design.Tile space shadow>
      <Design.Row vertical-center>
        <span @click="emit('abort')" class="mr-8 cursor-pointer">
          Porzuć formularz
        </span>
        <div class="ml-auto flex items-center">
          <Design.Button outline icon="jobOfferCreatorStepBack" @click="previousStep" v-if="hasPreviousStep">
            Wróć
          </Design.Button>
          <div class="ml-2">
            <Design.Button primary @click="emit('submit', fromFormModel(jobOffer))" v-if="step === 'preview'">
              {{buttonTitle}}
            </Design.Button>
            <Design.Button primary @click="nextStep" v-else>
              Dalej
            </Design.Button>
          </div>
        </div>
      </Design.Row>
    </Design.Tile>
  </div>
</template>

<script setup lang="ts">
import {computed, reactive, ref} from 'vue';
import {
  Currency,
  HiringType,
  LegalForm,
  Rate,
  SubmitJobOffer,
  UploadAssets,
  WorkExperience,
  WorkMode,
} from "../../../main";
import {formatWorkMode, parseWorkMode} from "../../../workMode";
import {Design} from "../design/design";
import {DrawerOption} from "../design/Dropdown.vue";
import LocationField from "../design/LocationField.vue";
import {ViewListener} from "../ui";
import {
  formatCompanySizeLevel,
  formatHiringType,
  formatLegalForm,
  formatWorkExperience,
  formatWorkMode as formatWorkModeString,
} from "./format";
import {FormModel, fromFormModel, toFormModel} from "./JobOfferForm";
import JobOfferLocationSet from './JobOfferLocationSet.vue';
import JobOfferPhotoSet from './JobOfferPhotoSet.vue';
import {fromSubmitToJobOfferShow} from "./JobOfferShow";
import JobOfferShow from "./JobOfferShow.vue";
import JobOfferStepper, {JobOfferCreatorStep} from "./JobOfferStepper.vue";
import JobOfferTagPrioritiesEditor from "./JobOfferTagPrioritiesEditor.vue";
import {ValidationBag} from './ValidationBag';

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  viewListener: ViewListener;
  jobOffer: SubmitJobOffer;
  jobOfferExpiresInDays: number;
  mode: 'create'|'update';
  upload: UploadAssets;
  fourSteps: boolean;
}

interface Emit {
  (event: 'change', jobOffer: SubmitJobOffer): void;
  (event: 'submit', jobOffer: SubmitJobOffer): void;
  (event: 'abort'): void;
}

const step = ref<JobOfferCreatorStep>('company');
const hasPreviousStep = computed<boolean>(() => step.value !== 'company');

const jobOffer: FormModel = reactive<FormModel>(toFormModel(props.jobOffer));

const validation = new ValidationBag(jobOffer, [
  'title', 'companyName', 'applicationEmail', 'applicationExternalAts',
  'salaryRangeFrom', 'salaryRangeTo', 'companyFundingYear',
  'companyWebsiteUrl', 'companyAddress', 'companyVideoUrl',
]);

const errors = reactive(validation.emptyErrors());

function nextStep(): void {
  if (step.value === 'preview') {
    throw new Error('Unexpected step.');
  }
  const [success, failureErrors] = validate();
  Object.assign(errors, failureErrors);
  if (success) {
    changeStep(step.value === 'company' ? 'jobOffer' : 'preview');
  } else {
    if (failureErrors.companyName) {
      window.scrollTo(0, 0);
    } else if (failureErrors.title) {
      window.scrollTo(0, 0);
    } else if (failureErrors.salaryRangeFrom || failureErrors.salaryRangeTo) {
      window.scrollTo(0, 600);
    } else if (failureErrors.applicationEmail || failureErrors.applicationExternalAts) {
      window.scrollTo(0, 1000);
    }
  }
}

function validate() {
  if (step.value === 'company') {
    return validation.validate(rules => {
      rules.nonEmpty('companyName', 'Podaj nazwę firmy.');
      rules.maxLength('companyName', 60, 'Nazwa firmy jest zbyt długa.');
      rules.optionalNumeric('companyFundingYear', 'Podaj poprawny rok założenia firmy.');
      rules.optionalJsUrl('companyWebsiteUrl', 'Podaj poprawny URL witryny firmy.');
      rules.optionalJsUrlHostname('companyVideoUrl',
        ['www.youtube.com', 'youtube.com', 'youtu.be'],
        'Podaj poprawny URL wideo firmy.');
      rules.optionalLocation('companyAddress', 'Podaj dokładniejszy adres.');
    });
  }
  return validation.validate(rules => {
    rules.nonEmpty('title', 'Podaj tytuł ogłoszenia.');
    rules.maxLength('title', 60, 'Tytuł ogłoszenia jest zbyt długi.');
    rules.optionalNumeric('salaryRangeFrom', 'Podaj poprawne wynagrodzenie.');
    rules.optionalNumeric('salaryRangeTo', 'Podaj poprawne wynagrodzenie.');
    if (jobOffer.applicationMode === '4programmers') {
      rules.nonEmpty('applicationEmail', 'Podaj adres e-mail do otrzymania aplikacji.');
    }
    if (jobOffer.applicationMode === 'external-ats') {
      rules.nonEmpty('applicationExternalAts', 'Podaj adres Twojego systemu ATS.');
    }
  });
}

function previousStep(): void {
  if (step.value === 'preview') {
    changeStep('jobOffer');
  } else if (step.value === 'jobOffer') {
    changeStep('company');
  }
}

function changeStep(newStep: JobOfferCreatorStep): void {
  step.value = newStep;
  window.scrollTo(0, 0);
}

const buttonTitle = computed<string>(() => {
  if (props.mode === 'update') {
    return 'Zapisz';
  }
  if (props.fourSteps) {
    return 'Dodaj ogłoszenie';
  }
  return 'Publikuj';
});

const workExperienceOptions: DrawerOption<WorkExperience>[] = [
  {value: 'intern', title: formatWorkExperience('intern')},
  {value: 'junior', title: formatWorkExperience('junior')},
  {value: 'mid-level', title: formatWorkExperience('mid-level')},
  {value: 'senior', title: formatWorkExperience('senior')},
  {value: 'lead', title: formatWorkExperience('lead')},
  {value: 'manager', title: formatWorkExperience('manager')},
  {value: 'not-provided', title: formatWorkExperience('not-provided')},
];
const legalFormOptions: DrawerOption<LegalForm>[] = [
  {value: 'employment', title: formatLegalForm('employment')},
  {value: 'of-mandate', title: formatLegalForm('of-mandate')},
  {value: 'specific-task', title: formatLegalForm('specific-task')},
  {value: 'b2b', title: formatLegalForm('b2b')},
];
const workModeOptions: DrawerOption<WorkMode>[] = [
  {value: 'stationary', title: formatWorkModeString('stationary')},
  {value: 'fullyRemote', title: formatWorkModeString('fullyRemote') + ' (100%)'},
  {value: 'hybrid', title: formatWorkModeString('hybrid')},
];
const workModeRemoteRangeOptions: DrawerOption<number>[] = [
  {value: 0, title: '0% pracy zdalnej - Praca stacjonarna'},
  {value: 10, title: '10% pracy zdalnej'},
  {value: 20, title: '20% pracy zdalnej'},
  {value: 30, title: '30% pracy zdalnej'},
  {value: 40, title: '40% pracy zdalnej'},
  {value: 50, title: '50% pracy zdalnej'},
  {value: 60, title: '60% pracy zdalnej'},
  {value: 70, title: '70% pracy zdalnej'},
  {value: 80, title: '80% pracy zdalnej'},
  {value: 90, title: '90% pracy zdalnej'},
  {value: 100, title: '100% pracy zdalnej'},
];
const hiringTypeOptions: DrawerOption<HiringType>[] = [
  {value: 'direct', title: formatHiringType('direct')},
  {value: 'agency', title: formatHiringType('agency')},
];
const salaryCurrencyOptions: DrawerOption<Currency>[] = [
  {value: 'PLN', title: 'PLN'},
  {value: 'EUR', title: 'EUR'},
  {value: 'USD', title: 'USD'},
  {value: 'GBP', title: 'GBP'},
  {value: 'CHF', title: 'CHF'},
];
const salaryRateOptions: DrawerOption<Rate>[] = [
  {value: 'hourly', title: 'godzinowo'},
  {value: 'weekly', title: 'tygodniowo'},
  {value: 'monthly', title: 'miesięcznie'},
  {value: 'yearly', title: 'rocznie'},
];
const salaryIsNetOptions: DrawerOption<boolean>[] = [
  {value: true, title: 'netto'},
  {value: false, title: 'brutto'},
];
const companySizeOptions: DrawerOption<number|null>[] = [
  {value: null, title: formatCompanySizeLevel(null)},
  {value: 1, title: formatCompanySizeLevel(1)},
  {value: 2, title: formatCompanySizeLevel(2)},
  {value: 3, title: formatCompanySizeLevel(3)},
  {value: 4, title: formatCompanySizeLevel(4)},
  {value: 5, title: formatCompanySizeLevel(5)},
  {value: 6, title: formatCompanySizeLevel(6)},
  {value: 7, title: formatCompanySizeLevel(7)},
  {value: 8, title: formatCompanySizeLevel(8)},
  {value: 9, title: formatCompanySizeLevel(9)},
  {value: 10, title: formatCompanySizeLevel(10)},
  {value: 11, title: formatCompanySizeLevel(11)},
];

const jobOfferWorkMode = computed((): WorkMode => parseWorkMode(jobOffer.workModeRemoteRange));

function updateWorkMode(workMode: WorkMode): void {
  jobOffer.workModeRemoteRange = formatWorkMode(workMode);
}

function updateTags(): void {
  jobOffer.tagNames = jobOffer.tags.map(tag => tag.tagName).join(', ');
  console.log(jobOffer.tagNames);
}

function updateTagNames(): void {
  jobOffer.tags = jobOffer.tagNames
    .split(',')
    .filter(tagName => tagName.trim().length > 0)
    .map(tagName => {
      return {
        tagName,
        priority: 2,
      };
    });
}
</script>
