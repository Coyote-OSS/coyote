import {ViewListener} from "../../../../../view/ui/ui";
import {BoardStore} from "./store";

export class JobBoardService {
  constructor(
    private store: BoardStore,
    private viewListener: ViewListener,
  ) {}

  redeemBundle(jobOfferId: number): void {
    this.viewListener.redeemBundle(jobOfferId);
  }
}
