<template>
  <JobOfferStepper four-steps step="publish"/>
  <div class="flex gap-4">
    <div class="flex-grow-1 basis-2/3 space-y-4">
      <Design.Card title="Płatność poprzez bezpieczne połączenie">
        <Design.PaymentMethod :options="paymentMethods" v-model="method"/>
        <Design.FieldGroup required label="Numer karty" v-show="method === 'card'">
          <div class="border border-neutral2-300 px-2 py-3 rounded-lg">
            <div id="creditCardInput">
              Wczytywanie...
            </div>
          </div>
        </Design.FieldGroup>
        <Design.Toast :subtitle="paymentP24Information" v-if="method === 'p24'"/>
      </Design.Card>
      <Design.Card title="Dane do faktury">
        <Design.FieldGroup required label="Nazwa firmy" :error="errors.companyName">
          <Design.TextField
            placeholder="Nazwa firmy na którą ma być wystawiona faktura"
            v-model="invoiceInformation.companyName"/>
        </Design.FieldGroup>
        <Design.Row space>
          <Design.FieldGroup required label="Kraj" :error="errors.countryCode">
            <Design.DropdownSingle
              scrollable
              :options="countryCodeOptions"
              v-model="invoiceInformation.countryCode"
              @update:model-value="vatDetailsChanged"/>
          </Design.FieldGroup>
          <Design.FieldGroup label="NIP / VAT - ID" :error="errorVatId">
            <Design.TextField
              placeholder="Np. 1234123412"
              v-model="invoiceInformation.vatId"
              @update:model-value="vatDetailsChanged"/>
          </Design.FieldGroup>
        </Design.Row>
        <Design.FieldGroup required label="Adres" :error="errors.companyAddress">
          <Design.TextField placeholder="Np. al. Jerozolimskie 3" v-model="invoiceInformation.companyAddress"/>
        </Design.FieldGroup>
        <Design.Row space>
          <Design.FieldGroup required label="Kod pocztowy" :error="errors.companyPostalCode">
            <Design.TextField placeholder="Np. 12-123" v-model="invoiceInformation.companyPostalCode"/>
          </Design.FieldGroup>
          <Design.FieldGroup required label="Miasto" :error="errors.companyCity">
            <Design.TextField placeholder="Np. Warszawa, Kraków, etc." v-model="invoiceInformation.companyCity"/>
          </Design.FieldGroup>
        </Design.Row>
      </Design.Card>
    </div>
    <div class="flex-grow-1 basis-1/3">
      <div class="rounded-xl p-4 border border-tile-border space-y-4">
        <div class="text-lg font-semibold">
          Twoja płatność obejmuje
        </div>
        <hr class="text-tile-border"/>
        <div class="flex">
          <span data-testid="paymentPricingPlan" v-text="formatPricingPlan(props.summary.bundleSize)"/>
          <span class="ml-auto" data-testid="paymentPriceBase" v-text="formatMoney(props.summary.basePrice)"/>
        </div>
        <div class="flex">
          <span>VAT (23%)</span>
          <span class="ml-auto" data-testid="paymentPriceVat" v-text="formatMoney(effectiveVat)"/>
        </div>
        <hr class="text-tile-border"/>
        <div class="flex items-center">
          <span class="text-lg font-semibold">Suma:</span>
          <span
            class="ml-auto accent p-2"
            data-testid="paymentPriceTotal"
            v-text="formatMoney(props.summary.basePrice + effectiveVat)"/>
        </div>
      </div>
    </div>
  </div>
  <Design.Tile space>
    <Design.Row>
      <Design.RowEnd>
        <Design.LoadingButton
          :loading="props.paymentProcessing"
          title="Zapłać i Publikuj"
          @click="pay"/>
      </Design.RowEnd>
    </Design.Row>
  </Design.Tile>
</template>

<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, reactive, ref} from "vue";
import {VatIdState} from "../../../../../Application/JobBoard/Port/PaymentListener";
import {PaymentMethod} from "../../../../../Application/JobBoard/Port/PaymentProvider";
import {Country, InvoiceInformation, PaymentSummary} from "../../../../../Domain/JobBoard/JobBoard";
import {Design} from "../../../DesignSystem/design";
import {DrawerOption} from "../../../DesignSystem/DropdownSingle.vue";
import {useJobBoardService} from "../vue";
import JobOfferStepper from './JobOfferStepper.vue';
import {ValidationBag} from "./ValidationBag";

