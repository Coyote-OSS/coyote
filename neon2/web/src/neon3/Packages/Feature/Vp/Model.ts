export interface Event {
  eventName: string;
  metadata: EventMetadata;
}

export type EventMetadata = Record<string, string|number|boolean|undefined>;
