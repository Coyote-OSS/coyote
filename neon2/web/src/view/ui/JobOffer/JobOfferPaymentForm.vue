<template>
  <Design.Card title="Płatność poprzez bezpieczne połączenie">
    <div id="creditCardInput"/>
  </Design.Card>
  <Design.Card title="Dane do faktury">
    <Design.FieldGroup label="Nazwa firmy">
      <Design.TextField placeholder="Nazwa firmy na którą ma być wystawiona faktura" v-model="invoiceInformation.companyName"/>
    </Design.FieldGroup>
    <Design.Row columns>
      <Design.FieldGroup label="Kraj">
        <Design.TextField placeholder="Np. Polska, etc." v-model="invoiceInformation.countryCode"/>
      </Design.FieldGroup>
      <Design.FieldGroup label="NIP / VAT - ID">
        <Design.TextField placeholder="Np. 1234123412" v-model="invoiceInformation.vatId"/>
      </Design.FieldGroup>
    </Design.Row>
    <Design.FieldGroup label="Adres">
      <Design.TextField placeholder="Np. al. Jerozolimskie 3" v-model="invoiceInformation.companyAddress"/>
    </Design.FieldGroup>
    <Design.Row columns>
      <Design.FieldGroup label="Kod pocztowy">
        <Design.TextField placeholder="Np. 12-123" v-model="invoiceInformation.companyPostalCode"/>
      </Design.FieldGroup>
      <Design.FieldGroup label="Miasto">
        <Design.TextField placeholder="Np. Warszawa, Kraków, etc." v-model="invoiceInformation.companyCity"/>
      </Design.FieldGroup>
    </Design.Row>
  </Design.Card>
  <Design.Tile>
    <Design.Row>
      <Design.RowEnd>
        <Design.Button primary @click="pay">
          Zapłać i Publikuj
        </Design.Button>
      </Design.RowEnd>
    </Design.Row>
  </Design.Tile>
</template>

<script setup lang="ts">
import {onBeforeUnmount, onMounted, reactive} from "vue";
import {InvoiceInformation} from "../../../main";
import {Design} from "../design/design";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  jobOfferId: number;
}

interface Emit {
  (event: 'pay', jobOfferId: number, invoiceInfo: InvoiceInformation): void;
  (event: 'mountCardInput', cssSelector: string): void;
  (event: 'unmountCardInput'): void;
}

function pay(): void {
  emit('pay', props.jobOfferId, {...invoiceInformation});
}

onMounted(() => emit('mountCardInput', '#creditCardInput'));
onBeforeUnmount(() => emit('unmountCardInput'));

const invoiceInformation: InvoiceInformation = reactive<InvoiceInformation>({
  companyName: '',
  countryCode: '',
  vatId: '',
  companyAddress: '',
  companyPostalCode: '',
  companyCity: '',
});
</script>
