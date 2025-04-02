import axios from 'axios';
import {postJobBoardMilestone} from '../../../feature/jobBoard/jobBoard';

import VueButton from '../../components/forms/button.vue';
import VueCheckbox from '../../components/forms/checkbox.vue';
import VueFormGroup from '../../components/forms/form-group.vue';
import VueSelect from '../../components/forms/select.vue';
import VueText from '../../components/forms/text.vue';
import {notify} from '../../toast';
import {createVueAppNotifications} from '../../vue';

const VAT_RATE = 1.23;

createVueAppNotifications('Payment', '#js-payment', {
  delimiters: ['${', '}'],
  components: {
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-checkbox': VueCheckbox,
    'vue-button': VueButton,
  },
  data: () => ({
    countries: window.countries,
    netPrice: window.netPrice,
    vatRate: window.vatRate,
    vatRates: window.vatRates,
    form: window.form,
    banks: window.banks,
    coupon: {
      code: null,
      amount: 0,
    },
    isCoupon: false,
    errors: {},
    hasPaymentError: false,
    isProcessing: false,
  }),
  mounted() {
    this.stripe = Stripe(window.stripeKey);
    const elements = this.stripe.elements();

    const style = {
      iconStyle: 'solid',
    };

    this.card = elements.create('card', {style});
    this.card.mount('#card-element');
  },
  methods: {
    money(value) {
      return parseFloat(value).toFixed(2);
    },

    calculate() {
      // if VAT ID is empty we must add VAT
      this.vatRate = this.form.invoice.vat_id.trim() !== ''
        ? (this.vatRates[this.form.invoice.country_id] ?? VAT_RATE)
        : VAT_RATE;
    },

    cardPayment({token, success_url}) {
      this.stripe.confirmCardPayment(token, {
        payment_method: {
          card: this.card,
          billing_details: {
            name: this.form.invoice.name,
          },
        },
      }).then(result => {
        if (result.error) {
          notify({type: 'error', text: result.error.message});
          this.isProcessing = false;
          postJobBoardMilestone('payment-attempt-failed-card');
        } else {
          // The payment has been processed!
          if (result.paymentIntent.status === 'succeeded') {
            postJobBoardMilestone('payment-success-card');
            window.location.href = success_url;
          }
        }
      });
    },

    async p24Payment({token, success_url}) {
      const {error} = await this.stripe.confirmP24Payment(
        token,
        {
          payment_method: {
            billing_details: {
              email: this.form.invoice.email,
            },
          },
          return_url: success_url,
        },
      );

      if (error) {
        postJobBoardMilestone('payment-attempt-failed-p24');
        notify({type: 'error', text: error.message});
        this.isProcessing = false;
      }
    },

    makePayment() {
      postJobBoardMilestone('payment-attempt');
      const data = Object.assign(this.form, {price: this.grossPrice});

      this.errors = {};
      this.isProcessing = true;
      this.hasPaymentError = false;

      axios
        .post(window.location.href, data)
        .then(response => {
          if (response.status === 201) {
            window.location.href = response.data;
          }
          if (this.form.payment_method === 'card') {
            this.cardPayment(response.data);
          }
          if (this.form.payment_method === 'p24') {
            this.p24Payment(response.data);
          }
        })
        .catch(err => {
          postJobBoardMilestone('payment-attempt-failed-data');
          this.isProcessing = false;
          if (err.response?.status === 422) {
            this.errors = err.response.data.errors;
          } else {
            this.hasPaymentError = true;
          }
        });
    },

    setPaymentMethod(method) {
      this.form.payment_method = method;
    },
  },
  computed: {
    percentageVatRate() {
      return (this.vatRate * 100) - 100;
    },

    grossPrice() {
      return this.discountNetPrice * this.vatRate;
    },

    discountNetPrice() {
      return Math.max(0, this.netPrice - this.coupon.amount);
    },

    vatPrice() {
      return this.grossPrice - this.discountNetPrice;
    },
  },
  watch: {
    'coupon.code': function (newValue) {
      axios.get('/Praca/Coupon/Validate', {params: {code: newValue}}).then(result => {
        this.coupon.amount = result.data;
        this.form.coupon = newValue;
      });
    },

    'form.invoice.country_id': function () {
      this.calculate();
    },

    'form.invoice.vat_id': function () {
      this.calculate();
    },
  },
});
