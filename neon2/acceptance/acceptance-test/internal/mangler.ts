export class Mangler {
  private hashedNames: Record<string, string> = {};

  encoded(name: string): string {
    if (this.hasHash(name)) {
      return this.lookupHash(name);
    }
    const hash = this.hashedName(name);
    this.hashedNames[hash] = name;
    return hash;
  }

  private hashedName(name: string): string {
    return '(' + this.randomString() + ') ' + name;
  }

  private hasHash(name: string): boolean {
    return Object.values(this.hashedNames).indexOf(name) > -1;
  }

  private lookupHash(name: string): string {
    for (const [hash, hashName] of Object.entries(this.hashedNames)) {
      if (hashName === name) {
        return hash;
      }
    }
  }

  decoded(hash: string): string {
    if (this.hashedNames.hasOwnProperty(hash)) {
      return this.hashedNames[hash];
    }
    return hash;
  }

  decodedAll(hashes: string[]): string[] {
    return hashes.map(hash => this.decoded(hash));
  }

  private randomString(): string {
    return Math.random().toString().substring(2);
  }

  reset(): void {
    this.hashedNames = {};
  }
}
