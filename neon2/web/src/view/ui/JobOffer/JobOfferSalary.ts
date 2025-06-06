import {Currency, Rate} from "../../../main";

function mapSalaryToUniform(salary: SalaryJobOffer): string {
  if (salary.salaryRate === 'monthly') {
    return salaryRange(salary.salaryRangeFrom, salary.salaryRangeTo);
  }
  if (salary.salaryRate === 'hourly') {
    return salaryRange(round(salary.salaryRangeFrom * 160), round(salary.salaryRangeTo * 160));
  }
  if (salary.salaryRate === 'yearly') {
    return salaryRange(round(salary.salaryRangeFrom / 12), round(salary.salaryRangeTo / 12));
  }
  if (salary.salaryRate === 'weekly') {
    return salaryRange(round(salary.salaryRangeFrom * 4.33), round(salary.salaryRangeTo * 4.33));
  }
  throw new Error('Failed to map salary.');
}

export function formatSalary(salary: SalaryJobOffer, mapToMonths: boolean): string {
  if (mapToMonths) {
    return [
      mapSalaryToUniform(salary),
      salary.salaryCurrency,
      salary.salaryIsNet ? 'netto' : 'brutto',
      rateTitle('monthly'),
    ].join(' ');
  }
  return [
    salaryRange(salary.salaryRangeFrom, salary.salaryRangeTo),
    salary.salaryCurrency,
    salary.salaryIsNet ? 'netto' : 'brutto',
    rateTitle(salary.salaryRate),
  ].join(' ');
}

function rateTitle(rate: Rate): string {
  const titles: Record<Rate, string> = {
    'hourly': '/ h',
    'weekly': '/ tygodniowo',
    'monthly': '',
    'yearly': '/ rocznie',
  };
  return titles[rate];
}

function salaryRange(from: number, to: number): string {
  if (from === to) {
    return formatNumber(from);
  }
  return `${formatNumber(from)} - ${formatNumber(to)}`;
}

function formatNumber(number: number): string {
  return addThousandSeparator(number, ' ');
}

function round(number: number): number {
  return Math.round(number / 250) * 250;
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
