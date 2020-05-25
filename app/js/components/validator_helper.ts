export class ValidatorHelper {

  public validateEmpty(value: string): boolean {
    return value === '';
  }

  public validateUrl(value: string): boolean {
    const ipValidator = require('ip-validator');
    try {
      // The url should be valid according to the JS parser
      const url: URL = new URL(value);
      // Url's can not be IP addresses, except for loopback addresses, they are verified in 'validateLoopback'
      const isIp: boolean = ipValidator.ipv4(url.host) || ipValidator.ipv6(url.host);
      // Localhost is not allowed in this validator. The validateLoopback will verify this as valid
      const isLocalhost: boolean = url.host === 'localhost';
      // Only allow http(s) protocol
      const isInvalidProtocol: boolean = !['http:', 'https:'].includes(url.protocol);
      return !(isIp || isInvalidProtocol || isLocalhost);
    } catch (e) {
      return false;
    }
  }

  public validateUrn(value: string): boolean {
    const regExp = /^urn:[a-z0-9][a-z0-9-]{0,31}:[a-z0-9()+,\-.:=@;$_!*'%\/?#]+$/i;

    return regExp.test(value);
  }

  public validateLoopback(value: string): boolean {
    const allowedHostnames: string[] = [
      'localhost',
      '127.0.0.1',
      '[::]',
      '[::1]',
      '[0:0:0:0:0:0:0:1]',
    ];

    try {
      // The url should be valid according to the JS parser
      const url: URL = new URL(value);
      // Protocol should be http(s) and the hostname should be a loopback address
      return allowedHostnames.includes(url.hostname) && ['http:', 'https:'].includes(url.protocol);
    } catch (e) {
      return false;
    }
  }
}
