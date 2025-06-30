import {inject} from "vue";
import {JobBoardService} from "../JobBoardService";

export const jobBoardServiceInjectKey = Symbol();

export function useJobBoardService(): JobBoardService {
  return inject<JobBoardService>(jobBoardServiceInjectKey)!;
}
