// tslint:disable-next-line:no-var-requires
export class ValidatorHelper {

  public validateEmpty(value: string): boolean {
    return value === '';
  }

  /**
   * Validate the input string to be a valid url
   *
   * The following specific constrains apply:
   * - The url should be valid according to the JS parser (which doesn't take much)
   * - Localhost is not allowed in this validator. The validateLoopback will verify this as valid
   * - Url's can not be IP addresses
   * - Reverse redirect urls are allowed (swapped protocol & hostname)
   */
  public validateUrl(value: string): boolean {
    let rawUrlValue = value;
    try {
      const isReverseRedirectUrl = this.isReverseUrl(rawUrlValue);
      let url: URL;
      if (isReverseRedirectUrl) {
        rawUrlValue = this.flipProtocol(rawUrlValue);
      }
      url = new URL(rawUrlValue);
      const isIp = this.isIp(url.host);
      const isLocalhost = url.host === 'localhost';

      return !(isIp || this.invalidProtocol(rawUrlValue, isReverseRedirectUrl) || this.missingTld(url) || isLocalhost);
    } catch (e) {
      return false;
    }
  }

  /**
   * Validate the input string to be a valid urn
   */
  public validateUrn(value: string): boolean {
    const regExp = /^urn:[a-z0-9][a-z0-9-]{0,31}:[a-z0-9()+,\-.:=@;$_!*'%\/?#]+$/i;

    return regExp.test(value);
  }

  /**
   * Validate the input string to be a valid loopback url
   */
  public validateLoopback(value: string): boolean {
    const allowedHostnames = [
      'localhost',
      '127.0.0.1',
      '[::]',
      '[::1]',
      '[0:0:0:0:0:0:0:1]',
    ];

    try {
      const url = new URL(value);
      return allowedHostnames.includes(url.hostname) && !this.invalidProtocol(value);
    } catch (e) {
      return false;
    }
  }

  /**
   * This helper method is designed to turn a reverse redirect url into a validatable regular url
   */
  public flipProtocol(value: string): string {
    // The hostname is provided in reverse as per https://tools.ietf.org/html/rfc8252#section-7.1
    const endScheme = value.indexOf(':/');
    const scheme = value.substring(0, endScheme);
    let protocol = value.indexOf('https') > -1 ? 'https' : '';
    // if not https specified, see if http is specified
    protocol = (value.indexOf('http') > -1 && !protocol) ? 'http' : 'https';
    const port = this.getPort(value, protocol);
    const protocolAndPort = port ? `${protocol}:${port}` : `${protocol}`;
    const newSchemeAndPort = port ? `${this.reverseScheme(scheme)}:${port}` : this.reverseScheme(scheme);
    const destination = value.substring(endScheme)
                              // remove : before protocol delimiter
                             .replace(':', '')
                              // remove protocol & port
                             .replace(protocolAndPort, '')
                              // remove /// if user used ://
                             .replace('///', '/')
                              // remove // if user used :/
                             .replace('//', '/');
    return `${protocol}://${newSchemeAndPort}${destination}`;
  }

  private invalidProtocol(input: string, isReverseUrl: boolean = false): boolean {
    // Reverse Urls are allowed to have custom protocol, Rules regarding custom protocols are validated in PHP.
    if (isReverseUrl) {
      return false;
    }
    const regex = /^https?:\/{2}/i;
    return !input.match(regex);
  }

  private missingTld(url: URL): boolean {
    return !url.host.includes('.');
  }

  private isIp(host: string): boolean {
    const regexv4 = /(([0-1]?[0-9]{1,2}\.)|(2[0-4][0-9]\.)|(25[0-5]\.)){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))/;
    const regexv6 = /(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/;

    return (regexv4.test(host) || regexv6.test(host));
  }

  private isReverseUrl(url: string): boolean {
    return this.invalidProtocol(url);
  }

  private reverseScheme(str: string): string {
    return str.split('.').reverse().join('.');
  }

  private getPort(url: string, protocol: string): string {
    const protocolStart = url.indexOf(protocol);
    const portStart = protocolStart + protocol.length;
    const hasPort = url[portStart] === ':';
    const portEnd = url.indexOf('/', portStart);
    if (hasPort) return url.substring(portStart + 1, portEnd);

    return '';
  }
}
