import {Currency, Rate} from "../../../../../Domain/JobBoard/JobBoard";

export function formatSalary(salary: SalaryJobOffer): string {
  return [
    salaryRange(salary),
    salary.salaryCurrency,
    salary.salaryIsNet ? 'netto' : 'brutto',
    rateTitle(salary),
  ].join(' ');
}

function rateTitle(salary: SalaryJobOffer): string {
  const titles: Record<Rate, string> = {
    'hourly': '/ h',
    'weekly': '/ tygodniowo',
    'monthly': '',
    'yearly': '/ rocznie',
  };
  return titles[salary.salaryRate];
}

function salaryRange(salary: SalaryJobOffer): string {
  if (salary.salaryRangeFrom === salary.salaryRangeTo) {
    return formatNumber(salary.salaryRangeFrom);
  }
  return `${formatNumber(salary.salaryRangeFrom)} - ${formatNumber(salary.salaryRangeTo)}`;
}

function formatNumber(number: number): string {
  return addThousandSeparator(number, ' ');
}

function addThousandSeparator(number: number, separator: string): string {
  const digits = number.toString();
  if (digits.length > 3) {
    return digits.substring(0, digits.length - 3) + separator + digits.substring(digits.length - 3);
  }
  return digits;
}

export interface SalaryJobOffer {
  salaryRangeFrom: number;
  salaryRangeTo: number;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
}
