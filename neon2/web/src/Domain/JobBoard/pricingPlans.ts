import {PricingPlan} from "./JobBoard";

export interface PlanCard {
  name: PricingPlan;
  title: string;
  price: number;
  expiresIn: number;
  bundleSize: number;
  bundlePrice?: number;
  bundleDiscount?: string;
  free?: boolean;
  color: PricingCardColor;
}

export type PricingCardColor = 'yellow'|'blue'|'violet'|'green'|'phantom';

export const offerPlans: PlanCard[] = [
  {name: 'free', title: 'Free*', price: 0, expiresIn: 14, bundleSize: 1, free: true, color: 'yellow'},
  {name: 'premium', title: 'Premium', price: 159, expiresIn: 30, bundleSize: 1, color: 'blue'},
];

export const bundlePlans: PlanCard[] = [
  {name: 'premium', title: 'Premium', price: 159, expiresIn: 30, bundleSize: 1, color: 'phantom'},
  {
    name: 'strategic',
    title: 'Strategic',
    price: 119,
    expiresIn: 30,
    bundleSize: 3,
    bundlePrice: 3 * 119,
    bundleDiscount: '25%',
    color: 'blue',
  },
  {
    name: 'growth',
    title: 'Growth',
    price: 99,
    expiresIn: 30,
    bundleSize: 5,
    bundlePrice: 5 * 99,
    bundleDiscount: '38%',
    color: 'violet',
  },
  {
    name: 'scale',
    title: 'Scale',
    price: 79,
    expiresIn: 30,
    bundleSize: 20,
    bundlePrice: 20 * 79,
    bundleDiscount: '50%',
    color: 'green',
  },
];
