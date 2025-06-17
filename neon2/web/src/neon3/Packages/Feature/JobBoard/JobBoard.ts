import {BackendPaymentIntent} from "../../Core/Backend/backendInput";

export interface JobOfferPaymentIntent {
  jobOfferId: number;
  paymentIntent: BackendPaymentIntent;
}
