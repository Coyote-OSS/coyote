import {HiringType, LegalForm, WorkExperience, WorkMode} from "../../../../../Domain/JobBoard/JobBoard";

export function formatExpiresInDays(expiresInDays: number): string {
  return 'za ' + expiresInDays.toString() + ' dni';
}

export function formatLegalForm(legalForm: LegalForm): string {
  return format(legalForm, {
    'b2b': 'B2B',
    'employment': 'Umowa o pracę',
    'of-mandate': 'Umowa zlecenie',
    'specific-task': 'Umowa o dzieło',
  });
}

export function formatWorkMode(workMode: WorkMode): string {
  return format(workMode, {
    'stationary': 'Praca stacjonarna',
    'fullyRemote': 'Praca zdalna',
    'hybrid': 'Praca hybrydowa',
  });
}

export function formatWorkExperience(experience: WorkExperience): string {
  return format(experience, {
    'intern': 'Stażysta',
    'junior': 'Junior',
    'mid-level': 'Mid/Regular',
    'senior': 'Senior',
    'lead': 'Lead',
    'manager': 'Manager',
    'not-provided': 'Wybierz...',
  });
}

export function formatHiringType(type: HiringType): string {
  return format(type, {
    'direct': 'Bezpośredni pracodawca',
    'agency': 'Agencja pośrednictwa',
  });
}

export function formatCompanySizeLevel(level: number|null): string {
  if (level === null) {
    return 'Wybierz...';
  }
  return format<number>(level, {
    1: '1-5 pracowników',
    2: '6-10 pracowników',
    3: '11-20 pracowników',
    4: '21-30 pracowników',
    5: '31-50 pracowników',
    6: '51-100 pracowników',
    7: '101-200 pracowników',
    8: '201-500 pracowników',
    9: '501-1000 pracowników',
    10: '1001-5000 pracowników',
    11: '5000+ pracowników',
  });
}

function format<T extends string|number>(value: T, titles: Record<T, string>): string {
  return titles[value];
}
