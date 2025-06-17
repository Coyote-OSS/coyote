import {PaymentMethod} from "../../../Core/Application/PaymentProvider";
import {InvoiceInformation} from "../Domain/Model";

export interface InitiatePayment {
  jobOfferId: number;
  invoiceInfo: InvoiceInformation;
  paymentMethod: PaymentMethod;
}