const props = defineProps<Props>();
const service = useJobBoardService();

interface Props {
  jobOfferId: number;
  summary: PaymentSummary;
  countries: Country[];
  vatIdState: VatIdState;
  paymentProcessing: boolean;
}

function pay(): void {
  const [success, failureErrors] = validate();
  Object.assign(errors, failureErrors);
  if (success) {
    service.payForJob({
      jobOfferId: props.jobOfferId,
      invoiceInfo: {
        companyName: invoiceInformation.companyName.trim(),
        countryCode: invoiceInformation.countryCode,
        vatId: invoiceInformation.vatId.replaceAll(' ', ''),
        companyAddress: invoiceInformation.companyAddress.trim(),
        companyPostalCode: invoiceInformation.companyPostalCode.trim(),
        companyCity: invoiceInformation.companyCity.trim(),
      },
      paymentMethod: method.value,
    });
  }
}

onMounted(() => service.managePaymentMethod('mount', '#creditCardInput'));
onBeforeUnmount(() => service.managePaymentMethod('unmount'));

const method = ref<PaymentMethod>('card');

const invoiceInformation: InvoiceInformation = reactive<InvoiceInformation>({
  companyName: '',
  countryCode: 'not-provided',
  vatId: '',
  companyAddress: '',
  companyPostalCode: '',
  companyCity: '',
});

const errors = reactive<Record<string, string|null>>({
  companyName: null as string|null,
  countryCode: null as string|null,
  companyAddress: null as string|null,
  companyPostalCode: null as string|null,
  companyCity: null as string|null,
});

const errorVatId = computed(() => {
  if (props.vatIdState === 'invalid') {
    return 'NIP / VAT - ID jest niepoprawny.';
  }
  return null;
});

const validation = new ValidationBag(invoiceInformation, Object.keys(errors));

function validate() {
  return validation.validate(v => {
    v.nonEmpty('companyName', 'Podaj nazwę firmy.');
    v.notEqual('countryCode', 'not-provided', 'Wybierz kraj.');
    v.nonEmpty('companyAddress', 'Podaj adres firmy.');
    v.nonEmpty('companyPostalCode', 'Podaj kod pocztowy firmy.');
    v.nonEmpty('companyCity', 'Podaj miasto firmy.');
  });
}

const paymentMethods: DrawerOption<PaymentMethod>[] = [
  {value: 'card', title: 'Karta kredytowa / debetowa'},
  {value: 'p24', title: 'BLIK lub przelew'},
];

const paymentP24Information = 'Po kliknięciu "Zapłać i Publikuj", zostaniesz przekierowany do formularza płatności online.';

function formatMoney(amount: number): string {
  if (amount === 0) {
    return '0 zł';
  }
  return insertedFromEnd(amount.toString(), 2, '.') + ' zł';
}

function insertedFromEnd(subject: string, index: number, infix: string): string {
  return subject.slice(0, -index) + infix + subject.slice(-index);
}

function formatPricingPlan(bundleSize: 1|3|5|20): string {
  const plans: Record<1|3|5|20, string> = {
    1: 'Ogłoszenie (30 dni)',
    3: 'Pakiet 3 ogłoszeń (30 dni)',
    5: 'Pakiet 5 ogłoszeń (30 dni)',
    20: 'Pakiet 20 ogłoszeń (30 dni)',
  };
  return plans[bundleSize];
}

const countryCodeOptions = computed<DrawerOption<string|null>[]>(() => [
  {value: 'not-provided', title: 'Wybierz kraj...'},
  ...props.countries.map(country => ({
    value: country.countryCode,
    title: `${country.countryName} (${country.countryCode})`,
  })),
]);

const effectiveVat = computed(() => {
  if (props.summary.vatIncluded) {
    return props.summary.vat;
  }
  return 0;
});

function vatDetailsChanged(): void {
  service.vatDetailsChanged(
    invoiceInformation.countryCode,
    invoiceInformation.vatId.trim());
}
</script>
