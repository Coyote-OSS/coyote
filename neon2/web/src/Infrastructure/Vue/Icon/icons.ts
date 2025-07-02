import "./internal/fontAwesome.css";
import "./internal/fontAwesomeIcons.css";

export type IconName = keyof typeof icons;

const iconCheck = 'fa-light fa-check';
const iconPlus = 'fa-light fa-plus';
const iconClose = 'fa-light fa-close';
const iconSuitcase = 'fa-light fa-suitcase';
const iconRemove = 'fa-light fa-trash';
const iconAsterisk = 'fa-light fa-asterisk';
const iconCalendar = 'fa-light fa-calendar';
const iconArrowLeft = 'fa-light fa-arrow-left';
const iconSearch = 'fa-regular fa-magnifying-glass';

const jobOfferLocation = 'fa-light fa-location-dot';
const jobOfferLegalForm = iconSuitcase;
const jobOfferWorkModeStationary = iconSuitcase;
const jobOfferWorkModeHybrid = 'fa-light fa-globe-wifi';
const jobOfferWorkModeRemote = 'fa-solid fa-wifi';
const jobOfferWorkExperience = 'fa-light fa-chart-line';

const jobBoardIcons = {
  dropdownClosed: 'fa-light fa-chevron-down',
  dropdownOptionSelected: iconCheck,
  dropdownOptionTagRemove: iconClose,
  checkboxChecked: iconCheck,
  add: iconPlus,
  remove: iconRemove,
  toastClose: iconClose,
  buttonLoading: 'fa-solid fa-spinner-third',
  toastInfo: 'fa-light fa-circle-info',
  jobOfferFavourite: 'fa-light fa-star',
  jobOfferFavouriteChecked: 'fa-solid fa-star',
  jobOfferWorkModeRemote,
  jobOfferWorkModeHybrid,
  jobOfferWorkModeStationary,
  jobOfferComments: 'fa-light fa-comment',
  jobOfferLocation,
  jobOfferCompany: 'fa-light fa-building',
  jobOfferCompanyWebsite: 'fa-light fa-globe',
  jobOfferCompanyFundingYear: iconCalendar,
  jobOfferCompanySize: 'fa-light fa-users',
  jobOfferLogoPlaceholder: 'fa-light fa-laptop-code',
  jobOfferSearch: iconSearch,
  jobOfferWorkExperience,
  jobOfferLegalForm,
  jobOfferFilter: 'fa-light fa-filter',
  jobOfferFilterClose: iconClose,
  jobOfferFilterTechnology: 'fa-light fa-code',
  jobOfferFilterLocation: jobOfferLocation,
  jobOfferFilterWorkMode: 'fa-light fa-globe',
  jobOfferFilterSalary: 'fa-light fa-sack-dollar',
  jobOfferFilterCurrency: 'fa-light fa-coins',
  jobOfferFilterSort: 'fa-light fa-bars-sort',
  jobOfferFilterLegalForm: jobOfferLegalForm,
  jobOfferFilterWorkExperience: jobOfferWorkExperience,
  jobOfferUploadCompanyLogo: 'fa-light fa-circle-plus',
  jobOfferStepperStepFinished: iconCheck,
  jobOfferCreatorStepBack: iconArrowLeft,
  jobOfferPricingPlanListBullet: iconCheck,
  jobOfferPricingPlanListBulletSpecialCase: iconAsterisk,
  jobOfferApplyExternally: 'fa-light fa-arrow-up-right-from-square',
  jobOfferTagRemove: iconClose,
  jobOfferExpiredOn: iconCalendar,
  vpBack: iconArrowLeft,
};

const navigationIcons = {
  navigationSectionDiscussion: 'fa-light fa-suitcase',
  navigationSectionDiscussionTechnical: 'fa-light fa-message-code',
  navigationSectionCommunity: 'fa-light fa-users',
  navigationActiveDiscussions: 'fa-light fa-message',
  navigationOnlineUsers: 'fa-light fa-arrow-trend-up',
  navigationCategoryPromoted: 'fa-solid fa-star',
  navigationCategoryTrending: 'fa-light fa-chevron-down',
  navigationAllCategories: 'fa-solid fa-chevron-right',
  navigationSearch: iconSearch,
  navigationGuestAvatar: 'fa-light fa-user',
  navigationProfile: 'fa-light fa-user',
  navigationAccount: 'fa-light fa-gear',
  navigationHelp: 'fa-light fa-comments-question-check',
  navigationNavigate: 'fa-light fa-arrow-right-to-bracket',
  navigationThemeDark: 'fa-light fa-moon',
  navigationThemeLight: 'fa-light fa-sun-bright',
  navigationNoMessages: 'fa-light fa-envelope',
  navigationNotification: 'fa-light fa-bell',
};

const mobileMenuIcons = {
  mobileMenuClose: iconClose,
  mobileMenuOpen: 'fa-regular fa-bars',
};

export const icons = {
  ...jobBoardIcons,
  ...navigationIcons,
  ...mobileMenuIcons,
};

export const htmlIconFormatBold = '<i class="fa-solid fa-bold"/>';
export const htmlIconFormatListUnordered = '<i class="fa-solid fa-list-ul"/>';
export const htmlIconFormatListOrdered = '<i class="fa-solid fa-list-ol"/>';
