import { ValidatorHelper } from './validator_helper';

describe('validation functions', () => {

  it('can validate empty strings', () => {
    const helper = new ValidatorHelper();
    expect(helper.validateEmpty('')).toBeTruthy();
    expect(helper.validateEmpty(' ')).toBeFalsy();
    expect(helper.validateEmpty('df')).toBeFalsy();
  });

  it('should validate urls', () => {
    const allowedUrls: string[] = [
      'https://www.sp-dashboard.com',
      'http://www.sp-dashboard.com',
      'http://www.sp-dashboard.com/with/path',
      'https://www.sp-dashboard.com/with/path',
      'https://www.sp-dashboard.com/with/path?query=param',
      'https://www.sp-dashboard.com/with/path#fragment',
      // Reverse redirect urls are allowed
      'nl.foobar.portal://https:7777/bar/foo',
      'nl.foobar.portal://https',
      'nl.foobar.portal://http',
      'nl.portal://custom',
      'nl.foobar.portal://custom/bar/foo',
      'nl.foobar.portal://https:7777/bar/foo?query=param',
      'nl.foobar.portal://https:7777/bar/foo?query=param&second=param',
      'nl.foobar.portal://https:7777/bar/foo#fragment',
    ];
    const illegalUrls: string[] = [
      'httpd://inorrect.protocol.com',
      'httpd:/invalid-protocol.com',
      'https://localhost',
      'https://185.258.148.45',
      'git://www.sp-dashboard.com/with/path',
      'ftp://www.sp-dashboard.com/with/path',
      'sftp://www.sp-dashboard.com/with/path',
      'https://il legalchar.com',
      'http://##/',
      'https:/www.sp-dashboard.com',
      'http://foobar',
      'http:/foobar',
    ];
    const helper = new ValidatorHelper();

    for (const allowed of allowedUrls) {
      expect(helper.validateUrl(allowed)).toBeTruthy();
    }

    for (const illegal of illegalUrls) {
      expect(helper.validateUrl(illegal)).toBeFalsy();
    }
  });

  it('should validate urns', () => {
    const allowedUrns: string[] = [
      'urn:mace:dir:attribute-def:eduPersonTargetedID',
      'urn:mace:dir:attribute-def:isMemberOf',
      'urn:oid:1.3.6.1.4.1.5923.1.1.1.16',
      'urn:lex:eu:foobar:directive:2010-03-09;2010-19-UE',
      'urn:lsid:foobank.org:pub:CDC8D258-8F57-41DC-B560-247E17D3DC8C',
      'urn:nonce:dir:pub:special-chars()+,-.:=@;$_!*\'%?#',
    ];
    const illegalUrns: string[] = [
      'url:nonce:dir:pub:foobar',
      'urn;nonce;dir;pub;foobar',
      'urn:nonce:dir:pub:illegalspecial-chars-🤕🤕😎',
    ];
    const helper = new ValidatorHelper();

    for (const allowed of allowedUrns) {
      expect(helper.validateUrn(allowed)).toBeTruthy();
    }

    for (const illegal of illegalUrns) {
      expect(helper.validateUrn(illegal)).toBeFalsy();
    }
  });

  it('should validate loopback urls', () => {
    const allowedUrls: string[] = [
      'https://localhost',
      'https://localhost/foo/bar',
      'http://localhost',
      'https://127.0.0.1',
      'https://[0:0:0:0:0:0:0:1]',
      'https://[::1]',
      'https://[::1]/foo/bar',
      'https://[::]',
      'http://[::]:7832',
      'https://localhost:3432',
    ];
    const illegalUrls: string[] = [
      'httpd://localhost',
      'https://local-host/foo/bar',
      'http://local',
    ];
    const helper = new ValidatorHelper();

    for (const allowed of allowedUrls) {
      expect(helper.validateLoopback(allowed)).toBeTruthy();
    }

    for (const illegal of illegalUrls) {
      expect(helper.validateLoopback(illegal)).toBeFalsy();
    }
  });
});
