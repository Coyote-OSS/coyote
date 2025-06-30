import {WorkMode} from "./JobBoard";

export function parseWorkMode(workModeRemoteRange: number): WorkMode {
  if (workModeRemoteRange === 0) {
    return 'stationary';
  }
  if (workModeRemoteRange === 100) {
    return 'fullyRemote';
  }
  return 'hybrid';
}

export function formatWorkMode(workMode: WorkMode): number {
  if (workMode === 'stationary') {
    return 0;
  }
  if (workMode === 'fullyRemote') {
    return 100;
  }
  return 50;
}
