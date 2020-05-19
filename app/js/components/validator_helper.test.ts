import {ValidatorHelper} from "./validator_helper";

describe('validation functions', function () {

  it('can validate empty strings', function () {
    const helper = new ValidatorHelper;
    expect(helper.validateEmpty('')).toBeTruthy();
    expect(helper.validateEmpty(' ')).toBeFalsy();
    expect(helper.validateEmpty('df')).toBeFalsy();
  });

  it('should validate urls', function () {
    let allowedUrls: Array<string> = [
      'https://www.sp-dashboard.com',
      'http://www.sp-dashboard.com',
      'https://www.sp-dashboard.com/with/path',
      'git://www.sp-dashboard.com/with/path',
      'ftp://www.sp-dashboard.com/with/path',
      'sftp://www.sp-dashboard.com/with/path',
    ];
    let illegalUrls: Array<string> = [
      'httpd://inorrect.protocol.com',
      'httpd:/invalid-protocol.com',
      'https://localhost',
    ];
    const helper = new ValidatorHelper;

    for (let allowed of allowedUrls) {
      expect(helper.validateUrl(allowed)).toBeTruthy();
    }

    for (let illegal of illegalUrls) {
      expect(helper.validateUrl(illegal)).toBeFalsy();
    }
  });

  it('should validate urns', function () {
    let allowedUrns: Array<string> = [
      'urn:mace:dir:attribute-def:eduPersonTargetedID',
      'urn:mace:dir:attribute-def:isMemberOf',
      'urn:oid:1.3.6.1.4.1.5923.1.1.1.16',
      'urn:lex:eu:foobar:directive:2010-03-09;2010-19-UE',
      'urn:lsid:foobank.org:pub:CDC8D258-8F57-41DC-B560-247E17D3DC8C',
      'urn:nonce:dir:pub:special-chars()+,-.:=@;$_!*\'%?#',
    ];
    let illegalUrns: Array<string> = [
      'url:nonce:dir:pub:foobar',
      'urn;nonce;dir;pub;foobar',
      'urn:nonce:dir:pub:illegalspecial-chars-🤕🤕😎',
    ];
    const helper = new ValidatorHelper;

    for (let allowed of allowedUrns) {
      expect(helper.validateUrn(allowed)).toBeTruthy();
    }

    for (let illegal of illegalUrns) {
      expect(helper.validateUrn(illegal)).toBeFalsy();
    }
  });

  it('should validate loopback urls', function () {
    let allowedUrls: Array<string> = [
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
    let illegalUrls: Array<string> = [
      'httpd://localhost',
      'https://local-host/foo/bar',
      'http://local',
    ];
    const helper = new ValidatorHelper;

    for (let allowed of allowedUrls) {
      expect(helper.validateLoopback(allowed)).toBeTruthy();
    }

    for (let illegal of illegalUrls) {
      expect(helper.validateLoopback(illegal)).toBeFalsy();
    }
  });

});