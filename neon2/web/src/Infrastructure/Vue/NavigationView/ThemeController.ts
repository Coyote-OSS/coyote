import {CoyoteApi} from "../../Backend/CoyoteApi";
import {NavigationView} from "./NavigationView";

export class ThemeController {
  constructor(
    private view: NavigationView,
    private coyoteApi: CoyoteApi,
  ) {}

  toggleTheme(): void {
    this.setDarkTheme(!this.view.isDarkTheme());
  }

  setDarkTheme(darkTheme: boolean): void {
    this.view.setDarkTheme(darkTheme);
    this.coyoteApi.toggleTheme(darkTheme);
  }
}
