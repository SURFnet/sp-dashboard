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
   */
  public validateUrl(value: string): boolean {
    try {
      const url = new URL(value);
      const isIp = this.isIp(url.host);
      const isLocalhost = url.host === 'localhost';
      // console.log(value, isIp, this.invalidProtocol(value), this.missingTld(url), isLocalhost);
      return !(isIp || this.invalidProtocol(value) || this.missingTld(url) || isLocalhost);
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

  private invalidProtocol(input: string): boolean {
    const regex = /^https?:\/{2}/i;
    if (!input.match(regex)) {
      return true;
    }
    return false;
  }

  private missingTld(url: URL): boolean {
    if (url.host.includes('.')) {
      return false;
    }
    return true;
  }

  private isIp(host: string): boolean {
    const regexv4 = /(([0-1]?[0-9]{1,2}\.)|(2[0-4][0-9]\.)|(25[0-5]\.)){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))/;
    const regexv6 = /(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/;

    return (regexv4.test(host) || regexv6.test(host));
  }
}
