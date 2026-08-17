import {ForumJobOfferTile} from "./ViewModel/ForumJobOfferTile";

export function rotateJobOffers(
  offers: ForumJobOfferTile[],
  count: number,
  dateSeed: Date,
): ForumJobOfferTile[] {
  if (offers.length <= count) {
    return offers;
  }
  const offset = dateOffset(dateSeed, offers);
  return mapRange(count, (i: number) => offers[(offset + i) % offers.length]);
}

function dateOffset(dateSeed: Date, items: unknown[]): number {
  const millisecondsInHour = 60 * 60 * 1000;
  return Math.floor(dateSeed.getTime() / millisecondsInHour) % items.length;
}

function mapRange<T>(length: number, mapper: (index: number) => T): T[] {
  return Array.from({length}, (_, i) => mapper(i));
}
