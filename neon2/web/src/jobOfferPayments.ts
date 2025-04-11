export class JobOfferPayments {
  private payments: JobOfferPayment[] = [];

  addJobOffer(payment: JobOfferPayment): void {
    this.payments.push(payment);
  }

  jobOfferId(paymentId: string): number {
    return this.payments.find(payment => payment.paymentId === paymentId)!.jobOfferId;
  }

  paymentId(jobOfferId: number): string {
    return this.payments.find(payment => payment.jobOfferId === jobOfferId)!.paymentId;
  }
}

interface JobOfferPayment {
  jobOfferId: number;
  paymentId: string;
}
