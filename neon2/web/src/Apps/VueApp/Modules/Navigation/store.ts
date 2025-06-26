import {defineStore} from "pinia";
import {IconName} from "../../Icon/icons";

export const useNavigationStore = defineStore('navigation', {
  state(): State {
    return {
      // authentication
      isAuthenticated: false,

      // navigation
      bodyItems: {
        categories: [
          {
            title: 'Dyskusje',
            icon: 'navigationCategoryDiscussion',
            items: [
              {title: 'Item 1', count: '1.2k'},
              {title: 'Slightly longer title', count: '1.2k'},
              {title: 'Item 3', count: '1.2k'},
              {title: 'Item 4', count: '1.2k'},
              {title: 'Item 5', count: '1.2k'},
              {title: 'Item 6', count: '1.2k'},
              {title: 'Item 7', count: '1.2k'},
              {title: 'Item 8', count: '1.2k'},
              {title: 'Item 9', count: '1.2k'},
              {title: 'Item 10', count: '1.2k'},
            ],
          },
          {
            title: 'Społeczność',
            icon: 'navigationCategoryCommunity',
            items: [
              {title: 'Mikroblogi', count: '1.2k', subtitle: 'krótkie wpisy i dyskusje'},
              {title: 'Wydarzenia', subtitle: 'Zainteresowany współpracą?'},
              {title: 'Off-Topic'},
              {title: 'Społeczność', count: '27k'},
              {title: 'Coyote', count: '27k'},
            ],
          },
          {
            title: 'Zasoby',
            icon: 'navigationCategoryResources',
            items: [
              {title: 'Kompendium', count: '66k', subtitle: 'Kursy, artykuły i wiele innych'},
              {title: 'Poradniki', count: '3.5k'},
              {title: 'Narzędzia', count: '27k'},
              {title: 'Algorytmy', count: '27k'},
            ],
          },
        ],
      },
      headerItems: [
        {title: '12.5k aktywnych dyskusji', icon: 'navigationActiveDiscussions'},
        {title: '235 online', icon: 'navigationOnlineUsers'},
      ],
      footerItems: [
        'Najnowsze',
        'Popularne',
        'Bez odpowiedzi',
      ],
    };
  },
});

interface State {
  isAuthenticated: boolean;
  headerItems: HeaderItem[];
  footerItems: string[];
  bodyItems: {
    categories: Category[];
  };
}

interface Category {
  title: string;
  icon: IconName;
  items: CategoryItem[];
}

interface CategoryItem {
  title: string;
  subtitle?: string;
  count?: string;
}

interface HeaderItem {
  title: string;
  icon: IconName;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;
